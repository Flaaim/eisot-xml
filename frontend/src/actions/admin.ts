"use server";

import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";
import { handleApiResponse } from "@/lib/handleApiResponse";
import type { ApiResponse } from "@/interfaces/response.interface";
import type {
  AdminPaymentsListResponse,
  AdminSubscriptionFilter,
  AdminSubscriptionStats,
  AdminUsersListResponse,
} from "@/interfaces/admin.interface";

export async function fetchAdminUsersAction(params?: {
  page?: number;
  limit?: number;
  email?: string;
  subscriptionStatus?: AdminSubscriptionFilter;
}): Promise<ApiResponse<AdminUsersListResponse>> {
  try {
    const search = new URLSearchParams();
    if (params?.page) {
      search.set("page", String(params.page));
    }
    if (params?.limit) {
      search.set("limit", String(params.limit));
    }
    if (params?.email) {
      search.set("email", params.email);
    }
    if (params?.subscriptionStatus) {
      search.set("subscriptionStatus", params.subscriptionStatus);
    }

    const query = search.toString();
    const response = await apiFetch(query ? `${API.admin.users()}?${query}` : API.admin.users(), {
      method: "GET",
      headers: { Accept: "application/json" },
    });

    return await handleApiResponse<AdminUsersListResponse>(response, {
      defaultError: "Не удалось загрузить список пользователей.",
    });
  } catch {
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function fetchAdminStatsAction(): Promise<ApiResponse<AdminSubscriptionStats>> {
  try {
    const response = await apiFetch(API.admin.stats(), {
      method: "GET",
      headers: { Accept: "application/json" },
    });

    return await handleApiResponse<AdminSubscriptionStats>(response, {
      defaultError: "Не удалось загрузить статистику.",
    });
  } catch {
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function fetchAdminPaymentsAction(params?: {
  page?: number;
  limit?: number;
}): Promise<ApiResponse<AdminPaymentsListResponse>> {
  try {
    const search = new URLSearchParams();
    if (params?.page) {
      search.set("page", String(params.page));
    }
    if (params?.limit) {
      search.set("limit", String(params.limit));
    }

    const query = search.toString();
    const response = await apiFetch(
      query ? `${API.admin.payments()}?${query}` : API.admin.payments(),
      {
        method: "GET",
        headers: { Accept: "application/json" },
      }
    );

    return await handleApiResponse<AdminPaymentsListResponse>(response, {
      defaultError: "Не удалось загрузить список транзакций.",
    });
  } catch {
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}
