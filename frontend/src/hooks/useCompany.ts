"use client";

import { useQuery } from "@tanstack/react-query";
import { fetchCompanyById } from "@/api/workers";
import type { CompanyShort } from "@/types/company";

/**
 * Хук для получения данных компании по ID.
 *
 * Используется на странице компании для отображения read-only
 * информации (name, inn) в шапке формы.
 */
export function useCompany(companyId: string) {
  const {
    data: company,
    isLoading,
    isError,
    error,
  } = useQuery<CompanyShort>({
    queryKey: ["company", companyId],
    queryFn: () => fetchCompanyById(companyId),
    enabled: Boolean(companyId),
    staleTime: 10 * 60 * 1000, // 10 минут — профиль компании меняется редко
  });

  return { company, isLoading, isError, error } as const;
}
