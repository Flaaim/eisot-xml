import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import type { AdminPaymentSummary } from "@/interfaces/admin.interface";

interface PaymentsTableProps {
  readonly payments: AdminPaymentSummary[];
}

function statusVariant(status: AdminPaymentSummary["status"]) {
  if (status === "succeeded") {
    return "success" as const;
  }
  if (status === "failed") {
    return "destructive" as const;
  }
  return "warning" as const;
}

export function PaymentsTable({ payments }: PaymentsTableProps) {
  if (payments.length === 0) {
    return (
      <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
        Транзакции пока отсутствуют.
      </div>
    );
  }

  return (
    <div className="rounded-lg border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>User</TableHead>
            <TableHead>Plan</TableHead>
            <TableHead>Status</TableHead>
            <TableHead className="text-right">Amount</TableHead>
            <TableHead>Создан</TableHead>
            <TableHead>Подтверждён</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {payments.map((payment) => (
            <TableRow key={payment.id}>
              <TableCell className="font-medium">{payment.userEmail || payment.userId}</TableCell>
              <TableCell>
                <Badge variant="outline">
                  {payment.plan === "extended" ? "Extended" : "Basic"}
                </Badge>
              </TableCell>
              <TableCell>
                <Badge variant={statusVariant(payment.status)}>{payment.status}</Badge>
              </TableCell>
              <TableCell className="text-right">
                {payment.amountValue} {payment.amountCurrency}
              </TableCell>
              <TableCell className="text-muted-foreground">
                {new Date(payment.createdAt).toLocaleString("ru-RU")}
              </TableCell>
              <TableCell className="text-muted-foreground">
                {payment.confirmedAt ? new Date(payment.confirmedAt).toLocaleString("ru-RU") : "—"}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  );
}
