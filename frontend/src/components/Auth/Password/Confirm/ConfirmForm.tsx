"use client";

import { JSX, Suspense, useMemo, useState } from "react";
import { useSearchParams } from "next/navigation";
import { z } from "zod";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { ArrowLeft, Check, Loader2, Wrench, XCircle } from "lucide-react";
import { Controller, useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import Link from "next/link";
import { passwordResetConfirm } from "@/actions/auth";

const tokenSchema = z.uuid("Неверный формат токена");

const schema = z
  .object({
    password: z
      .string()
      .min(8, "Пароль должен содержать минимум 8 символов.")
      .max(18, "Пароль должен содержать максимум 18 символов."),
    confirm_password: z
      .string()
      .min(8, "Пароль должен содержать минимум 8 символов.")
      .max(18, "Пароль должен содержать максимум 18 символов."),
  })
  .superRefine(({ confirm_password, password }, ctx) => {
    if (confirm_password !== password) {
      ctx.addIssue({
        code: "custom",
        message: "Пароли не совпадают",
        path: ["confirm_password"],
      });
    }
  });

type FormData = z.infer<typeof schema>;

const ResetPasswordFormContent = (): JSX.Element => {
  const searchParams = useSearchParams();
  const token = searchParams.get("token");

  const [isSuccess, setIsSuccess] = useState<boolean>(false);

  const tokenError = useMemo((): string | null => {
    if (!token) {
      return "Токен отсутствует в ссылке.";
    }

    const parsed = tokenSchema.safeParse(token);
    if (!parsed.success) {
      return parsed.error.issues[0]?.message ?? "Неверный формат токена";
    }

    return null;
  }, [token]);

  const form = useForm({
    mode: "onSubmit",
    resolver: zodResolver(schema),
    defaultValues: {
      password: "",
      confirm_password: "",
    },
  });

  async function onSubmit(values: FormData) {
    if (!token) return;
    const result = await passwordResetConfirm(token, values.password);

    if (!result.ok) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }
    setIsSuccess(true);
  }

  if (tokenError) {
    return (
      <div className="mx-auto max-w-md p-4 pt-12 md:p-8">
        <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
          <CardHeader className="space-y-4">
            <div className="mx-auto w-fit rounded-full bg-red-100 p-4">
              <XCircle className="size-10 text-red-600" />
            </div>
            <CardTitle className="text-2xl font-semibold tracking-tight">
              <h1>Ошибка доступа</h1>
            </CardTitle>
            <CardDescription className="text-base text-red-600">{tokenError}</CardDescription>
          </CardHeader>
          <CardFooter className="justify-center">
            <Button variant="outline">
              <Link href="/join/login">Вернуться на страницу входа</Link>
            </Button>
          </CardFooter>
        </Card>
      </div>
    );
  }
  if (isSuccess) {
    return (
      <div className="mx-auto max-w-md p-4 pt-12 md:p-8">
        <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
          <CardHeader className="space-y-4">
            <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
              <Check className="size-10 text-green-600" />
            </div>
            <CardTitle className="text-2xl font-semibold tracking-tight">Успешно</CardTitle>
            <CardDescription className="text-base">
              Ваш пароль успешно изменен. Используйте его для входа на сайт.
            </CardDescription>
          </CardHeader>
          <CardFooter className="justify-center">
            <Button variant="outline">
              <Link href="/join/login">Вернуться на страницу входа</Link>
            </Button>
          </CardFooter>
        </Card>
      </div>
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
            <span>Назад на страницу входа</span>
          </Link>
        </Button>
      </div>
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader className="space-y-4">
          <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
            <Wrench className="size-10 text-green-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight">
            <h1>Восстановление доступа</h1>
          </CardTitle>
          <CardDescription>
            Для того чтобы восстановить доступ к аккаунту, необходимо придумать новый надежный
            пароль и подтвердить его.
          </CardDescription>
        </CardHeader>
        <CardContent className="text-start">
          <form id="new-password-form" onSubmit={(e) => { void form.handleSubmit(onSubmit)(e); }} method="POST">
            <FieldGroup>
              <Controller
                name="password"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="new-password">Пароль</FieldLabel>
                    <Input
                      {...field}
                      id="new-password"
                      type="password"
                      value={field.value}
                      placeholder="Укажите пароль"
                      aria-invalid={fieldState.invalid}
                      autoComplete="current-password"
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />

              <Controller
                name="confirm_password"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="join-confirm-password">Подтвердите пароль</FieldLabel>
                    <Input
                      {...field}
                      id="join-confirm-password"
                      type="password"
                      value={field.value}
                      placeholder="Подтвердите пароль"
                      aria-invalid={fieldState.invalid}
                      autoComplete="current-password"
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />
            </FieldGroup>
          </form>
        </CardContent>
        <CardFooter className="text-start">
          <div className="flex flex-col">
            <div className="space-y-4 pt-4">
              {form.formState.errors.root && (
                <div className="rounded-md bg-destructive/10 p-2 text-center text-sm font-medium text-destructive">
                  {form.formState.errors.root.message}
                </div>
              )}
              <Button
                type="submit"
                form="new-password-form"
                disabled={form.formState.isSubmitting}
                className="cursor-pointer py-2"
              >
                {form.formState.isSubmitting ? "Загрузка..." : "Обновить пароль"}
              </Button>
            </div>
          </div>
        </CardFooter>
      </Card>
    </div>
  );
};

export default function ResetPasswordForm() {
  return (
    <Suspense
      fallback={
        <div className="flex justify-center p-8">
          <Loader2 className="size-8 animate-spin text-gray-400" />
        </div>
      }
    >
      <ResetPasswordFormContent />
    </Suspense>
  );
}
