"use client";

import { useForm, useFieldArray, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Plus, Trash2, ShieldCheck, UserCheck, BookOpen, AlertCircle } from "lucide-react";
import { toast } from "sonner";
import { useRouter } from "next/navigation";

import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import {
  WorkerAndProtocolsSchema,
  WorkerAndProtocolsFormData,
  TRAINING_PROGRAMS,
  TRAINING_RESULTS,
  ProtocolFormData
} from "@/types/worker-form.schema";
import { registerWorkerWithProtocolsAction } from "@/actions/worker";
import { CompanyShort } from "@/interfaces/company.interface";

interface WorkerRegistrationFormProps {
  readonly companyId: string;
  readonly company: CompanyShort;
}

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
  protocols: [{ ...EMPTY_PROTOCOL }],
};

/**
 * Auto-formatter for SNILS (111-222-333 45)
 */
function formatSnils(value: string): string {
  const digits = value.replace(/\D/g, "").slice(0, 11);
  let formatted = "";
  if (digits.length > 0) {
    formatted += digits.slice(0, 3);
  }
  if (digits.length > 3) {
    formatted += "-" + digits.slice(3, 6);
  }
  if (digits.length > 6) {
    formatted += "-" + digits.slice(6, 9);
  }
  if (digits.length > 9) {
    formatted += " " + digits.slice(9, 11);
  }
  return formatted;
}

