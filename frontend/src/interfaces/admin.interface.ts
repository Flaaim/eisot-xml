export type AdminSubscriptionFilter = "active" | "none" | "expired";

export interface AdminUserSummary {
  id: string;
  email: string;
  status: string;
  role: string;
  createdAt: string;
  activeSubscriptionPlan: "basic" | "extended" | "trial" | null;
  subscriptionStatus: "active" | "expired" | "cancelled" | null;
  companiesCount: number;
}

export interface AdminUsersListResponse {
  items: AdminUserSummary[];
  total: number;
  page: number;
  limit: number;
}

export interface AdminSubscriptionStats {
  totalUsers: number;
  registrationsLast30Days: number;
  activeSubscriptions: number;
  activeBasicPlan: number;
  activeExtendedPlan: number;
  activeSubscriptionsLast30Days: number;
}

export interface AdminPaymentSummary {
  id: string;
  userId: string;
  userEmail: string;
  plan: "basic" | "extended";
  status: "pending" | "succeeded" | "failed";
  amountValue: string;
  amountCurrency: string;
  createdAt: string;
  confirmedAt: string | null;
}

export interface AdminPaymentsListResponse {
  items: AdminPaymentSummary[];
  total: number;
  page: number;
  limit: number;
}
