"use client";

import { useRouter } from "next/navigation";
import { useTransition } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { AdminSubscriptionFilter } from "@/interfaces/admin.interface";

interface UsersFiltersProps {
  readonly email: string;
  readonly subscriptionStatus: AdminSubscriptionFilter | "";
}

export function UsersFilters({ email, subscriptionStatus }: UsersFiltersProps) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();

  return (
    <form
      className="flex flex-col gap-3 sm:flex-row sm:items-end"
      onSubmit={(event) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        const emailValue = formData.get("email");
        const statusValue = formData.get("subscriptionStatus");
        const nextEmail = typeof emailValue === "string" ? emailValue.trim() : "";
        const nextStatus = typeof statusValue === "string" ? statusValue : "";

        const params = new URLSearchParams();
        if (nextEmail) {
          params.set("email", nextEmail);
        }
        if (nextStatus) {
          params.set("subscriptionStatus", nextStatus);
        }

        const query = params.toString();
        startTransition(() => {
          router.push(query ? `/admin/users?${query}` : "/admin/users");
        });
      }}
    >
      <div className="flex-1 space-y-1">
        <label htmlFor="admin-email-filter" className="text-xs font-medium text-muted-foreground">
          Email
        </label>
        <Input
          id="admin-email-filter"
          name="email"
          defaultValue={email}
          placeholder="поиск по email"
        />
      </div>
      <div className="w-full space-y-1 sm:w-56">
        <label
          htmlFor="admin-subscription-filter"
          className="text-xs font-medium text-muted-foreground"
        >
          Subscription Status
        </label>
        <select
          id="admin-subscription-filter"
          name="subscriptionStatus"
          defaultValue={subscriptionStatus}
          className="flex h-8 w-full rounded-lg border border-input bg-transparent px-2.5 text-sm outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
        >
          <option value="">Все</option>
          <option value="active">Active</option>
          <option value="expired">Expired</option>
          <option value="none">Без подписки</option>
        </select>
      </div>
      <Button type="submit" className="cursor-pointer" disabled={pending}>
        {pending ? "Фильтрация..." : "Применить"}
      </Button>
    </form>
  );
}
