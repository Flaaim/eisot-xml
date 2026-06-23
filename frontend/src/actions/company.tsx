"use server";

import { AddCompanyPayload, CompanyShort } from "@/interfaces/company.interface";
import { ApiResponse } from "@/interfaces/response.interface";
import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";

interface AddCompanyResponseData {
  id: string;
}

async function handleApiResponse<T>(response: Response): Promise<ApiResponse<T>> {
  const text = await response.text();
  let data;

  try {
    data = text ? JSON.parse(text) : {};
  } catch (parseError) {
    console.error("Ошибка парсинга ответа API:", parseError);
    return { ok: false, data: null, error: "Сервер вернул некорректный ответ." };
  }

  if (!response.ok) {
    const errorMessage =
      data.error_description || data.message || "Произошла ошибка при выполнении запроса.";
    return { ok: false, data, error: errorMessage };
  }

  return { ok: true, data: data as T };
}

export async function addCompanyAction(
  payload: AddCompanyPayload,
): Promise<ApiResponse<AddCompanyResponseData>> {
  try {
    const response = await apiFetch(API.company.add(), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        name: payload.name,
        inn: payload.inn,
      }),
    });

    return await handleApiResponse<AddCompanyResponseData>(response);
  } catch (error) {
    console.error("addCompanyAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

/**
 * Получить список компаний текущего пользователя.
 * Server Action — вызывает apiFetch с JWT из cookies.
 */
export async function fetchCompaniesAction(): Promise<ApiResponse<CompanyShort[]>> {
  try {
    const response = await apiFetch(API.company.list(), {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
    });

    return await handleApiResponse<CompanyShort[]>(response);
  } catch (error) {
    console.error("fetchCompaniesAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function archiveCompany(id: string): Promise<ApiResponse> {
  try {
      const response = await apiFetch(API.company.archive(id), {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        }
      })
    const parsed = await handleApiResponse(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }
    return { ok: true };
  }catch (error){
    console.error("archiveCompany Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}
