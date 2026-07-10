import type { CompanyShort } from "@/types/company";

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? "";

/**
 * Получить список компаний текущего пользователя.
 *
 * Передаёт JWT-токен из localStorage в заголовке Authorization.
 * При ошибке HTTP выбрасывает Error с описанием.
 */
export async function fetchCompanies(): Promise<CompanyShort[]> {
  const token = typeof window !== "undefined" ? localStorage.getItem("auth_token") : null;

  const response = await fetch(`${API_BASE_URL}/api/companies`, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  });

  if (!response.ok) {
    throw new Error(`Ошибка загрузки компаний: ${String(response.status)} ${response.statusText}`);
  }

  return response.json() as Promise<CompanyShort[]>;
}
