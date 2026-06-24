export interface AddCompanyPayload {
  name: string;
  inn: string;
}

export interface AddCompanyResponseData {
  id: string;
}

/**
 * Краткая информация о компании для списка / дашборда.
 * Соответствует CompanyShortDto на бэкенде.
 */
export interface CompanyShort {
  readonly id: string;
  readonly name: string;
  readonly inn: string;
  readonly is_archived: boolean
}

export interface CompanyStats {
  readonly workersCount: number;
  readonly protocolsCount: number;
  readonly status: string;
}
