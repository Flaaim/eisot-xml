"use server";

import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";
import { ApiResponse } from "@/interfaces/response.interface";
import { WorkerAndProtocolsFormData } from "@/types/worker-form.schema";

interface RegisterWorkerResponse {
  workerId: string;
}

export async function registerWorkerWithProtocolsAction(
  companyId: string,
  formData: WorkerAndProtocolsFormData
): Promise<ApiResponse<RegisterWorkerResponse>> {
  try {
    // 1. Parse FIO
    const parts = formData.fio.trim().split(/\s+/);
    const lastName = parts[0] || "";
    const firstName = parts[1] || "";
    const middleName = parts.slice(2).join(" ") || null;

    // 2. Register worker
    const workerResponse = await apiFetch(API.worker.register(companyId), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        lastName,
        firstName,
        middleName,
        profession: formData.profession,
        snils: formData.snils,
        isForeigner: false,
      }),
    });

    const workerText = await workerResponse.text();
    let workerData;
    try {
      workerData = workerText ? JSON.parse(workerText) : {};
    } catch (parseError) {
      console.error("Ошибка парсинга ответа API регистрации работника:", parseError);
      return { ok: false, error: "Сервер вернул некорректный ответ при регистрации работника." };
    }

    if (!workerResponse.ok) {
      const errorMessage =
        workerData.error_description || workerData.message || "Не удалось зарегистрировать работника.";
      return { ok: false, error: errorMessage };
    }

    const workerId = workerData.id;
    if (!workerId) {
      return { ok: false, error: "Не удалось получить ID зарегистрированного работника." };
    }

    // 3. Register protocols (training records) in parallel
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
              const data = JSON.parse(text);
              msg = data.error_description || data.message || msg;
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
      const msg = recordError instanceof Error ? recordError.message : "Не удалось зарегистрировать некоторые протоколы.";
      return { ok: false, error: msg };
    }

    return { ok: true, data: { workerId } };
  } catch (error) {
    console.error("registerWorkerWithProtocolsAction error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}
