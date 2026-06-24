import { Suspense } from "react";
import { fetchCompanyAction } from "@/actions/company";
import { WorkerRegistrationForm } from "@/components/User/Company/WorkerRegistrationForm";
import { CompanyStats, CompanyStatsSkeleton } from "@/components/company/CompanyStats";

interface CompanyOverviewPageProps {
  params: Promise<{ companyId: string }>;
}

export default async function CompanyOverviewPage({ params }: CompanyOverviewPageProps) {
  const { companyId } = await params;

  const result = await fetchCompanyAction(companyId);

  if (!result.ok || !result.data) {
    return null; // Layout notFound() will execute
  }

  const company = result.data;

  return (
    <div className="space-y-6">
      {/* Stats Grid */}
      <Suspense fallback={<CompanyStatsSkeleton />}>
        <CompanyStats companyId={companyId} />
      </Suspense>

      {/* Form Area */}
      <div className="pt-4 border-t space-y-4">
        <div>
          <h2 className="text-lg font-semibold tracking-tight">Регистрация работника</h2>
          <p className="text-sm text-muted-foreground">
            Заполните данные сотрудника и его протоколы обучения.
          </p>
        </div>
        <WorkerRegistrationForm companyId={companyId} company={company} />
      </div>
    </div>
  );
}

