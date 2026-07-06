import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { KeyRound, Mail, Shield, UserIcon, Crown } from "lucide-react";
import { Button } from "@/components/ui/button";
import { fetchUser } from "@/actions/auth";
import { checkSubscriptionAccessAction } from "@/actions/subscription";
import { SubscriptionStatusBadge } from "@/components/User/Subscription/SubscriptionStatusBadge";
import { redirect } from "next/navigation";
import Link from "next/link";

export default async function ProfilePage() {
  let profile;
  try {
    profile = await fetchUser();
  } catch (error) {
    console.error("Ошибка авторизации в лейауте, перенаправление...", error);
    redirect("/join/login");
  }

  const accessResult = await checkSubscriptionAccessAction();
  const subscriptionAccess = accessResult.data ?? {
    hasAccess: false,
    plan: null,
    status: null,
    periodStart: null,
    periodEnd: null,
  };

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 md:p-8">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Настройки профиля</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Управляйте своими личными данными и настройками безопасности.
        </p>
      </div>
      <div className="grid gap-6 md:grid-cols-2">
        <Card className="shadow-sm">
          <CardHeader>
            <div className="mb-1 flex items-center gap-2">
              <UserIcon className="size-5 text-blue-600" />
              <CardTitle className="text-xl">Личные данные</CardTitle>
            </div>
            <CardDescription>Основная информация о вашем аккаунте.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="space-y-1">
              <p className="text-sm leading-none font-medium text-muted-foreground">
                ID пользователя
              </p>
              <p className="w-fit rounded-md bg-gray-50 p-2 font-mono text-sm text-gray-600">
                {profile.id}
              </p>
            </div>

            <div className="space-y-1">
              <p className="text-sm leading-none font-medium text-muted-foreground">
                Имя и Фамилия
              </p>
              <p className="text-base font-medium">{profile.name ?? "Не указано"}</p>
            </div>
            <div className="pt-2">
              <Button variant="outline" size="sm" disabled>
                Редактировать профиль
              </Button>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <div className="mb-1 flex items-center gap-2">
              <Shield className="size-5 text-green-600" />
              <CardTitle className="text-xl">Безопасность</CardTitle>
            </div>
            <CardDescription>Управление email-адресом и паролем.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="flex flex-col justify-between gap-4 border-b pb-4 sm:flex-row sm:items-center">
              <div className="space-y-1">
                <div className="flex items-center gap-2">
                  <Mail className="size-4 text-muted-foreground" />
                  <p className="text-sm leading-none font-medium text-muted-foreground">
                    Email адрес
                  </p>
                </div>
                <p className="pl-6 text-base font-medium">{profile.email}</p>
              </div>
              <Button
                variant="secondary"
                size="sm"
                nativeButton={false}
                render={<Link href="/user/profile/change-email" />}
              >
                Изменить email
              </Button>
            </div>

            <div className="flex flex-col justify-between gap-4 pt-2 sm:flex-row sm:items-center">
              <div className="space-y-1">
                <div className="flex items-center gap-2">
                  <KeyRound className="size-4 text-muted-foreground" />
                  <p className="text-sm leading-none font-medium text-muted-foreground">Пароль</p>
                </div>
                <p className="pl-6 text-base font-medium">••••••••••••</p>
              </div>
              <Button
                variant="secondary"
                size="sm"
                nativeButton={false}
                render={<Link href="/user/dashboard/profile/change-password" />}
              >
                Изменить пароль
              </Button>
            </div>
          </CardContent>
        </Card>
        <Card className="shadow-sm md:col-span-2">
          <CardHeader>
            <div className="mb-1 flex items-center gap-2">
              <Crown className="size-5 text-primary" />
              <CardTitle className="text-xl">User Subscription</CardTitle>
            </div>
            <CardDescription>
              Подписка аккаунта для формирования RegistrySet XML во всех ваших компаниях.
            </CardDescription>
          </CardHeader>
          <CardContent className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <SubscriptionStatusBadge access={subscriptionAccess} />
            <Button
              variant="secondary"
              size="sm"
              nativeButton={false}
              render={<Link href="/user/subscription" />}
            >
              Управление подпиской
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
