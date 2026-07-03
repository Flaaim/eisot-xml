import { redirect } from "next/navigation";

export default function CompanySubscriptionRedirect({
  params,
}: {
  params: Promise<{ companyId: string }>;
}) {
  void params;
  redirect("/user/subscription");
}
