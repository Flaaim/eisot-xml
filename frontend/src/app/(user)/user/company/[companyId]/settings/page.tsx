import { notFound } from "next/navigation";

import { fetchCompanyAction } from "@/actions/company";
import { CompanySettingsForm } from "@/components/User/Company/CompanySettingsForm";

interface CompanySettingsPageProps {
  params: Promise<{ companyId: string }>;
}

export default async function CompanySettingsPage({ params }: CompanySettingsPageProps) {
  const { companyId } = await params;
  const result = await fetchCompanyAction(companyId);

  if (!result.ok || !result.data) {
    notFound();
  }

  const company = result.data;

  return (
    <CompanySettingsForm
      companyId={companyId}
      initialTitle={company.name}
      initialInn={company.inn}
      isArchived={company.status === "ARCHIVED"}
    />
  );
}
