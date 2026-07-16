"use client";

import { useState } from "react";
import { Crown, Gift, Loader2, ShieldAlert } from "lucide-react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { toast } from "sonner";

import { activateTrialAction } from "@/actions/subscription";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";

interface AccessRestrictedDialogProps {
  readonly open: boolean;
  readonly onOpenChange: (open: boolean) => void;
  readonly trialAvailable?: boolean;
}

/**
 * Модальное окно «Доступ ограничен».
 * User Subscription разблокирует экспорт RegistrySet для всех компаний аккаунта.
 */
export function AccessRestrictedDialog({
  open,
  onOpenChange,
  trialAvailable = false,
}: AccessRestrictedDialogProps) {
  const router = useRouter();
  const [trialLoading, setTrialLoading] = useState(false);

  const handleActivateTrial = async () => {
    setTrialLoading(true);
    try {
      const result = await activateTrialAction();
      if (!result.ok) {
        toast.error(result.error ?? "Не удалось активировать Trial Subscription.");
        return;
      }

      toast.success("Trial Subscription активирована на 3 дня.");
      onOpenChange(false);
      router.refresh();
    } finally {
      setTrialLoading(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <div className="mb-2 flex size-12 items-center justify-center rounded-full bg-amber-500/10 text-amber-600">
            <ShieldAlert className="size-6" />
          </div>
          <DialogTitle>Доступ ограничен</DialogTitle>
          <DialogDescription className="leading-relaxed">
            Формирование XML-файла реестра обученных лиц (<strong>RegistrySet</strong>) для загрузки
            в федеральную систему ЕИСОТ доступно только при активной User Subscription (Active
            Status) на выбранный тарифный Plan.
          </DialogDescription>
        </DialogHeader>
        <p className="text-sm text-muted-foreground">
          Оформите подписку для вашего аккаунта — она разблокирует выгрузку протоколов обучения во
          всех компаниях, которыми вы владеете, в формате schema.xsd Минтруда России.
        </p>
        {trialAvailable && (
          <div className="rounded-lg border border-sky-500/30 bg-sky-500/5 p-3 text-sm text-muted-foreground">
            Или активируйте бесплатный Trial Subscription на 3 дня — с лимитом 1 компании и доступом
            к экспорту RegistrySet.
          </div>
        )}
        <DialogFooter>
          <Button
            variant="outline"
            onClick={() => {
              onOpenChange(false);
            }}
            className="cursor-pointer"
          >
            Закрыть
          </Button>
          {trialAvailable ? (
            <Button
              className="cursor-pointer"
              disabled={trialLoading}
              onClick={() => {
                void handleActivateTrial();
              }}
            >
              {trialLoading ? (
                <>
                  <Loader2 className="mr-2 size-4 animate-spin" />
                  Активация...
                </>
              ) : (
                <>
                  <Gift className="mr-2 size-4" />
                  Активировать 3 дня
                </>
              )}
            </Button>
          ) : (
            <Button
              nativeButton={false}
              render={<Link href="/user/subscription" />}
              className="cursor-pointer"
            >
              <Crown className="mr-2 size-4" />
              Тарифы и подписка
            </Button>
          )}
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
