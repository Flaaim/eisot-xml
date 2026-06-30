/**
 * СНИЛС — форматирование и валидация (синхронизировано с доменным Snils.php).
 */

/** Формат ЕИСОТ: XXX-XXX-XXX XX */
export const SNILS_PATTERN = /^\d{3}-\d{3}-\d{3} \d{2}$/;

/** Маска react-input-mask */
export const SNILS_INPUT_MASK = "999-999-999 99";

/** Номера ≤ этого значения не проверяются по контрольной сумме (ПФР). */
const CHECKSUM_EXEMPT_MAX = 1_001_998;

/**
 * Извлекает только цифры из строки.
 */
export function extractSnilsDigits(value: string): string {
  return value.replace(/\D/g, "").slice(0, 11);
}

/**
 * Приводит ввод к формату «XXX-XXX-XXX XX» (частичный ввод тоже форматируется).
 */
export function normalizeSnils(value: string): string {
  const digits = extractSnilsDigits(value);
  if (!digits) {
    return "";
  }

  const part1 = digits.slice(0, 3);
  const part2 = digits.length > 3 ? digits.slice(3, 6) : "";
  const part3 = digits.length > 6 ? digits.slice(6, 9) : "";
  const part4 = digits.length > 9 ? digits.slice(9, 11) : "";

  let formatted = part1;
  if (part2) formatted += `-${part2}`;
  if (part3) formatted += `-${part3}`;
  if (part4) formatted += ` ${part4}`;

  return formatted;
}

/**
 * Алгоритм контрольного числа ПФР (по первым 9 цифрам).
 */
export function calculateSnilsChecksum(nineDigits: string): number {
  const clean = nineDigits.replace(/\D/g, "");
  if (clean.length !== 9) {
    return -1;
  }

  let sum = 0;
  for (let i = 0; i < 9; i++) {
    sum += Number(clean[i]) * (9 - i);
  }

  if (sum < 100) {
    return sum;
  }
  if (sum === 100 || sum === 101) {
    return 0;
  }

  const remainder = sum % 101;
  return remainder === 100 ? 0 : remainder;
}

/**
 * Проверяет формат и контрольную сумму СНИЛС.
 */
export function isValidSnils(value: string): boolean {
  const normalized = normalizeSnils(value);

  if (!SNILS_PATTERN.test(normalized)) {
    return false;
  }

  const digits = extractSnilsDigits(normalized);
  const serial = Number(digits.slice(0, 9));
  const checksum = Number(digits.slice(9, 11));

  if (serial <= CHECKSUM_EXEMPT_MAX) {
    return true;
  }

  return calculateSnilsChecksum(digits.slice(0, 9)) === checksum;
}

/**
 * Возвращает нормализованный СНИЛС для отправки в API или null, если невалиден.
 */
export function toSnilsApiValue(value: string): string | null {
  const normalized = normalizeSnils(value);
  return isValidSnils(normalized) ? normalized : null;
}
