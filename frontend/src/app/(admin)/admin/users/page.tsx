import { UsersTable } from "@/components/Admin/UsersTable";
import { UsersFilters } from "@/components/Admin/UsersFilters";
import { fetchAdminUsersAction } from "@/actions/admin";
import type { AdminSubscriptionFilter } from "@/interfaces/admin.interface";

interface AdminUsersPageProps {
  readonly searchParams: Promise<{
    email?: string;
    subscriptionStatus?: string;
    page?: string;
  }>;
}

function parseSubscriptionStatus(value: string | undefined): AdminSubscriptionFilter | undefined {
  if (value === "active" || value === "none" || value === "expired") {
    return value;
  }
  return undefined;
}

export default async function AdminUsersPage({ searchParams }: AdminUsersPageProps) {
  const params = await searchParams;
  const email = params.email?.trim() ?? "";
  const subscriptionStatus = parseSubscriptionStatus(params.subscriptionStatus);
  const page = Math.max(1, Number(params.page ?? "1") || 1);

  const usersResult = await fetchAdminUsersAction({
    page,
    limit: 20,
    email: email || undefined,
    subscriptionStatus,
  });

  const users = usersResult.data?.items ?? [];
  const total = usersResult.data?.total ?? 0;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold tracking-tight">Пользователи</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Список User, их компаний и статусов User Subscription.
        </p>
      </div>

      <UsersFilters email={email} subscriptionStatus={subscriptionStatus ?? ""} />

      {!usersResult.ok && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {usersResult.error}
        </div>
      )}

      <div className="text-sm text-muted-foreground">Найдено: {total}</div>
      <UsersTable users={users} />
    </div>
  );
}
