import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import type { AdminSubscriptionStats } from "@/interfaces/admin.interface";
import { Users, UserPlus, Crown, BadgeCheck } from "lucide-react";

interface StatsCardsProps {
  readonly stats: AdminSubscriptionStats;
}

export function StatsCards({ stats }: StatsCardsProps) {
  const cards = [
    {
      title: "Всего регистраций",
      value: stats.totalUsers,
      hint: `+${stats.registrationsLast30Days} за 30 дней`,
      icon: Users,
    },
    {
      title: "Регистрации за 30 дней",
      value: stats.registrationsLast30Days,
      hint: "Новые User",
      icon: UserPlus,
    },
    {
      title: "Active Subscriptions",
      value: stats.activeSubscriptions,
      hint: `+${stats.activeSubscriptionsLast30Days} за 30 дней`,
      icon: Crown,
    },
    {
      title: "Plan Basic / Extended",
      value: `${stats.activeBasicPlan} / ${stats.activeExtendedPlan}`,
      hint: "Активные тарифные Plan",
      icon: BadgeCheck,
    },
  ];

  return (
    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      {cards.map((card) => (
        <Card key={card.title} className="shadow-sm">
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              {card.title}
            </CardTitle>
            <card.icon className="size-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-semibold tracking-tight">{card.value}</div>
            <p className="mt-1 text-xs text-muted-foreground">{card.hint}</p>
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
