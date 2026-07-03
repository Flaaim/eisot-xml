export type SubscriptionPlan = "basic" | "premium";

export type SubscriptionStatus = "active" | "expired" | "cancelled";

export interface SubscriptionAccess {
  hasAccess: boolean;
  plan: SubscriptionPlan | null;
  status: SubscriptionStatus | null;
  periodStart: string | null;
  periodEnd: string | null;
}

export interface ActivateSubscriptionPayload {
  planId: SubscriptionPlan;
  durationDays: number;
}
