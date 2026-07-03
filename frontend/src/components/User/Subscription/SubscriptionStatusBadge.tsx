import { Crown } from "lucide-react";

import type { SubscriptionAccess } from "@/interfaces/subscription.interface";

const PLAN_LABELS: Record<string, string> = {
  basic: "Базовый",
  premium: "Премиум",
};

interface SubscriptionStatusBadgeProps {
  readonly access: SubscriptionAccess;
}

export function SubscriptionStatusBadge({ access }: SubscriptionStatusBadgeProps) {
  if (!access.hasAccess) {
    return (
      <span className="inline-flex items-center gap-1.5 rounded-md bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground ring-1 ring-inset ring-border">
        <Crown className="h-3.5 w-3.5" />
        User Subscription не активна
      </span>
    );
  }

  const planLabel = access.plan ? (PLAN_LABELS[access.plan] ?? access.plan) : "—";
  const endDate = access.periodEnd ? new Date(access.periodEnd).toLocaleDateString("ru-RU") : null;

  return (
    <span className="inline-flex items-center gap-1.5 rounded-md bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-500/20">
      <Crown className="h-3.5 w-3.5" />
      Plan: {planLabel}
      {endDate ? ` · до ${endDate}` : ""}
    </span>
  );
}
