/**
 * ИНН организации (Organization.Inn / EmployerInn в XSD).
 * Синхронизировано с доменным Value Object Inn.php (формат + контрольные цифры).
 */

export const INN_PATTERN = /^\d{10}(\d{2})?$/;

const CHECKSUM_COEF_10 = [2, 4, 10, 3, 5, 9, 4, 6, 8] as const;
const CHECKSUM_COEF_12_FIRST = [7, 2, 4, 10, 3, 5, 9, 4, 6, 8] as const;
const CHECKSUM_COEF_12_SECOND = [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8] as const;

/** Включить, когда на бэкенде появится прокси к сервису ФНС. */
export const FNS_TITLE_LOOKUP_ENABLED =
  process.env.NEXT_PUBLIC_FNS_TITLE_LOOKUP_ENABLED === "true";

export function normalizeInn(value: string): string {
  return value.replace(/\D/g, "").slice(0, 12);
}

export function isValidInnFormat(value: string): boolean {
  const digits = normalizeInn(value);
  return digits.length === 10 || digits.length === 12;
}

function calculateCheckDigit(digits: string, coefficients: readonly number[]): number {
  let sum = 0;
  for (let i = 0; i < coefficients.length; i++) {
    sum += Number(digits[i]) * coefficients[i];
  }
  return (sum % 11) % 10;
}

export function isValidInnChecksum(value: string): boolean {
  const digits = normalizeInn(value);

  if (digits.length === 10) {
    return calculateCheckDigit(digits, CHECKSUM_COEF_10) === Number(digits[9]);
  }

  if (digits.length === 12) {
    const firstCheck = calculateCheckDigit(digits, CHECKSUM_COEF_12_FIRST);
    const secondCheck = calculateCheckDigit(digits, CHECKSUM_COEF_12_SECOND);
    return firstCheck === Number(digits[10]) && secondCheck === Number(digits[11]);
  }

  return false;
}

export function isValidInn(value: string): boolean {
  const digits = normalizeInn(value);
  if (!INN_PATTERN.test(digits)) {
    return false;
  }
  return isValidInnChecksum(digits);
}

export function validateInn(value: string): string | null {
  const digits = normalizeInn(value);

  if (digits.length === 0) {
    return "ИНН обязателен для заполнения.";
  }

  if (!/^\d+$/.test(digits)) {
    return "ИНН должен содержать только цифры.";
  }

  if (digits.length !== 10 && digits.length !== 12) {
    return "ИНН должен состоять из 10 или 12 цифр.";
  }

  if (!isValidInnChecksum(digits)) {
    return "Неверная контрольная сумма ИНН.";
  }

  return null;
}
