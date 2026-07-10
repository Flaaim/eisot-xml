"use client";

import { useForm, useFieldArray, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  WorkerAndProtocolsSchema,
  TRAINING_RESULTS,
  type WorkerAndProtocolsFormData,
  type ProtocolFormData,
} from "@/types/worker-form.schema";
import { useTrainingPrograms } from "@/hooks/useTrainingPrograms";
import { useRegisterWorker } from "@/hooks/useRegisterWorker";
import { useToast } from "@/components/Toast/Toast";
import type { CompanyShort } from "@/types/company";

// ───────────────────────────────────────────────
// Props
// ───────────────────────────────────────────────

interface WorkerRegistrationFormProps {
  readonly companyId: string;
  readonly company: CompanyShort;
}

// ───────────────────────────────────────────────
// Defaults
// ───────────────────────────────────────────────

const EMPTY_PROTOCOL: ProtocolFormData = {
  programId: [],
  result: "удовлетворительно",
  date: "",
  protocolNumber: "",
};

const DEFAULT_VALUES: WorkerAndProtocolsFormData = {
  fio: "",
  snils: "",
  profession: "",
  isForeigner: false,
  protocols: [{ ...EMPTY_PROTOCOL }],
};

// ───────────────────────────────────────────────
// Reusable UI primitives (local — not worth extracting yet)
// ───────────────────────────────────────────────

function SectionHeader({ icon, title }: { icon: React.ReactNode; title: string }) {
  return (
    <div className="mb-6 flex items-center gap-3">
      <div className="flex size-9 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-400">
        {icon}
      </div>
      <h2 className="text-lg font-semibold text-white">{title}</h2>
    </div>
  );
}

function FieldLabel({ htmlFor, children }: { htmlFor: string; children: React.ReactNode }) {
  return (
    <label htmlFor={htmlFor} className="mb-1.5 block text-sm font-medium text-slate-300">
      {children}
    </label>
  );
}

function FieldError({ message }: { message?: string }) {
  if (!message) return null;
  return (
    <p className="mt-1.5 text-xs font-medium text-red-400" role="alert">
      {message}
    </p>
  );
}

const INPUT_BASE =
  "w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none backdrop-blur-sm transition-all duration-200 focus:border-indigo-500/50 focus:bg-white/[0.07] focus:ring-2 focus:ring-indigo-500/20";

const INPUT_DISABLED =
  "w-full rounded-xl border border-white/[0.06] bg-white/[0.02] px-4 py-2.5 text-sm text-slate-400 outline-none cursor-not-allowed";

const INPUT_ERROR = "border-red-500/40 focus:border-red-500/50 focus:ring-red-500/20";

// ───────────────────────────────────────────────
// Component
// ───────────────────────────────────────────────

