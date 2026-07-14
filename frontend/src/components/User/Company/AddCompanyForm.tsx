"use client";

import { z } from "zod";
import Link from "next/link";
import { Controller, useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { toast } from "sonner";
import { addCompanyAction, fetchCompanyTitleByInnAction } from "@/actions/company";
import { FNS_TITLE_LOOKUP_ENABLED, normalizeInn, validateInn } from "@/lib/inn";
import { useRouter } from "next/navigation";
import type { SubscriptionPlan } from "@/interfaces/subscription.interface";
import { Loader2, Search } from "lucide-react";
import { useState } from "react";

const schema = z.object({
  name: z
    .string()
    .min(1, "Название организации обязательно для заполнения.")
    .max(255, "Название не должно превышать 255 символов."),
  inn: z
    .string()
    .transform(normalizeInn)
    .superRefine((val, ctx) => {
      const error = validateInn(val);
      if (error) {
        ctx.addIssue({
          code: "custom",
          message: error,
        });
      }
    }),
});

type AddCompanyFormData = z.infer<typeof schema>;

interface AddCompanyFormProps {
  readonly plan: SubscriptionPlan | null;
  readonly totalCompanyCount: number;
}

function isCompanyLimitReached(plan: SubscriptionPlan | null, totalCompanyCount: number): boolean {
  if (plan === "extended") {
    return false;
  }

  return totalCompanyCount >= 1;
}

export default function AddCompanyForm({ plan, totalCompanyCount }: AddCompanyFormProps) {
  const companyLimitReached = isCompanyLimitReached(plan, totalCompanyCount);
  const [isLookupLoading, setIsLookupLoading] = useState(false);

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
        form.setValue("name", result.data.title, { shouldDirty: true, shouldValidate: true });
        toast.success("Наименование получено по ИНН.");
      }
    } finally {
      setIsLookupLoading(false);
    }
  }
  const form = useForm<AddCompanyFormData>({
    mode: "onBlur",
    resolver: zodResolver(schema),
    defaultValues: {
      name: "",
      inn: "",
    },
  });
  const router = useRouter();
  async function onSubmit(values: AddCompanyFormData) {
    const result = await addCompanyAction({
      name: values.name,
      inn: values.inn,
    });

    if (!result.ok) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }

    toast.success("Компания успешно добавлена!");
    form.reset();
    router.push("/user/company");
  }

  const submitButton = (
    <Button
      type="submit"
      form="add-company-form"
      disabled={form.formState.isSubmitting || companyLimitReached}
      className="w-full cursor-pointer py-2"
    >
      {form.formState.isSubmitting ? "Загрузка..." : "Добавить компанию"}
    </Button>
  );

  return (
    <Card className="mx-auto w-full max-w-lg shadow-sm">
      <CardHeader className="space-y-2">
        <CardTitle className="text-2xl font-semibold tracking-tight">Добавить компанию</CardTitle>
        <CardDescription>Укажите название и ИНН организации, проводившей обучение.</CardDescription>
      </CardHeader>
      <CardContent>
        <form
          id="add-company-form"
          onSubmit={(e) => {
            void form.handleSubmit(onSubmit)(e);
          }}
          method="POST"
        >
          <FieldGroup>
            <Controller
              name="name"
              control={form.control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid}>
                  <FieldLabel htmlFor="company-name">Название организации</FieldLabel>
                  <Input
                    {...field}
                    id="company-name"
                    value={field.value}
                    placeholder="ООО «Пример»"
                    aria-invalid={fieldState.invalid}
                    autoComplete="organization"
                  />
                  {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                </Field>
              )}
            />
            <Tooltip>
              <TooltipTrigger
                render={
                  <Button
                    type="button"
                    variant="outline"
                    disabled={
                      isLookupLoading || form.formState.isSubmitting || !FNS_TITLE_LOOKUP_ENABLED
                    }
                    onClick={() => {
                      void handleLookupTitleByInn();
                    }}
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
            <Controller
              name="inn"
              control={form.control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid}>
                  <FieldLabel htmlFor="company-inn">ИНН</FieldLabel>
                  <Input
                    {...field}
                    id="company-inn"
                    value={field.value}
                    placeholder="1234567890"
                    aria-invalid={fieldState.invalid}
                    inputMode="numeric"
                  />
                  {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                </Field>
              )}
            />
          </FieldGroup>
        </form>
      </CardContent>
      <CardFooter>
        <div className="flex w-full flex-col">
          <div className="space-y-4 pt-4">
            {companyLimitReached && (
              <div className="rounded-md bg-muted p-3 text-center text-sm text-muted-foreground">
                Удалите существующую компанию или{" "}
                <Link href="/user/subscription" className="font-medium text-primary underline">
                  обновите тариф
                </Link>
                .
              </div>
            )}
            {form.formState.errors.root && (
              <div className="rounded-md bg-destructive/10 p-2 text-center text-sm font-medium text-destructive">
                {form.formState.errors.root.message}
              </div>
            )}
            {companyLimitReached ? (
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger
                    render={<span className="inline-block w-full">{submitButton}</span>}
                  />
                  <TooltipContent>Удалите существующую компанию или обновите тариф</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            ) : (
              submitButton
            )}
          </div>
        </div>
      </CardFooter>
    </Card>
  );
}
