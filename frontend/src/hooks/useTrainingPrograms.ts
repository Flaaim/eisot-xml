"use client";

import { useQuery } from "@tanstack/react-query";
import { fetchTrainingPrograms } from "@/api/workers";
import type { TrainingProgram } from "@/types/worker";

/**
 * Хук для получения справочника программ обучения Минтруда.
 *
 * Справочник практически статичен — используем длинный staleTime.
 */
export function useTrainingPrograms() {
  const {
    data: programs = [],
    isLoading,
    isError,
    error,
  } = useQuery<TrainingProgram[]>({
    queryKey: ["training-programs"],
    queryFn: fetchTrainingPrograms,
    staleTime: 30 * 60 * 1000, // 30 минут — справочник меняется крайне редко
  });

  return { programs, isLoading, isError, error } as const;
}
