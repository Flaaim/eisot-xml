import { PaymentsTable } from "@/components/Admin/PaymentsTable";
import { fetchAdminPaymentsAction } from "@/actions/admin";

interface AdminTransactionsPageProps {
  readonly searchParams: Promise<{
    page?: string;
  }>;
}

export default async function AdminTransactionsPage({ searchParams }: AdminTransactionsPageProps) {
  const params = await searchParams;
  const page = Math.max(1, Number(params.page ?? "1") || 1);

  const paymentsResult = await fetchAdminPaymentsAction({
    page,
    limit: 20,
  });

  const payments = paymentsResult.data?.items ?? [];
  const total = paymentsResult.data?.total ?? 0;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold tracking-tight">Транзакции</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Платежи User Subscription через ЮKassa.
        </p>
      </div>

      {!paymentsResult.ok && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {paymentsResult.error}
        </div>
      )}

      <div className="text-sm text-muted-foreground">Всего: {total}</div>
      <PaymentsTable payments={payments} />
    </div>
  );
}
