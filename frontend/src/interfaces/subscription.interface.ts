export type SubscriptionPlan = "basic" | "extended";

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

export type PaymentStatus = "pending" | "succeeded" | "failed";

export interface CreatePaymentPayload {
  planId: SubscriptionPlan;
  durationDays: number;
  returnUrl: string;
}

export interface CreatePaymentResponse {
  paymentId: string;
  confirmationUrl: string;
}

export interface PaymentStatusResponse {
  paymentId: string;
  status: PaymentStatus;
  planId: SubscriptionPlan;
}
