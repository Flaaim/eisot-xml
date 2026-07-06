"use client";

import { z } from "zod";
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
import { toast } from "sonner";
import { addCompanyAction } from "@/actions/company";
import { normalizeInn, validateInn } from "@/lib/inn";
import { useRouter } from "next/navigation";

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

export default function AddCompanyForm() {
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
            {form.formState.errors.root && (
              <div className="rounded-md bg-destructive/10 p-2 text-center text-sm font-medium text-destructive">
                {form.formState.errors.root.message}
              </div>
            )}
            <Button
              type="submit"
              form="add-company-form"
              disabled={form.formState.isSubmitting}
              className="w-full cursor-pointer py-2"
            >
              {form.formState.isSubmitting ? "Загрузка..." : "Добавить компанию"}
            </Button>
          </div>
        </div>
      </CardFooter>
    </Card>
  );
}
