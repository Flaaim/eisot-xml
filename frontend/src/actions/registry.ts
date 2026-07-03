"use server";

import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";
import { ApiResponse } from "@/interfaces/response.interface";

export interface RegistryRecordDto {
  id: string;
  workerFullName: string;
  snils: string;
  profession: string;
  programTitle: string;
  result: string;
  date: string;
  protocolNumber: string;
}

export async function getRegistryRecordsAction(
  companyId: string
): Promise<ApiResponse<RegistryRecordDto[]>> {
  try {
    const response = await apiFetch(API.company.trainingRecords(companyId), {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : [];
    } catch (parseError) {
      console.error("Ошибка парсинга ответа реестра обучения:", parseError);
      return { ok: false, error: "Сервер вернул некорректный ответ." };
    }

    if (!response.ok) {
      const errorMessage =
        data.message || data.error_description || "Не удалось загрузить реестр обучения.";
      return { ok: false, error: errorMessage };
    }

    return { ok: true, data: data as RegistryRecordDto[] };
  } catch (error) {
    console.error("getRegistryRecordsAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function exportRegistryToXmlAction(
  recordIds: string[]
): Promise<ApiResponse<{ xmlContent: string }>> {
  try {
    const response = await apiFetch(API.training.export(), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/xml, application/json",
      },
      body: JSON.stringify({ recordIds }),
    });

    const text = await response.text();

    if (!response.ok) {
      let errorMessage = "Не удалось экспортировать реестр в XML.";
      try {
        const data = JSON.parse(text);
        if (data.code === "subscription_required") {
          return { ok: false, error: "subscription_required" };
        }
        errorMessage = data.message || data.error_description || errorMessage;
      } catch {
        if (text) {
          errorMessage += ` (${text})`;
        }
      }
      return { ok: false, error: errorMessage };
    }

    return { ok: true, data: { xmlContent: text } };
  } catch (error) {
    console.error("exportRegistryToXmlAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}
