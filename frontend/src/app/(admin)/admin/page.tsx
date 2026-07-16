import { StatsCards } from "@/components/Admin/StatsCards";
import { fetchAdminStatsAction } from "@/actions/admin";

export default async function AdminDashboardPage() {
  const statsResult = await fetchAdminStatsAction();

  const stats = statsResult.data ?? {
    totalUsers: 0,
    registrationsLast30Days: 0,
    activeSubscriptions: 0,
    activeBasicPlan: 0,
    activeExtendedPlan: 0,
    activeSubscriptionsLast30Days: 0,
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold tracking-tight">Дашборд</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Обзор регистраций User и Active Status подписок.
        </p>
      </div>
      {!statsResult.ok && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {statsResult.error}
        </div>
      )}
      <StatsCards stats={stats} />
    </div>
  );
}
