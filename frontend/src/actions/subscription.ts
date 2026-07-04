"use server";

import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";
import {
  ActivateSubscriptionPayload,
  SubscriptionAccess,
} from "@/interfaces/subscription.interface";
import { ApiResponse } from "@/interfaces/response.interface";
import { handleApiResponse } from "@/lib/handleApiResponse";
import { revalidatePath } from "next/cache";

interface ActivateSubscriptionResponse {
  id: string;
}

export async function checkSubscriptionAccessAction(): Promise<ApiResponse<SubscriptionAccess>> {
  try {
    const response = await apiFetch(API.subscription.access(), {
      method: "GET",
      headers: { Accept: "application/json" },
    });

    return await handleApiResponse<SubscriptionAccess>(response, {
      defaultError: "Не удалось проверить статус User Subscription.",
    });
  } catch {
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function activateSubscriptionAction(
  payload: ActivateSubscriptionPayload
): Promise<ApiResponse<{ id: string }>> {
  try {
    const response = await apiFetch(API.subscription.activate(), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    const parsed = await handleApiResponse<ActivateSubscriptionResponse>(response, {
      defaultError: "Не удалось активировать User Subscription.",
    });

    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }

    revalidatePath("/user/subscription");
    revalidatePath("/user/profile");
    revalidatePath("/user/company");

    return { ok: true, data: parsed.data ?? undefined };
  } catch {
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}
