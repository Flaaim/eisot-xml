import { Crown, ShieldAlert } from "lucide-react";
import Link from "next/link";

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
}

/**
 * Модальное окно «Доступ ограничен» (актуальная редакция, июнь 2026).
 * User Subscription разблокирует экспорт RegistrySet для всех компаний аккаунта.
 */
export function AccessRestrictedDialog({ open, onOpenChange }: AccessRestrictedDialogProps) {
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <div className="flex h-12 w-12 items-center justify-center rounded-full bg-amber-500/10 text-amber-600 mb-2">
            <ShieldAlert className="h-6 w-6" />
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
        <DialogFooter>
          <Button variant="outline" onClick={() => onOpenChange(false)} className="cursor-pointer">
            Закрыть
          </Button>
          <Button asChild className="cursor-pointer">
            <Link href="/user/subscription">
              <Crown className="mr-2 h-4 w-4" />
              Тарифы и подписка
            </Link>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
