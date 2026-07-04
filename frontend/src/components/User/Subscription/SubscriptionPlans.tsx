"use client";

import { useState } from "react";
import Link from "next/link";
import { Check, Crown, Loader2 } from "lucide-react";
import { toast } from "sonner";

import { activateSubscriptionAction } from "@/actions/subscription";
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
    title: "Базовый Plan",
    price: "990 ₽ / мес.",
    durationDays: 30,
    features: [
      "RegistrySet XML для всех ваших компаний",
      "До 500 записей реестра в Subscription Period",
      "Проверка СНИЛС и ИНН по контрольным суммам",
    ],
  },
  {
    id: "premium",
    title: "Премиум Plan",
    price: "2 490 ₽ / мес.",
    durationDays: 30,
    features: [
      "Неограниченное формирование RegistrySet XML",
      "Доступ ко всем компаниям аккаунта",
      "Приоритетная поддержка по актуальной редакции XSD",
    ],
  },
];

interface SubscriptionPlansProps {
  readonly initialAccess: SubscriptionAccess;
}

export function SubscriptionPlans({ initialAccess }: SubscriptionPlansProps) {
  const [access, setAccess] = useState(initialAccess);
  const [loadingPlan, setLoadingPlan] = useState<SubscriptionPlan | null>(null);

  const handleActivate = async (plan: PlanOption) => {
    setLoadingPlan(plan.id);
    try {
      const result = await activateSubscriptionAction({
        planId: plan.id,
        durationDays: plan.durationDays,
      });

      if (!result.ok) {
        toast.error(result.error ?? "Не удалось активировать User Subscription.");
        return;
      }

      toast.success(`User Subscription «${plan.title}» успешно активирована.`);
      setAccess({
        hasAccess: true,
        plan: plan.id,
        status: "active",
        periodStart: new Date().toISOString().slice(0, 10),
        periodEnd: new Date(Date.now() + plan.durationDays * 86400000).toISOString().slice(0, 10),
      });
    } finally {
      setLoadingPlan(null);
    }
  };

  return (
    <div className="space-y-6">
      <div className="rounded-lg border bg-muted/30 p-4 text-sm text-muted-foreground">
        {access.hasAccess ? (
          <p>
            Active Status: активна · Plan:{" "}
            <strong>{access.plan === "premium" ? "Премиум" : "Базовый"}</strong>
            {access.periodEnd && (
              <>
                {" "}
                · Subscription Period до{" "}
                <strong>{new Date(access.periodEnd).toLocaleDateString("ru-RU")}</strong>
              </>
            )}
            . Подписка действует для всех компаний вашего аккаунта.
          </p>
        ) : (
          <p>
            User Subscription разблокирует выгрузку реестра обученных лиц (RegistrySet) в ЕИСОТ для
            всех ваших компаний. Выберите тарифный Plan ниже.
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
                  variant={plan.id === "premium" ? "default" : "outline"}
                  disabled={isCurrent || loadingPlan !== null}
                  onClick={() => { void handleActivate(plan); }}
                >
                  {loadingPlan === plan.id ? (
                    <>
                      <Loader2 className="mr-2 size-4 animate-spin" />
                      Активация...
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
        Актуальная редакция требований к XML-реестру — июнь 2026 года. Оплата будет подключена на
        следующем этапе; сейчас подписка активируется в демо-режиме.
      </p>

      <div>
        <Button variant="ghost" asChild className="cursor-pointer">
          <Link href="/user/company">Перейти к компаниям</Link>
        </Button>
      </div>
    </div>
  );
}
