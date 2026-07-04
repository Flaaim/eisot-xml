import type { ApiResponse } from "@/interfaces/response.interface";

export interface ApiErrorBody {
  message?: string;
  error_description?: string;
  code?: string;
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null;
}

export function parseApiErrorBody(data: unknown): ApiErrorBody {
  if (!isRecord(data)) {
    return {};
  }

  return {
    message: typeof data.message === "string" ? data.message : undefined,
    error_description:
      typeof data.error_description === "string" ? data.error_description : undefined,
    code: typeof data.code === "string" ? data.code : undefined,
  };
}

export function getApiErrorMessage(
  data: unknown,
  fallback = "Произошла ошибка при выполнении запроса."
): string {
  const body = parseApiErrorBody(data);
  return body.error_description ?? body.message ?? fallback;
}

export async function readResponseJson(response: Response): Promise<unknown> {
  const text = await response.text();
  if (!text) {
    return {};
  }

  return JSON.parse(text) as unknown;
}

interface HandleApiResponseOptions {
  defaultError?: string;
  unauthorizedMessage?: string;
}

export async function handleApiResponse<T>(
  response: Response,
  options: HandleApiResponseOptions = {}
): Promise<ApiResponse<T>> {
  let data: unknown;

  try {
    data = await readResponseJson(response);
  } catch (parseError) {
    console.error("Ошибка парсинга ответа API:", parseError);
    return { ok: false, data: null, error: "Сервер вернул некорректный ответ." };
  }

  if (!response.ok) {
    const body = parseApiErrorBody(data);
    const defaultError = options.defaultError ?? "Произошла ошибка при выполнении запроса.";
    let errorMessage = getApiErrorMessage(data, defaultError);

    if (response.status === 409 && body.message) {
      errorMessage = body.message;
    } else if (response.status === 401 && !body.error_description) {
      errorMessage = options.unauthorizedMessage ?? "Ошибка авторизации";
    }

    return { ok: false, data: data as T, error: errorMessage };
  }

  return { ok: true, data: data as T };
}
