"use client";

import { Suspense, useEffect, useState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Check, Clock3, Loader2, XCircle } from "lucide-react";

import { getPaymentStatusAction } from "@/actions/subscription";
import type { PaymentStatus } from "@/interfaces/subscription.interface";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

function PaymentCallbackContent() {
  const searchParams = useSearchParams();
  const paymentId = searchParams.get("paymentId");
  const [status, setStatus] = useState<PaymentStatus | "loading" | "unknown">(
    paymentId ? "loading" : "unknown"
  );
  const [error, setError] = useState<string | null>(
    paymentId ? null : "Идентификатор платежа не найден в URL."
  );

  useEffect(() => {
    if (!paymentId) {
      return;
    }

    let attempts = 0;
    const maxAttempts = 10;

    const pollStatus = async () => {
      const result = await getPaymentStatusAction(paymentId);

      if (!result.ok || !result.data) {
        setStatus("unknown");
        setError(result.error ?? "Не удалось получить статус платежа.");
        return;
      }

      setStatus(result.data.status);

      if (result.data.status === "pending" && attempts < maxAttempts) {
        attempts += 1;
        window.setTimeout(() => {
          void pollStatus();
        }, 2000);
      }
    };

    void pollStatus();
  }, [paymentId]);

  if (status === "loading" || status === "pending") {
    return (
      <Card className="mx-auto w-full max-w-lg shadow-sm">
        <CardHeader className="text-center">
          <div className="mx-auto w-fit rounded-full bg-blue-100 p-4">
            <Clock3 className="size-10 text-blue-600" />
          </div>
          <CardTitle>Платёж обрабатывается</CardTitle>
          <CardDescription>
            ЮKassa подтверждает оплату. Обычно это занимает несколько секунд.
          </CardDescription>
        </CardHeader>
        <CardContent className="flex justify-center pb-6">
          <Loader2 className="size-8 animate-spin text-muted-foreground" />
        </CardContent>
      </Card>
    );
  }

  if (status === "succeeded") {
    return (
      <Card className="mx-auto w-full max-w-lg shadow-sm">
        <CardHeader className="text-center">
          <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
            <Check className="size-10 text-green-600" />
          </div>
          <CardTitle>Оплата успешна</CardTitle>
          <CardDescription>
            User Subscription активирована. Теперь вы можете формировать RegistrySet XML для всех
            ваших компаний.
          </CardDescription>
        </CardHeader>
        <CardFooter className="justify-center gap-3">
          <Button nativeButton={false} render={<Link href="/user/subscription" />}>
            К тарифам
          </Button>
          <Button variant="outline" nativeButton={false} render={<Link href="/user/company" />}>
            К компаниям
          </Button>
        </CardFooter>
      </Card>
    );
  }

  return (
    <Card className="mx-auto w-full max-w-lg shadow-sm">
      <CardHeader className="text-center">
        <div className="mx-auto w-fit rounded-full bg-red-100 p-4">
          <XCircle className="size-10 text-red-600" />
        </div>
        <CardTitle>Оплата не завершена</CardTitle>
        <CardDescription>
          {error ?? "Платёж отменён или ещё не подтверждён. Попробуйте оформить подписку снова."}
        </CardDescription>
      </CardHeader>
      <CardFooter className="justify-center">
        <Button nativeButton={false} render={<Link href="/user/subscription" />}>
          Вернуться к тарифам
        </Button>
      </CardFooter>
    </Card>
  );
}

export default function SubscriptionCallbackPage() {
  return (
    <div className="mx-auto max-w-4xl p-4 md:p-8">
      <Suspense
        fallback={
          <div className="flex justify-center p-8">
            <Loader2 className="size-8 animate-spin text-muted-foreground" />
          </div>
        }
      >
        <PaymentCallbackContent />
      </Suspense>
    </div>
  );
}
