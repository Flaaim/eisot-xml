"use server";

import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";
import { ApiResponse } from "@/interfaces/response.interface";
import { WorkerAndProtocolsFormData } from "@/types/worker-form.schema";
import { buildRegisterWorkerCommand, RegisterWorkerCommand } from "@/lib/register-worker.command";
import { getApiErrorMessage, handleApiResponse } from "@/lib/handleApiResponse";

interface RegisterWorkerResponse {
  id: string;
}

function isRegisterWorkerResponse(data: unknown): data is RegisterWorkerResponse {
  return (
    typeof data === "object" &&
    data !== null &&
    "id" in data &&
    typeof (data as RegisterWorkerResponse).id === "string"
  );
}

function toApiPayload(command: RegisterWorkerCommand): Record<string, unknown> {
  if (command.isForeigner) {
    return {
      lastName: command.lastName,
      firstName: command.firstName,
      middleName: command.middleName,
      profession: command.profession,
      isForeigner: true,
      citizenship: command.citizenship,
      ...(command.foreignSnils ? { foreignSnils: command.foreignSnils } : {}),
    };
  }

  return {
    lastName: command.lastName,
    firstName: command.firstName,
    middleName: command.middleName,
    profession: command.profession,
    isForeigner: false,
    snils: command.snils,
  };
}

export async function registerWorkerWithProtocolsAction(
  companyId: string,
  formData: WorkerAndProtocolsFormData
): Promise<ApiResponse<{ workerId: string }>> {
  try {
    const command = buildRegisterWorkerCommand(formData);

    const workerResponse = await apiFetch(API.worker.register(companyId), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(toApiPayload(command)),
    });

    const parsed = await handleApiResponse<RegisterWorkerResponse>(
      workerResponse,
      { defaultError: "Не удалось зарегистрировать работника." }
    );

    if (!parsed.ok || !parsed.data) {
      return { ok: false, error: parsed.error };
    }

    if (!isRegisterWorkerResponse(parsed.data)) {
      return { ok: false, error: "Не удалось получить ID зарегистрированного работника." };
    }

    const workerId = parsed.data.id;

    const records = formData.protocols.flatMap((p) => {
      const [year, month, day] = p.date.split("-");
      const formattedDate = `${day}.${month}.${year}`;
      return p.programId.map((pid) => ({
        program: pid,
        result: p.result,
        date: formattedDate,
        protocolNumber: p.protocolNumber,
      }));
    });

    try {
      await Promise.all(
        records.map(async (record) => {
          const response = await apiFetch(API.worker.recordTraining(workerId), {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify(record),
          });

          if (!response.ok) {
            const text = await response.text().catch(() => "");
            let msg = "Не удалось сохранить протокол обучения.";
            try {
              const data: unknown = text ? JSON.parse(text) : null;
              msg = getApiErrorMessage(data, msg);
            } catch {
              if (text) {
                msg += ` (${text})`;
              }
            }
            throw new Error(msg);
          }
        })
      );
    } catch (recordError) {
      console.error("Ошибка при регистрации протоколов:", recordError);
      const msg =
        recordError instanceof Error
          ? recordError.message
          : "Не удалось зарегистрировать некоторые протоколы.";
      return { ok: false, error: msg };
    }

    return { ok: true, data: { workerId } };
  } catch (error) {
    console.error("registerWorkerWithProtocolsAction error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}
