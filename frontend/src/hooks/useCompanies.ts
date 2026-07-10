"use client";

import { useQuery } from "@tanstack/react-query";
import { fetchCompanies } from "@/api/companies";
import type { CompanyShort } from "@/types/company";

/**
 * Хук для получения списка компаний текущего пользователя.
 *
 * Использует React Query для кэширования, дедупликации запросов
 * и автоматического отслеживания состояний загрузки / ошибки.
 */
export function useCompanies() {
  const {
    data: companies = [],
    isLoading,
    isError,
    error,
  } = useQuery<CompanyShort[]>({
    queryKey: ["companies"],
    queryFn: fetchCompanies,
    staleTime: 5 * 60 * 1000, // 5 минут — данные меняются редко
  });

  return {
    companies,
    isLoading,
    isError,
    error,
  } as const;
}
