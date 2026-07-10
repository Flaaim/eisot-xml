/**
 * Краткая информация о компании для списка / дашборда.
 *
 * Соответствует CompanyShortDto на бэкенде.
 */
export interface CompanyShort {
  /** UUID компании */
  readonly id: string;
  /** Полное наименование */
  readonly name: string;
  /** ИНН (10 или 12 цифр) */
  readonly inn: string;
}
