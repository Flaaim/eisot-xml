"use server";

import {
  AddCompanyPayload,
  CompanyShort,
  CompanyStats,
  CompanyTitleByInnResponse,
} from "@/interfaces/company.interface";
import { ApiResponse } from "@/interfaces/response.interface";
import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";
import { handleApiResponse } from "@/lib/handleApiResponse";
import { FNS_TITLE_LOOKUP_ENABLED } from "@/lib/inn";
import { revalidatePath } from "next/cache";

interface AddCompanyResponseData {
  id: string;
}

export async function addCompanyAction(
  payload: AddCompanyPayload
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
export async function fetchCompanyAction(id: string): Promise<ApiResponse<CompanyShort>> {
  try {
    const response = await apiFetch(API.company.get(id), {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
    });
    return await handleApiResponse<CompanyShort>(response);
  } catch (error) {
    console.error("fetchCompanyAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}
export async function archiveCompanyAction(companyId: string): Promise<ApiResponse> {
  try {
    const response = await apiFetch(API.company.archive(companyId), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
    });

    const parsed = await handleApiResponse(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }
    revalidatePath("/user/company");
    revalidatePath(`/user/company/${companyId}`);
    return { ok: true };
  } catch (error) {
    console.error("archiveCompanyAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function unarchiveCompanyAction(companyId: string): Promise<ApiResponse> {
  try {
    const response = await apiFetch(API.company.restore(companyId), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
    });

    const parsed = await handleApiResponse(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }
    revalidatePath("/user/company");
    revalidatePath(`/user/company/${companyId}`);
    return { ok: true };
  } catch (error) {
    console.error("unarchiveCompanyAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function getCompanyStatsAction(companyId: string): Promise<CompanyStats> {
  try {
    const response = await apiFetch(API.company.stats(companyId), {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
    });

    const parsed = await handleApiResponse<CompanyStats>(response);
    if (!parsed.ok || !parsed.data) {
      throw new Error(parsed.error ?? "Не удалось загрузить статистику компании.");
    }
    return parsed.data;
  } catch (error) {
    console.error("getCompanyStatsAction Fetch error:", error);
    return {
      workersCount: 0,
      protocolsCount: 0,
      status: "Ошибка",
    };
  }
}

export async function renameCompanyAction(
  companyId: string,
  name: string
): Promise<ApiResponse<null>> {
  try {
    const response = await apiFetch(API.company.rename(companyId), {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ name }),
    });

    const parsed = await handleApiResponse<null>(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }

    revalidatePath("/user/company");
    revalidatePath(`/user/company/${companyId}`);
    revalidatePath(`/user/company/${companyId}/settings`);

    return { ok: true, data: null };
  } catch (error) {
    console.error("renameCompanyAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function changeCompanyInnAction(
  companyId: string,
  inn: string
): Promise<ApiResponse<null>> {
  try {
    const response = await apiFetch(API.company.changeInn(companyId), {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ inn }),
    });

    const parsed = await handleApiResponse<null>(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }

    revalidatePath("/user/company");
    revalidatePath(`/user/company/${companyId}`);
    revalidatePath(`/user/company/${companyId}/settings`);

    return { ok: true, data: null };
  } catch (error) {
    console.error("changeCompanyInnAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

/**
 * Заглушка для интеграции с прокси ФНС (получение Title по Inn).
 * Включить после появления эндпоинта на бэкенде.
 */
export function fetchCompanyTitleByInnAction(
  inn: string
): Promise<ApiResponse<CompanyTitleByInnResponse>> {
  void inn;

  return Promise.resolve({
    ok: false,
    error: FNS_TITLE_LOOKUP_ENABLED
      ? "Эндпоинт прокси ФНС не настроен."
      : "Сервис получения наименования по ИНН будет доступен в следующей версии.",
  });
}
