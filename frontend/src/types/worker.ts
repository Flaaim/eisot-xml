/**
 * DTO для создания работника (Command-side).
 *
 * Отправляется на POST /api/companies/{companyId}/workers.
 */
export interface CreateWorkerDto {
  readonly fio: string;
  readonly snils: string;
  readonly profession: string;
}

/**
 * Ответ бэкенда при успешном создании работника.
 */
export interface CreateWorkerResponse {
  readonly workerId: string;
}

/**
 * DTO для создания записи об обучении (Command-side).
 *
 * Отправляется на POST /api/workers/{workerId}/training-records.
 */
export interface CreateTrainingRecordDto {
  readonly programId: number;
  readonly result: TrainingResult;
  readonly date: string;
  readonly protocolNumber: string;
}

/**
 * Результат обучения — строгий enum.
 */
export type TrainingResult = "удовлетворительно" | "неудовлетворительно";

/**
 * Элемент справочника программ обучения (Read-Model).
 */
export interface TrainingProgram {
  readonly id: number;
  readonly title: string;
}
