"use client";

import { useForm, useFieldArray, Controller, useWatch } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Plus, Trash2, ShieldCheck, UserCheck, BookOpen, AlertCircle } from "lucide-react";
import { toast } from "sonner";
import { useRouter } from "next/navigation";

import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardDescription,
} from "@/components/ui/card";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import {
  WorkerAndProtocolsSchema,
  WorkerAndProtocolsFormData,
  TRAINING_PROGRAMS,
  TRAINING_RESULTS,
  ProtocolFormData,
} from "@/types/worker-form.schema";
import { registerWorkerWithProtocolsAction } from "@/actions/worker";
import { CompanyShort } from "@/interfaces/company.interface";
import { SnilsInput } from "@/components/User/Company/SnilsInput";

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
  isForeigner: false,
  snils: "",
  citizenship: "",
  foreignSnils: "",
  profession: "",
  protocols: [{ ...EMPTY_PROTOCOL }],
};

export function WorkerRegistrationForm({ companyId, company }: WorkerRegistrationFormProps) {
  const router = useRouter();

  const {
    control,
    handleSubmit,
    reset,
    setValue,
    formState: { errors, isSubmitting },
  } = useForm<WorkerAndProtocolsFormData>({
    resolver: zodResolver(WorkerAndProtocolsSchema),
    defaultValues: DEFAULT_VALUES,
    mode: "onBlur",
  });

  const isForeigner = useWatch({ control, name: "isForeigner", defaultValue: false });

  const { fields, append, remove } = useFieldArray({
    control,
    name: "protocols",
  });

  const onSubmit = async (values: WorkerAndProtocolsFormData) => {
    const result = await registerWorkerWithProtocolsAction(companyId, values);

    if (!result.ok) {
      toast.error(result.error ?? "Произошла ошибка при отправке данных.");
      return;
    }

    toast.success("Работник и протоколы обучения успешно зарегистрированы!");
    reset(DEFAULT_VALUES);
    router.refresh();
  };

  return (
    <form onSubmit={(e) => { void handleSubmit(onSubmit)(e); }} className="space-y-6">
      {/* 1. Блок "Компания" (Read-only) */}
      <Card className="shadow-sm">
        <CardHeader className="flex flex-row items-center gap-3 pb-3">
          <div className="flex size-9 items-center justify-center rounded-lg bg-muted text-muted-foreground">
            <ShieldCheck className="size-5" />
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
              <Input
                id="company-name-ro"
                value={company.name}
                disabled
                className="cursor-not-allowed bg-muted/50"
              />
            </Field>
            <Field>
              <FieldLabel htmlFor="company-inn-ro">ИНН организации работодателя</FieldLabel>
              <Input
                id="company-inn-ro"
                value={company.inn}
                disabled
                className="cursor-not-allowed bg-muted/50"
              />
            </Field>
          </FieldGroup>
        </CardContent>
      </Card>

      {/* 2. Блок "Сотрудник" */}
      <Card className="shadow-sm">
        <CardHeader className="flex flex-row items-center gap-3 pb-3">
          <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
            <UserCheck className="size-5" />
          </div>
          <div className="space-y-0.5">
            <CardTitle className="text-lg font-semibold">Сотрудник</CardTitle>
            <CardDescription>Введите персональные данные сотрудника</CardDescription>
          </div>
        </CardHeader>
        <CardContent className="space-y-4">
          <Controller
            name="isForeigner"
            control={control}
            render={({ field }) => (
              <Field orientation="horizontal">
                <Checkbox
                  id="worker-is-foreigner"
                  checked={field.value}
                  onCheckedChange={(checked) => {
                    const nextValue = checked;
                    field.onChange(nextValue);
                    if (nextValue) {
                      setValue("snils", "");
                    } else {
                      setValue("citizenship", "");
                      setValue("foreignSnils", "");
                    }
                  }}
                />
                <FieldLabel htmlFor="worker-is-foreigner" className="font-normal">
                  Иностранный гражданин (IsForeignSnils)
                </FieldLabel>
              </Field>
            )}
          />

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

            {!isForeigner ? (
              <Controller
                name="snils"
                control={control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="worker-snils">СНИЛС</FieldLabel>
                    <SnilsInput
                      id="worker-snils"
                      value={field.value ?? ""}
                      onChange={field.onChange}
                      onBlur={field.onBlur}
                      aria-invalid={fieldState.invalid}
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />
            ) : (
              <>
                <Controller
                  name="citizenship"
                  control={control}
                  render={({ field, fieldState }) => (
                    <Field data-invalid={fieldState.invalid}>
                      <FieldLabel htmlFor="worker-citizenship">Гражданство</FieldLabel>
                      <Input
                        {...field}
                        id="worker-citizenship"
                        placeholder="Узбекистан"
                        aria-invalid={fieldState.invalid}
                      />
                      {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                    </Field>
                  )}
                />
                <Controller
                  name="foreignSnils"
                  control={control}
                  render={({ field, fieldState }) => (
                    <Field data-invalid={fieldState.invalid}>
                      <FieldLabel htmlFor="worker-foreign-snils">СНИЛС иностранца</FieldLabel>
                      <Input
                        {...field}
                        id="worker-foreign-snils"
                        placeholder="Необязательно"
                        aria-invalid={fieldState.invalid}
                        maxLength={30}
                      />
                      {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                    </Field>
                  )}
                />
              </>
            )}

            <Controller
              name="profession"
              control={control}
              render={({ field, fieldState }) => (
                <Field
                  data-invalid={fieldState.invalid}
                  className={isForeigner ? "sm:col-span-3" : undefined}
                >
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
            <div className="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <BookOpen className="size-5" />
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
              <AlertCircle className="size-4" />
              <span>{errors.protocols.root.message}</span>
            </div>
          )}

          {fields.map((item, index) => {
            return (
              <div
                key={item.id}
                className="group relative rounded-lg border border-dashed p-4 transition-colors hover:border-primary/30 hover:bg-muted/10"
              >
                <div className="mb-4 flex items-center justify-between">
                  <span className="text-sm font-semibold text-muted-foreground">
                    Протокол #{String(index + 1)}
                  </span>
                  {fields.length > 1 && (
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      onClick={() => { remove(index); }}
                      className="size-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                      title="Удалить протокол"
                    >
                      <Trash2 className="size-4" />
                    </Button>
                  )}
                </div>

                <div className="grid gap-4 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-4">
                  <div className="md:col-span-2 xl:col-span-1">
                    <Controller
                      name={`protocols.${String(index)}.programId`}
                      control={control}
                      render={({ field: controllerField, fieldState }) => (
                        <Field data-invalid={fieldState.invalid}>
                          <FieldLabel htmlFor={`program-${String(index)}`}>Программа обучения</FieldLabel>
                          <select
                            id={`program-${String(index)}`}
                            multiple
                            value={(controllerField.value as number[]).map((programId) =>
                              String(programId)
                            )}
                            onChange={(e) => {
                              const selected = Array.from(e.target.selectedOptions, (opt) =>
                                Number.parseInt(opt.value, 10)
                              );
                              controllerField.onChange(selected);
                            }}
                            className="flex min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                          >
                            {TRAINING_PROGRAMS.map((program) => (
                              <option key={program.id} value={program.id}>
                                {program.id}. {program.title}
                              </option>
                            ))}
                          </select>
                          <p className="mt-1 text-[11px] text-muted-foreground">
                            Удерживайте Ctrl (или Cmd на Mac) для выбора нескольких программ.
                          </p>
                          {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                        </Field>
                      )}
                    />
                  </div>

                  <Controller
                    name={`protocols.${String(index)}.result`}
                    control={control}
                    render={({ field: controllerField, fieldState }) => (
                      <Field data-invalid={fieldState.invalid}>
                        <FieldLabel htmlFor={`result-${String(index)}`}>Результат</FieldLabel>
                        <select
                          id={`result-${String(index)}`}
                          {...controllerField}
                          className="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
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

                  <Controller
                    name={`protocols.${String(index)}.date`}
                    control={control}
                    render={({ field: controllerField, fieldState }) => (
                      <Field data-invalid={fieldState.invalid}>
                        <FieldLabel htmlFor={`date-${String(index)}`}>Дата протокола</FieldLabel>
                        <Input
                          {...controllerField}
                          id={`date-${String(index)}`}
                          type="date"
                          aria-invalid={fieldState.invalid}
                        />
                        {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                      </Field>
                    )}
                  />

                  <Controller
                    name={`protocols.${String(index)}.protocolNumber`}
                    control={control}
                    render={({ field: controllerField, fieldState }) => (
                      <Field data-invalid={fieldState.invalid}>
                        <FieldLabel htmlFor={`number-${String(index)}`}>Номер протокола</FieldLabel>
                        <Input
                          {...controllerField}
                          id={`number-${String(index)}`}
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
            onClick={() => { append({ ...EMPTY_PROTOCOL }); }}
            className="flex cursor-pointer items-center gap-1"
          >
            <Plus className="size-4" />
            Добавить еще протокол
          </Button>
        </CardContent>
      </Card>

      <div className="flex items-center justify-end gap-3 pt-2">
        <Button
          type="button"
          variant="ghost"
          disabled={isSubmitting}
          onClick={() => { reset(DEFAULT_VALUES); }}
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
