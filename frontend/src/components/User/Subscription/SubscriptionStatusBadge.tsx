import { Crown } from "lucide-react";

import type { SubscriptionAccess } from "@/interfaces/subscription.interface";
import { daysRemainingUntil, formatDaysLabel } from "@/interfaces/subscription.interface";

const PLAN_LABELS: Record<string, string> = {
  basic: "Базовый",
  extended: "Расширенный",
  trial: "Пробный период",
};

interface SubscriptionStatusBadgeProps {
  readonly access: SubscriptionAccess;
}

export function SubscriptionStatusBadge({ access }: SubscriptionStatusBadgeProps) {
  if (!access.hasAccess) {
    return (
      <span className="inline-flex items-center gap-1.5 rounded-md bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground ring-1 ring-border ring-inset">
        <Crown className="size-3.5" />
        Подписка не активна
      </span>
    );
  }

  if (access.plan === "trial" && access.periodEnd) {
    const remaining = daysRemainingUntil(access.periodEnd);
    return (
      <span className="inline-flex items-center gap-1.5 rounded-md bg-sky-500/10 px-2.5 py-1 text-xs font-medium text-sky-700 ring-1 ring-sky-500/20 ring-inset dark:text-sky-400">
        <Crown className="size-3.5" />
        Пробный период (осталось {formatDaysLabel(remaining)})
      </span>
    );
  }

  const planLabel = access.plan ? (PLAN_LABELS[access.plan] ?? access.plan) : "—";
  const endDate = access.periodEnd ? new Date(access.periodEnd).toLocaleDateString("ru-RU") : null;

  return (
    <span className="inline-flex items-center gap-1.5 rounded-md bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-500/20 ring-inset dark:text-emerald-400">
      <Crown className="size-3.5" />
      Plan: {planLabel}
      {endDate ? ` · до ${endDate}` : ""}
    </span>
  );
}
