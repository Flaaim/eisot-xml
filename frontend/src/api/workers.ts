import type {
  CreateWorkerDto,
  CreateWorkerResponse,
  CreateTrainingRecordDto,
  TrainingProgram,
} from "@/types/worker";
import type { CompanyShort } from "@/types/company";

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? "";

// ───────────────────────────────────────────────
// Helpers
// ───────────────────────────────────────────────

function getAuthHeaders(): HeadersInit {
  const token = typeof window !== "undefined" ? localStorage.getItem("auth_token") : null;

  return {
    "Content-Type": "application/json",
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };
}

async function handleResponse<T>(response: Response): Promise<T> {
  if (!response.ok) {
    const body = await response.text().catch(() => "");
    throw new Error(
      `Ошибка ${String(response.status)}: ${response.statusText}${body ? ` — ${body}` : ""}`
    );
  }
  return response.json() as Promise<T>;
}

// ───────────────────────────────────────────────
// Queries (Read-Model)
// ───────────────────────────────────────────────

/**
 * Получить данные компании по ID.
 */
export async function fetchCompanyById(companyId: string): Promise<CompanyShort> {
  const response = await fetch(`${API_BASE_URL}/api/companies/${companyId}`, {
    method: "GET",
    headers: getAuthHeaders(),
  });

  return handleResponse<CompanyShort>(response);
}

/**
 * Получить справочник программ обучения Минтруда.
 */
export async function fetchTrainingPrograms(): Promise<TrainingProgram[]> {
  const response = await fetch(`${API_BASE_URL}/api/training-programs`, {
    method: "GET",
    headers: getAuthHeaders(),
  });

  return handleResponse<TrainingProgram[]>(response);
}

// ───────────────────────────────────────────────
// Commands (Write-side)
// ───────────────────────────────────────────────

/**
 * Создать работника в контексте компании.
 *
 * POST /api/companies/{companyId}/workers
 * Возвращает сгенерированный бэкендом workerId.
 */
export async function createWorker(
  companyId: string,
  dto: CreateWorkerDto
): Promise<CreateWorkerResponse> {
  const response = await fetch(`${API_BASE_URL}/api/companies/${companyId}/workers`, {
    method: "POST",
    headers: getAuthHeaders(),
    body: JSON.stringify(dto),
  });

  return handleResponse<CreateWorkerResponse>(response);
}

/**
 * Создать запись об обучении для работника.
 *
 * POST /api/workers/{workerId}/training-records
 */
export async function createTrainingRecord(
  workerId: string,
  dto: CreateTrainingRecordDto
): Promise<void> {
  const response = await fetch(`${API_BASE_URL}/api/workers/${workerId}/training-records`, {
    method: "POST",
    headers: getAuthHeaders(),
    body: JSON.stringify(dto),
  });

  if (!response.ok) {
    const body = await response.text().catch(() => "");
    throw new Error(
      `Ошибка создания протокола: ${String(response.status)}${body ? ` — ${body}` : ""}`
    );
  }
}