export function WorkerRegistrationForm({ companyId, company }: WorkerRegistrationFormProps) {
  const router = useRouter();

  const {
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

  const onSubmit = async (values: WorkerAndProtocolsFormData) => {
    const result = await registerWorkerWithProtocolsAction(companyId, values);

    if (!result.ok) {
      toast.error(result.error || "Произошла ошибка при отправке данных.");
      return;
    }

    toast.success("Работник и протоколы обучения успешно зарегистрированы!");
    reset(DEFAULT_VALUES);
    router.refresh();
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
      {/* 1. Блок "Компания" (Read-only) */}
      <Card className="shadow-sm">
        <CardHeader className="flex flex-row items-center gap-3 pb-3">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-muted text-muted-foreground">
            <ShieldCheck className="h-5 w-5" />
          </div>
          <div className="space-y-0.5">
            <CardTitle className="text-lg font-semibold">Компания</CardTitle>
            <CardDescription>Место работы и реквизиты организации</CardDescription>
          </div>
        </CardHeader>
        <CardContent>
          <FieldGroup className="grid gap-4 sm:grid-cols-2">
            <Field>
              <FieldLabel htmlFor="company-name-ro">Место работы</FieldLabel>
              <Input id="company-name-ro" value={company.name} disabled className="bg-muted/50 cursor-not-allowed" />
            </Field>
            <Field>
              <FieldLabel htmlFor="company-inn-ro">ИНН организации работодателя</FieldLabel>
              <Input id="company-inn-ro" value={company.inn} disabled className="bg-muted/50 cursor-not-allowed" />
            </Field>
          </FieldGroup>
        </CardContent>
      </Card>

      {/* 2. Блок "Сотрудник" */}
      <Card className="shadow-sm">
        <CardHeader className="flex flex-row items-center gap-3 pb-3">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
            <UserCheck className="h-5 w-5" />
          </div>
          <div className="space-y-0.5">
            <CardTitle className="text-lg font-semibold">Сотрудник</CardTitle>
            <CardDescription>Введите персональные данные сотрудника</CardDescription>
          </div>
        </CardHeader>
        <CardContent>
          <FieldGroup className="grid gap-4 sm:grid-cols-3">
            <Controller
              name="fio"
              control={control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid}>
                  <FieldLabel htmlFor="worker-fio">ФИО</FieldLabel>
                  <Input
                    {...field}
                    id="worker-fio"
                    placeholder="Иванов Иван Иванович"
                    aria-invalid={fieldState.invalid}
                    autoComplete="name"
                  />
                  {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                </Field>
              )}
            />

            <Controller
              name="snils"
              control={control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid}>
                  <FieldLabel htmlFor="worker-snils">СНИЛС</FieldLabel>
                  <Input
                    {...field}
                    id="worker-snils"
                    placeholder="111-222-333 45"
                    value={field.value}
                    onChange={(e) => {
                      field.onChange(formatSnils(e.target.value));
                    }}
                    aria-invalid={fieldState.invalid}
                  />
                  {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                </Field>
              )}
            />

            <Controller
              name="profession"
              control={control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid}>
                  <FieldLabel htmlFor="worker-profession">Профессия</FieldLabel>
                  <Input
                    {...field}
                    id="worker-profession"
                    placeholder="Электромонтёр"
                    aria-invalid={fieldState.invalid}
                  />
                  {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                </Field>
              )}
            />
          </FieldGroup>
        </CardContent>
      </Card>

      {/* 3. Блок "Протоколы обучения" */}
      <Card className="shadow-sm">
        <CardHeader className="flex flex-row items-center justify-between pb-3">
          <div className="flex flex-row items-center gap-3">
            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <BookOpen className="h-5 w-5" />
            </div>
            <div className="space-y-0.5">
              <CardTitle className="text-lg font-semibold">Протоколы обучения</CardTitle>
              <CardDescription>Зарегистрируйте один или несколько протоколов</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent className="space-y-4">
          {errors.protocols?.root && (
            <div className="flex items-center gap-2 rounded-md bg-destructive/15 p-3 text-sm text-destructive">
              <AlertCircle className="h-4 w-4" />
              <span>{errors.protocols.root.message}</span>
            </div>
          )}

          {fields.map((item, index) => {
            const protocolErrors = errors.protocols?.[index];

            return (
              <div
                key={item.id}
                className="group relative rounded-lg border border-dashed p-4 transition-colors hover:border-primary/30 hover:bg-muted/10"
              >
                <div className="mb-4 flex items-center justify-between">
                  <span className="text-sm font-semibold text-muted-foreground">
                    Протокол #{index + 1}
                  </span>
                  {fields.length > 1 && (
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      onClick={() => remove(index)}
                      className="h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                      title="Удалить протокол"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  )}
                </div>

                <div className="grid gap-4 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-4">
                  {/* Программа обучения */}
                  <div className="md:col-span-2 xl:col-span-1">
                    <Controller
                      name={`protocols.${index}.programId`}
                      control={control}
                      render={({ field: controllerField, fieldState }) => (
                        <Field data-invalid={fieldState.invalid}>
                          <FieldLabel htmlFor={`program-${index}`}>Программа обучения</FieldLabel>
                          <select
                            id={`program-${index}`}
                            multiple
                            value={controllerField.value.map(String)}
                            onChange={(e) => {
                              const selected = Array.from(
                                e.target.selectedOptions,
                                (opt) => Number(opt.value)
                              );
                              controllerField.onChange(selected);
                            }}
                            className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 min-h-[100px]"
                          >
                            {TRAINING_PROGRAMS.map((program) => (
                              <option key={program.id} value={program.id}>
                                {program.id}. {program.title}
                              </option>
                            ))}
                          </select>
                          <p className="text-[11px] text-muted-foreground mt-1">
                            Удерживайте Ctrl (или Cmd на Mac) для выбора нескольких программ.
                          </p>
                          {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                        </Field>
                      )}
                    />
                  </div>

                  {/* Результат */}
                  <Controller
                    name={`protocols.${index}.result`}
                    control={control}
                    render={({ field: controllerField, fieldState }) => (
                      <Field data-invalid={fieldState.invalid}>
                        <FieldLabel htmlFor={`result-${index}`}>Результат</FieldLabel>
                        <select
                          id={`result-${index}`}
                          {...controllerField}
                          className="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                          {TRAINING_RESULTS.map((res) => (
                            <option key={res} value={res}>
                              {res.charAt(0).toUpperCase() + res.slice(1)}
                            </option>
                          ))}
                        </select>
                        {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                      </Field>
                    )}
                  />

                  {/* Дата */}
                  <Controller
                    name={`protocols.${index}.date`}
                    control={control}
                    render={({ field: controllerField, fieldState }) => (
                      <Field data-invalid={fieldState.invalid}>
                        <FieldLabel htmlFor={`date-${index}`}>Дата протокола</FieldLabel>
                        <Input
                          {...controllerField}
                          id={`date-${index}`}
                          type="date"
                          aria-invalid={fieldState.invalid}
                        />
                        {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                      </Field>
                    )}
                  />

                  {/* Номер протокола */}
                  <Controller
                    name={`protocols.${index}.protocolNumber`}
                    control={control}
                    render={({ field: controllerField, fieldState }) => (
                      <Field data-invalid={fieldState.invalid}>
                        <FieldLabel htmlFor={`number-${index}`}>Номер протокола</FieldLabel>
                        <Input
                          {...controllerField}
                          id={`number-${index}`}
                          placeholder="ПР-001"
                          aria-invalid={fieldState.invalid}
                        />
                        {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                      </Field>
                    )}
                  />
                </div>
              </div>
            );
          })}

          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => append({ ...EMPTY_PROTOCOL })}
            className="flex items-center gap-1 cursor-pointer"
          >
            <Plus className="h-4 w-4" />
            Добавить еще протокол
          </Button>
        </CardContent>
      </Card>

      {/* Buttons */}
      <div className="flex items-center justify-end gap-3 pt-2">
        <Button
          type="button"
          variant="ghost"
          disabled={isSubmitting}
          onClick={() => reset(DEFAULT_VALUES)}
          className="cursor-pointer"
        >
          Сбросить
        </Button>
        <Button type="submit" disabled={isSubmitting} className="min-w-[150px] cursor-pointer">
          {isSubmitting ? "Регистрация..." : "Зарегистрировать"}
        </Button>
      </div>
    </form>
  );
}
