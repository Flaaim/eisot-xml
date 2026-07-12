"use client";

import { useState } from "react";
import Link from "next/link";
import { Check, Crown, Loader2 } from "lucide-react";
import { toast } from "sonner";

import { createPaymentAction } from "@/actions/subscription";
import type { SubscriptionAccess, SubscriptionPlan } from "@/interfaces/subscription.interface";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

interface PlanOption {
  id: SubscriptionPlan;
  title: string;
  price: string;
  durationDays: number;
  features: string[];
}

const PLANS: PlanOption[] = [
  {
    id: "basic",
    title: "Базовый",
    price: "490 ₽ / мес.",
    durationDays: 30,
    features: [
      "1 компания",
      "RegistrySet XML для вашей компании",
      "До 500 записей реестра в период подписки",
      "Проверка СНИЛС и ИНН по контрольным суммам",
    ],
  },
  {
    id: "extended",
    title: "Расширенный",
    price: "1 490 ₽ / мес.",
    durationDays: 30,
    features: [
      "Безлимит компаний",
      "RegistrySet XML для всех ваших компаний",
      "До 500 записей реестра в период подписки",
      "Проверка СНИЛС и ИНН по контрольным суммам",
    ],
  },
];

interface SubscriptionPlansProps {
  readonly initialAccess: SubscriptionAccess;
}

export function SubscriptionPlans({ initialAccess }: SubscriptionPlansProps) {
  const [access] = useState(initialAccess);
  const [loadingPlan, setLoadingPlan] = useState<SubscriptionPlan | null>(null);

  const handleCheckout = async (plan: PlanOption) => {
    setLoadingPlan(plan.id);

    try {
      const returnUrl = `${window.location.origin}/user/subscription/callback`;
      const result = await createPaymentAction({
        planId: plan.id,
        durationDays: plan.durationDays,
        returnUrl,
      });

      if (!result.ok || !result.data) {
        toast.error(result.error ?? "Не удалось инициировать оплату User Subscription.");
        return;
      }

      window.location.href = result.data.confirmationUrl;
    } finally {
      setLoadingPlan(null);
    }
  };

  return (
    <div className="space-y-6">
      <div className="rounded-lg border bg-muted/30 p-4 text-sm text-muted-foreground">
        {access.hasAccess ? (
          <p>
            Active Status: активна · План:{" "}
            <strong>{access.plan === "extended" ? "Расширенный" : "Базовый"}</strong>
            {access.periodEnd && (
              <>
                {" "}
                · Период подписки до{" "}
                <strong>{new Date(access.periodEnd).toLocaleDateString("ru-RU")}</strong>
              </>
            )}
            . Подписка действует для всех компаний вашего аккаунта.
          </p>
        ) : (
          <p>
            Подписка разблокирует выгрузку реестра обученных лиц (RegistrySet) в ЕИСОТ для ваших
            компаний. Выберите тарифный план и перейдите к оплате через ЮKassa.
          </p>
        )}
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        {PLANS.map((plan) => {
          const isCurrent = access.hasAccess && access.plan === plan.id;

          return (
            <Card
              key={plan.id}
              className={isCurrent ? "shadow-md ring-2 ring-primary/30" : "shadow-sm"}
            >
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Crown className="size-5 text-primary" />
                  {plan.title}
                </CardTitle>
                <CardDescription>{plan.price}</CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="space-y-2 text-sm text-muted-foreground">
                  {plan.features.map((feature) => (
                    <li key={feature} className="flex items-start gap-2">
                      <Check className="mt-0.5 size-4 shrink-0 text-emerald-600" />
                      <span>{feature}</span>
                    </li>
                  ))}
                </ul>
              </CardContent>
              <CardFooter>
                <Button
                  className="w-full cursor-pointer"
                  variant={plan.id === "extended" ? "default" : "outline"}
                  disabled={isCurrent || loadingPlan !== null}
                  onClick={() => {
                    void handleCheckout(plan);
                  }}
                >
                  {loadingPlan === plan.id ? (
                    <>
                      <Loader2 className="mr-2 size-4 animate-spin" />
                      Переход к оплате...
                    </>
                  ) : isCurrent ? (
                    "Текущий Plan"
                  ) : (
                    "Оформить подписку"
                  )}
                </Button>
              </CardFooter>
            </Card>
          );
        })}
      </div>

      <p className="text-xs text-muted-foreground">
        Оплата обрабатывается через ЮKassa. После подтверждения платежа Подписка активируется
        автоматически для всех компаний вашего аккаунта. Приобретая доступ вы соглашаетесь с{" "}
        <Link className="link" href="/user/terms">
          условиями использования
        </Link>
      </p>

      <div>
        <Button
          variant="ghost"
          nativeButton={false}
          render={<Link href="/user/company" />}
          className="cursor-pointer"
        >
          Перейти к компаниям
        </Button>
      </div>
    </div>
  );
}
