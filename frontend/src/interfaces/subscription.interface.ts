export type SubscriptionPlan = "basic" | "extended" | "trial";

export type PaidSubscriptionPlan = "basic" | "extended";

export type SubscriptionStatus = "active" | "expired" | "cancelled";

export interface SubscriptionAccess {
  hasAccess: boolean;
  plan: SubscriptionPlan | null;
  status: SubscriptionStatus | null;
  periodStart: string | null;
  periodEnd: string | null;
  trialUsed: boolean;
  trialAvailable: boolean;
}

export interface ActivateSubscriptionPayload {
  planId: PaidSubscriptionPlan;
  durationDays: number;
}

export type PaymentStatus = "pending" | "succeeded" | "failed";

export interface CreatePaymentPayload {
  planId: PaidSubscriptionPlan;
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
  planId: PaidSubscriptionPlan;
}

export function daysRemainingUntil(periodEnd: string): number {
  const end = new Date(periodEnd);
  const today = new Date();
  end.setHours(0, 0, 0, 0);
  today.setHours(0, 0, 0, 0);

  const diffMs = end.getTime() - today.getTime();
  return Math.max(0, Math.round(diffMs / 86_400_000));
}

export function formatDaysLabel(days: number): string {
  const mod10 = days % 10;
  const mod100 = days % 100;

  if (mod10 === 1 && mod100 !== 11) {
    return `${days} день`;
  }
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) {
    return `${days} дня`;
  }
  return `${days} дней`;
}
