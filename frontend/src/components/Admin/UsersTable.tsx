import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import type { AdminUserSummary } from "@/interfaces/admin.interface";

interface UsersTableProps {
  readonly users: AdminUserSummary[];
}

function planLabel(plan: AdminUserSummary["activeSubscriptionPlan"]): string {
  if (plan === "basic") {
    return "Basic";
  }
  if (plan === "extended") {
    return "Extended";
  }
  return "—";
}

function subscriptionBadge(user: AdminUserSummary) {
  if (user.subscriptionStatus === "active" && user.activeSubscriptionPlan) {
    return <Badge variant="success">Active · {planLabel(user.activeSubscriptionPlan)}</Badge>;
  }

  if (user.subscriptionStatus === "expired") {
    return <Badge variant="warning">Expired</Badge>;
  }

  if (user.subscriptionStatus === "cancelled") {
    return <Badge variant="outline">Cancelled</Badge>;
  }

  return <Badge variant="secondary">Нет подписки</Badge>;
}

export function UsersTable({ users }: UsersTableProps) {
  if (users.length === 0) {
    return (
      <div className="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground">
        Пользователи не найдены.
      </div>
    );
  }

  return (
    <div className="rounded-lg border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Email</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Role</TableHead>
            <TableHead>Subscription</TableHead>
            <TableHead>Plan</TableHead>
            <TableHead className="text-right">Companies</TableHead>
            <TableHead>Создан</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {users.map((user) => (
            <TableRow key={user.id}>
              <TableCell className="font-medium">{user.email}</TableCell>
              <TableCell>
                <Badge variant={user.status === "active" ? "success" : "warning"}>
                  {user.status}
                </Badge>
              </TableCell>
              <TableCell>
                <Badge variant={user.role === "admin" ? "default" : "outline"}>{user.role}</Badge>
              </TableCell>
              <TableCell>{subscriptionBadge(user)}</TableCell>
              <TableCell>{planLabel(user.activeSubscriptionPlan)}</TableCell>
              <TableCell className="text-right">{user.companiesCount}</TableCell>
              <TableCell className="text-muted-foreground">
                {new Date(user.createdAt).toLocaleDateString("ru-RU")}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  );
}
