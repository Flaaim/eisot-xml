import type { WorkerAndProtocolsFormData } from "@/types/worker-form.schema";
import { normalizeSnils, toSnilsApiValue } from "@/lib/snils";

/**
 * Команда регистрации работника (Ubiquitous Language).
 * Соответствует RegisterWorker\Command на бэкенде.
 */
export type RegisterWorkerCommand =
  | {
      lastName: string;
      firstName: string;
      middleName: string | null;
      profession: string;
      isForeigner: false;
      snils: string;
    }
  | {
      lastName: string;
      firstName: string;
      middleName: string | null;
      profession: string;
      isForeigner: true;
      citizenship: string;
      foreignSnils?: string;
    };

function parseFullName(fio: string): {
  lastName: string;
  firstName: string;
  middleName: string | null;
} {
  const parts = fio.trim().split(/\s+/);
  const lastName = parts[0] ?? "";
  const firstName = parts[1] ?? "";
  const middleName = parts.slice(2).join(" ") || null;

  return { lastName, firstName, middleName };
}

/**
 * Преобразует данные формы в команду регистрации работника.
 * Бизнес-логика отделена от UI-компонента формы.
 */
export function buildRegisterWorkerCommand(
  formData: WorkerAndProtocolsFormData
): RegisterWorkerCommand {
  const { lastName, firstName, middleName } = parseFullName(formData.fio);

  if (formData.isForeigner) {
    return {
      lastName,
      firstName,
      middleName,
      profession: formData.profession,
      isForeigner: true,
      citizenship: formData.citizenship.trim(),
      ...(formData.foreignSnils?.trim() ? { foreignSnils: formData.foreignSnils.trim() } : {}),
    };
  }

  const snils = toSnilsApiValue(formData.snils) ?? normalizeSnils(formData.snils);

  return {
    lastName,
    firstName,
    middleName,
    profession: formData.profession,
    isForeigner: false,
    snils,
  };
}
