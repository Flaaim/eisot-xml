"use client";

import { useMutation } from "@tanstack/react-query";
import { createWorker, createTrainingRecord } from "@/api/workers";
import type { WorkerAndProtocolsFormData } from "@/types/worker-form.schema";
import type { CreateTrainingRecordDto } from "@/types/worker";

/**
 * Параметры мутации: данные формы + companyId из URL.
 */
interface RegisterWorkerParams {
  readonly companyId: string;
  readonly formData: WorkerAndProtocolsFormData;
}

/**
 * Хук-оркестратор для создания работника и его протоколов обучения.
 *
 * Последовательность (CQRS / DDD):
 * 1. POST /api/companies/{companyId}/workers → workerId
 * 2. Promise.all() → POST /api/workers/{workerId}/training-records (×N)
 *
 * Если создание работника прошло, но один из протоколов упал —
 * ошибка выбрасывается, но работник уже создан (eventual consistency).
 */
export function useRegisterWorker() {
  return useMutation({
    mutationFn: async ({ companyId, formData }: RegisterWorkerParams) => {
      // ── Step 1: Создание работника ────────────────────────
      const { workerId } = await createWorker(companyId, {
        fio: formData.fio,
        snils: formData.snils ?? "",
        profession: formData.profession,
      });

      // ── Step 2: Создание протоколов обучения ─────────────
      // Каждый протокол с мультиселектом программ «разворачивается»
      // в N записей — по одной на каждую выбранную программу.
      const trainingRecordDtos: CreateTrainingRecordDto[] = formData.protocols.flatMap((protocol) =>
        protocol.programId.map((pid) => ({
          programId: pid,
          result: protocol.result,
          date: protocol.date,
          protocolNumber: protocol.protocolNumber,
        }))
      );

      await Promise.all(trainingRecordDtos.map((dto) => createTrainingRecord(workerId, dto)));

      return { workerId };
    },
  });
}
