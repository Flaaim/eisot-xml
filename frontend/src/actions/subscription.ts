"use server";

import { API } from "@/app/api";
import { apiFetch } from "@/lib/apiClient";
import {
  ActivateSubscriptionPayload,
  SubscriptionAccess,
} from "@/interfaces/subscription.interface";
import { ApiResponse } from "@/interfaces/response.interface";
import { revalidatePath } from "next/cache";

export async function checkSubscriptionAccessAction(): Promise<ApiResponse<SubscriptionAccess>> {
  try {
    const response = await apiFetch(API.subscription.access(), {
      method: "GET",
      headers: { Accept: "application/json" },
    });

    const text = await response.text();
    const data = text ? JSON.parse(text) : null;

    if (!response.ok) {
      return {
        ok: false,
        error: data?.message ?? "Не удалось проверить статус User Subscription.",
      };
    }

    return { ok: true, data };
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

    const text = await response.text();
    const data = text ? JSON.parse(text) : null;

    if (!response.ok) {
      return {
        ok: false,
        error: data?.message ?? "Не удалось активировать User Subscription.",
      };
    }

    revalidatePath("/user/subscription");
    revalidatePath("/user/profile");
    revalidatePath("/user/company");

    return { ok: true, data };
  } catch {
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}
