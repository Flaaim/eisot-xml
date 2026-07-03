"use client";

import { useState } from "react";
import { Controller, useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Building2, Loader2, Search } from "lucide-react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";

import {
  changeCompanyInnAction,
  fetchCompanyTitleByInnAction,
  renameCompanyAction,
} from "@/actions/company";
import { InnInput } from "@/components/User/Company/InnInput";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Field, FieldDescription, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FNS_TITLE_LOOKUP_ENABLED, normalizeInn, validateInn } from "@/lib/inn";
import { CompanySettingsFormData, CompanySettingsSchema } from "@/types/company-settings.schema";

interface CompanySettingsFormProps {
  readonly companyId: string;
  readonly initialTitle: string;
  readonly initialInn: string;
  readonly isArchived: boolean;
}

export function CompanySettingsForm({
  companyId,
  initialTitle,
  initialInn,
  isArchived,
}: CompanySettingsFormProps) {
  const router = useRouter();
  const [isLookupLoading, setIsLookupLoading] = useState(false);

  const form = useForm<CompanySettingsFormData>({
    mode: "onBlur",
    resolver: zodResolver(CompanySettingsSchema),
    defaultValues: {
      title: initialTitle,
      inn: initialInn,
    },
  });

  async function handleLookupTitleByInn() {
    const inn = form.getValues("inn");
    const validationError = validateInn(inn);

    if (validationError) {
      form.setError("inn", {
        type: "manual",
        message: validationError,
      });
      return;
    }

    const normalizedInn = normalizeInn(inn);

    setIsLookupLoading(true);
    try {
      const result = await fetchCompanyTitleByInnAction(normalizedInn);
      if (!result.ok) {
        toast.info(result.error ?? "Сервис получения наименования по ИНН недоступен.");
        return;
      }
      if (result.data?.title) {
        form.setValue("title", result.data.title, { shouldDirty: true, shouldValidate: true });
        toast.success("Наименование получено по ИНН.");
      }
    } finally {
      setIsLookupLoading(false);
    }
  }

  async function onSubmit(values: CompanySettingsFormData) {
    const titleChanged = values.title.trim() !== initialTitle.trim();
    const innChanged = values.inn !== initialInn;

    if (!titleChanged && !innChanged) {
      toast.message("Изменений нет.");
      return;
    }

    if (titleChanged) {
      const renameResult = await renameCompanyAction(companyId, values.title.trim());
      if (!renameResult.ok) {
        form.setError("root", { type: "server", message: renameResult.error });
        return;
      }
    }

    if (innChanged) {
      const innResult = await changeCompanyInnAction(companyId, values.inn);
      if (!innResult.ok) {
        form.setError("root", { type: "server", message: innResult.error });
        return;
      }
    }

    toast.success("Данные Company успешно сохранены.");
    form.reset({ title: values.title.trim(), inn: values.inn });
    router.refresh();
  }

  return (
    <TooltipProvider>
      <Card className="mx-auto w-full max-w-2xl border-border/80 shadow-sm">
        <CardHeader className="space-y-2">
          <div className="flex items-center gap-3">
            <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <Building2 className="size-5" />
            </div>
            <div>
              <CardTitle className="text-xl">Настройки Company</CardTitle>
              <CardDescription>
                Редактирование Title и Inn организации для элементов Organization в RegistrySet (XSD
                1.0.9).
              </CardDescription>
            </div>
          </div>
        </CardHeader>

        <CardContent>
          {isArchived ? (
            <div className="mb-4 rounded-md bg-amber-500/10 px-3 py-2 text-sm text-amber-800 dark:text-amber-200">
              Компания в архиве. Для изменения реквизитов восстановите её из архива.
            </div>
          ) : null}

          <form id="company-settings-form" onSubmit={form.handleSubmit(onSubmit)} method="POST">
            <FieldGroup>
              <Controller
                name="inn"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="company-inn-settings">ИНН (Inn)</FieldLabel>
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-start">
                      <InnInput
                        id="company-inn-settings"
                        value={field.value}
                        onChange={field.onChange}
                        onBlur={field.onBlur}
                        disabled={isArchived || form.formState.isSubmitting}
                        aria-invalid={fieldState.invalid}
                        className="sm:flex-1"
                      />
                      <Tooltip>
                        <TooltipTrigger
                          render={
                            <Button
                              type="button"
                              variant="outline"
                              disabled={
                                isArchived ||
                                isLookupLoading ||
                                form.formState.isSubmitting ||
                                !FNS_TITLE_LOOKUP_ENABLED
                              }
                              onClick={handleLookupTitleByInn}
                              className="shrink-0"
                            >
                              {isLookupLoading ? (
                                <Loader2 className="size-4 animate-spin" />
                              ) : (
                                <Search className="size-4" />
                              )}
                              Получить наименование по ИНН
                            </Button>
                          }
                        />
                        <TooltipContent side="bottom" className="max-w-xs text-center">
                          {FNS_TITLE_LOOKUP_ENABLED
                            ? "Запросить Title организации в сервисе ФНС по указанному Inn"
                            : "Интеграция с сервисом ФНС будет доступна в следующей версии (июнь 2026)"}
                        </TooltipContent>
                      </Tooltip>
                    </div>
                    <FieldDescription>
                      xs:string · 10 цифр (юрлицо) или 12 цифр (ИП), с проверкой контрольной суммы.
                    </FieldDescription>
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />

              <Controller
                name="title"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="company-title-settings">Наименование (Title)</FieldLabel>
                    <Input
                      {...field}
                      id="company-title-settings"
                      placeholder="ООО «Пример»"
                      disabled={isArchived || form.formState.isSubmitting}
                      aria-invalid={fieldState.invalid}
                      autoComplete="organization"
                    />
                    <FieldDescription>
                      Organization.Title / EmployerTitle в XML-реестре ЕИСОТ.
                    </FieldDescription>
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />
            </FieldGroup>
          </form>
        </CardContent>

        <CardFooter>
          <div className="flex w-full flex-col gap-3">
            {form.formState.errors.root && (
              <div className="rounded-md bg-destructive/10 p-2 text-center text-sm font-medium text-destructive">
                {form.formState.errors.root.message}
              </div>
            )}
            <Button
              type="submit"
              form="company-settings-form"
              disabled={isArchived || form.formState.isSubmitting}
              className="w-full cursor-pointer sm:w-auto"
            >
              {form.formState.isSubmitting ? (
                <>
                  <Loader2 className="size-4 animate-spin" />
                  Сохранение...
                </>
              ) : (
                "Сохранить изменения"
              )}
            </Button>
          </div>
        </CardFooter>
      </Card>
    </TooltipProvider>
  );
}