export function WorkerRegistrationForm({ companyId, company }: WorkerRegistrationFormProps) {
  const { showToast } = useToast();
  const { programs, isLoading: isProgramsLoading } = useTrainingPrograms();
  const registerMutation = useRegisterWorker();

  const {
    register,
    control,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<WorkerAndProtocolsFormData>({
    resolver: zodResolver(WorkerAndProtocolsSchema),
    defaultValues: DEFAULT_VALUES,
    mode: "onBlur",
  });

  const { fields, append, remove } = useFieldArray({
    control,
    name: "protocols",
  });

  // ── Submit handler ──────────────────────────
  const onSubmit = async (formData: WorkerAndProtocolsFormData) => {
    try {
      await registerMutation.mutateAsync({ companyId, formData });
      showToast("Работник и протоколы успешно зарегистрированы!", "success");
      reset(DEFAULT_VALUES);
    } catch (err) {
      const message = err instanceof Error ? err.message : "Произошла непредвиденная ошибка";
      showToast(message, "error");
    }
  };

  const isBusy = isSubmitting || registerMutation.isPending;

  return (
    <form
      onSubmit={(e) => {
        void handleSubmit(onSubmit)(e);
      }}
      className="space-y-8"
      noValidate
    >
      {/* ═══════════════════════════════════════════
          Блок 1: Компания (Read-only)
          ═══════════════════════════════════════════ */}
      <section className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl">
        <SectionHeader
          title="Компания"
          icon={
            <svg
              className="size-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              strokeWidth={1.5}
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
              />
            </svg>
          }
        />

        <div className="grid gap-5 sm:grid-cols-2">
          {/* Место работы */}
          <div>
            <FieldLabel htmlFor="company-name">Место работы</FieldLabel>
            <input
              id="company-name"
              type="text"
              value={company.name}
              disabled
              className={INPUT_DISABLED}
            />
          </div>

          {/* ИНН */}
          <div>
            <FieldLabel htmlFor="company-inn">ИНН организации работодателя</FieldLabel>
            <input
              id="company-inn"
              type="text"
              value={company.inn}
              disabled
              className={INPUT_DISABLED}
            />
          </div>
        </div>
      </section>

      {/* ═══════════════════════════════════════════
          Блок 2: Сотрудник
          ═══════════════════════════════════════════ */}
      <section className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl">
        <SectionHeader
          title="Сотрудник"
          icon={
            <svg
              className="size-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              strokeWidth={1.5}
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
              />
            </svg>
          }
        />

        <div className="grid gap-5 sm:grid-cols-3">
          {/* ФИО */}
          <div>
            <FieldLabel htmlFor="fio">ФИО</FieldLabel>
            <input
              id="fio"
              type="text"
              placeholder="Иванов Иван Иванович"
              className={`${INPUT_BASE} ${errors.fio ? INPUT_ERROR : ""}`}
              {...register("fio")}
            />
            <FieldError message={errors.fio?.message} />
          </div>

          {/* СНИЛС */}
          <div>
            <FieldLabel htmlFor="snils">СНИЛС</FieldLabel>
            <input
              id="snils"
              type="text"
              placeholder="111-222-333 45"
              className={`${INPUT_BASE} ${errors.snils ? INPUT_ERROR : ""}`}
              {...register("snils")}
            />
            <FieldError message={errors.snils?.message} />
          </div>

          {/* Профессия */}
          <div>
            <FieldLabel htmlFor="profession">Профессия</FieldLabel>
            <input
              id="profession"
              type="text"
              placeholder="Электромонтёр"
              className={`${INPUT_BASE} ${errors.profession ? INPUT_ERROR : ""}`}
              {...register("profession")}
            />
            <FieldError message={errors.profession?.message} />
          </div>
        </div>
      </section>

      {/* ═══════════════════════════════════════════
          Блок 3: Протоколы обучения
          ═══════════════════════════════════════════ */}
      <section className="rounded-2xl border border-white/10 bg-white/[0.03] p-6 backdrop-blur-xl">
        <SectionHeader
          title="Протоколы обучения"
          icon={
            <svg
              className="size-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              strokeWidth={1.5}
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"
              />
            </svg>
          }
        />

        {/* Protocol array-level error */}
        {errors.protocols?.root?.message && (
          <div className="mb-4 rounded-lg border border-red-500/20 bg-red-500/5 px-4 py-2 text-sm text-red-400">
            {errors.protocols.root.message}
          </div>
        )}
        {typeof errors.protocols?.message === "string" && (
          <div className="mb-4 rounded-lg border border-red-500/20 bg-red-500/5 px-4 py-2 text-sm text-red-400">
            {errors.protocols.message}
          </div>
        )}

        <div className="space-y-4">
          {fields.map((field, index) => {
            const protocolErrors = errors.protocols?.[index];

            return (
              <div
                key={field.id}
                className="group relative rounded-xl border border-white/[0.06] bg-white/[0.02] p-5 transition-colors hover:border-white/10"
              >
                {/* Protocol number badge */}
                <div className="mb-4 flex items-center justify-between">
                  <span className="inline-flex items-center gap-2 rounded-lg bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-300">
                    <span className="size-1.5 rounded-full bg-indigo-400" />
                    Протокол #{index + 1}
                  </span>

                  {fields.length > 1 && (
                    <button
                      type="button"
                      onClick={() => {
                        remove(index);
                      }}
                      className="
                        flex size-8 items-center justify-center rounded-lg text-slate-500
                        opacity-0 transition-all duration-200 group-hover:opacity-100
                        hover:bg-red-500/10 hover:text-red-400
                      "
                      aria-label={`Удалить протокол ${index + 1}`}
                      title="Удалить протокол"
                    >
                      <svg
                        className="size-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        strokeWidth={2}
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
                        />
                      </svg>
                    </button>
                  )}
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                  {/* Программа обучения (мультиселект) */}
                  <div className="sm:col-span-2 lg:col-span-1">
                    <FieldLabel htmlFor={`protocol-program-${index}`}>
                      Программа обучения
                    </FieldLabel>
                    <Controller
                      control={control}
                      name={`protocols.${index}.programId`}
                      render={({ field: controllerField }) => (
                        <select
                          id={`protocol-program-${index}`}
                          multiple
                          value={controllerField.value.map(String)}
                          onChange={(e) => {
                            const selected = Array.from(e.target.selectedOptions, (opt) =>
                              Number(opt.value)
                            );
                            controllerField.onChange(selected);
                          }}
                          className={`${INPUT_BASE} min-h-[80px] ${
                            protocolErrors?.programId ? INPUT_ERROR : ""
                          }`}
                          disabled={isProgramsLoading}
                        >
                          {programs.map((program) => (
                            <option key={program.id} value={String(program.id)}>
                              {program.id}. {program.title}
                            </option>
                          ))}
                        </select>
                      )}
                    />
                    <FieldError message={protocolErrors?.programId?.message} />
                    <p className="mt-1 text-[11px] text-slate-500">
                      Ctrl+Click для выбора нескольких
                    </p>
                  </div>

                  {/* Результат */}
                  <div>
                    <FieldLabel htmlFor={`protocol-result-${index}`}>Результат</FieldLabel>
                    <select
                      id={`protocol-result-${index}`}
                      className={`${INPUT_BASE} ${protocolErrors?.result ? INPUT_ERROR : ""}`}
                      {...register(`protocols.${index}.result`)}
                    >
                      {TRAINING_RESULTS.map((result) => (
                        <option key={result} value={result}>
                          {result.charAt(0).toUpperCase() + result.slice(1)}
                        </option>
                      ))}
                    </select>
                    <FieldError message={protocolErrors?.result?.message} />
                  </div>

                  {/* Дата */}
                  <div>
                    <FieldLabel htmlFor={`protocol-date-${index}`}>Дата</FieldLabel>
                    <input
                      id={`protocol-date-${index}`}
                      type="date"
                      className={`${INPUT_BASE} ${protocolErrors?.date ? INPUT_ERROR : ""}`}
                      {...register(`protocols.${index}.date`)}
                    />
                    <FieldError message={protocolErrors?.date?.message} />
                  </div>

                  {/* Номер протокола */}
                  <div>
                    <FieldLabel htmlFor={`protocol-number-${index}`}>Номер протокола</FieldLabel>
                    <input
                      id={`protocol-number-${index}`}
                      type="text"
                      placeholder="ПР-001"
                      className={`${INPUT_BASE} ${
                        protocolErrors?.protocolNumber ? INPUT_ERROR : ""
                      }`}
                      {...register(`protocols.${index}.protocolNumber`)}
                    />
                    <FieldError message={protocolErrors?.protocolNumber?.message} />
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        {/* Add protocol button */}
        <button
          type="button"
          onClick={() => {
            append({ ...EMPTY_PROTOCOL });
          }}
          className="
            mt-5 inline-flex items-center gap-2 rounded-xl
            border border-dashed border-white/10 bg-white/[0.02]
            px-5 py-2.5 text-sm font-medium text-slate-400
            transition-all duration-200
            hover:border-indigo-500/30 hover:bg-indigo-500/5 hover:text-indigo-300
          "
        >
          <svg
            className="size-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            strokeWidth={2}
          >
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Добавить ещё протокол
        </button>
      </section>

      {/* ═══════════════════════════════════════════
          Submit
          ═══════════════════════════════════════════ */}
      <div className="flex items-center justify-end gap-4">
        <button
          type="button"
          onClick={() => {
            reset(DEFAULT_VALUES);
          }}
          disabled={isBusy}
          className="
            rounded-xl px-6 py-2.5 text-sm font-medium text-slate-400
            transition-colors duration-200
            hover:text-white
            disabled:cursor-not-allowed disabled:opacity-40
          "
        >
          Сбросить
        </button>

        <button
          type="submit"
          disabled={isBusy}
          className="
            relative inline-flex items-center gap-2 rounded-xl
            bg-gradient-to-r from-indigo-600 to-indigo-500
            px-8 py-2.5 text-sm font-semibold text-white
            shadow-lg shadow-indigo-500/25
            transition-all duration-200
            hover:from-indigo-500 hover:to-indigo-400 hover:shadow-indigo-500/40
            active:scale-[0.98] disabled:cursor-not-allowed
            disabled:opacity-50
          "
        >
          {isBusy ? (
            <>
              <svg className="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle
                  className="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  strokeWidth="4"
                />
                <path
                  className="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              Сохранение…
            </>
          ) : (
            <>
              <svg
                className="size-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                strokeWidth={2}
              >
                <path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
              Зарегистрировать
            </>
          )}
        </button>
      </div>
    </form>
  );
}
