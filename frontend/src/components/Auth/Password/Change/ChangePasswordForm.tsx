"use client";

import { z } from "zod";
import { ProfileDTO } from "@/interfaces/auth.interface";
import { useState } from "react";
import Link from "next/link";
import { ArrowLeft, MailCheck, Wrench } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Controller, useForm } from "react-hook-form";
import { Input } from "@/components/ui/input";
import { zodResolver } from "@hookform/resolvers/zod";
import { requestPasswordChange } from "@/actions/auth";

const schema = z
  .object({
    old_password: z
      .string()
      .min(8, "Пароль должен содержать минимум 8 символов.")
      .max(18, "Пароль должен содержать максимум 18 символов."),
    new_password: z
      .string()
      .min(8, "Пароль должен содержать минимум 8 символов.")
      .max(18, "Пароль должен содержать максимум 18 символов."),
    confirm_new_password: z
      .string()
      .min(8, "Пароль должен содержать минимум 8 символов.")
      .max(18, "Пароль должен содержать максимум 18 символов."),
  })
  .superRefine(({ confirm_new_password, new_password }, ctx) => {
    if (confirm_new_password !== new_password) {
      ctx.addIssue({
        code: "custom",
        message: "Пароли не совпадают",
        path: ["confirm_new_password"],
      });
    }
  });

type FormData = z.infer<typeof schema>;
interface ChangePasswordFormProps {
  profile: ProfileDTO;
}

export default function RequestChangePassword({ profile }: ChangePasswordFormProps) {
  const [isSuccess, setIsSuccess] = useState(false);

  const form = useForm({
    mode: "onSubmit",
    resolver: zodResolver(schema),
    defaultValues: {
      old_password: "",
      new_password: "",
      confirm_new_password: "",
    },
  });
  async function onSubmit(values: FormData) {
    const result = await requestPasswordChange(values.old_password, values.new_password);

    if (!result.ok) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }

    setIsSuccess(true);
  }

  if (isSuccess) {
    return (
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader className="space-y-4">
          <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
            <MailCheck className="h-10 w-10 text-green-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight">
            <h1>Пароль изменён</h1>
          </CardTitle>
          <CardDescription className="text-base">
            Пароль был изменен. На ваш адрес электронной почты отправлено письмо уведомление.
            <br />
            <strong className="text-foreground">{profile.email}</strong>.
          </CardDescription>
        </CardHeader>
        <CardFooter>
          <Button variant="link" className="w-full">
            <Link className="link" href="/user/profile">
              Вернуться в профиль
            </Link>
          </Button>
        </CardFooter>
      </Card>
    );
  }
  return (
    <div className="mx-auto max-w-md p-4 pt-12 md:p-8">
      <div className="mb-6">
        <Button
          variant="ghost"
          size="sm"
          className="pl-0 text-muted-foreground hover:bg-transparent hover:text-gray-900"
        >
          <Link href="/user/profile" className="inline-flex items-center">
            <ArrowLeft className="mr-2 size-4" />
            <span>Назад в профиль</span>
          </Link>
        </Button>
      </div>
      <Card className="shadow-sm">
        <CardHeader className="space-y-4">
          <div className="mx-auto w-fit rounded-full bg-blue-100 p-4">
            <Wrench className="h-10 w-10 text-blue-600" />
          </div>
          <CardTitle className="text-center text-2xl font-semibold tracking-tight">
            Изменение пароля
          </CardTitle>
          <CardDescription className="text-center">
            Введите новый надежный пароль и подтвердите его.
          </CardDescription>
        </CardHeader>

        <CardContent>
          <form
            id="change-password-form"
            onSubmit={(e) => {
              void form.handleSubmit(onSubmit)(e);
            }}
            method="POST"
          >
            <FieldGroup>
              <Controller
                name="old_password"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="old-password">Старый пароль</FieldLabel>
                    <Input
                      {...field}
                      id="old-password"
                      type="password"
                      placeholder="Укажите старый пароль"
                      aria-invalid={fieldState.invalid}
                      autoComplete="old-password"
                      value={field.value}
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />

              <Controller
                name="new_password"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="new-password">Новый пароль</FieldLabel>
                    <Input
                      {...field}
                      id="new-password"
                      type="password"
                      value={field.value}
                      placeholder="Укажите новый пароль"
                      aria-invalid={fieldState.invalid}
                      autoComplete="new-password"
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />

              <Controller
                name="confirm_new_password"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="confirm-new-password">Подтвердите новый пароль</FieldLabel>
                    <Input
                      {...field}
                      id="confirm-new-password"
                      type="password"
                      value={field.value}
                      placeholder="Подтвердите новый пароль"
                      aria-invalid={fieldState.invalid}
                      autoComplete="confirm-new-password"
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />
            </FieldGroup>
          </form>
        </CardContent>

        <CardFooter className="flex-col">
          {form.formState.errors.root && (
            <div className="mb-4 w-full rounded-md bg-destructive/10 p-3 text-center text-sm font-medium text-destructive">
              {form.formState.errors.root.message}
            </div>
          )}
          <Button
            type="submit"
            form="change-password-form"
            disabled={form.formState.isSubmitting}
            className="w-full cursor-pointer"
          >
            {form.formState.isSubmitting ? "Отправка..." : "Продолжить"}
          </Button>
        </CardFooter>
      </Card>
    </div>
  );
}
