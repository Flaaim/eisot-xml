"use server";

import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";
import { ApiResponse } from "@/interfaces/response.interface";
import { getApiErrorMessage, handleApiResponse, parseApiErrorBody } from "@/lib/handleApiResponse";

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

    return await handleApiResponse<RegistryRecordDto[]>(response, {
      defaultError: "Не удалось загрузить реестр обучения.",
    });
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
        const data: unknown = text ? JSON.parse(text) : null;
        const body = parseApiErrorBody(data);
        if (body.code === "subscription_required") {
          return { ok: false, error: "subscription_required" };
        }
        errorMessage = getApiErrorMessage(data, errorMessage);
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
