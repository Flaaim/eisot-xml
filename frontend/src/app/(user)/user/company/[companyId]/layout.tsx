import { notFound } from "next/navigation";
import Link from "next/link";
import { Building2, ArrowLeft } from "lucide-react";

import { fetchCompanyAction } from "@/actions/company";
import { CompanyTabs } from "@/components/company/CompanyTabs";

interface CompanyLayoutProps {
  children: React.ReactNode;
  params: Promise<{ companyId: string }>;
}

export default async function CompanyLayout({ children, params }: CompanyLayoutProps) {
  const { companyId } = await params;

  const result = await fetchCompanyAction(companyId);

  if (!result.ok || !result.data) {
    notFound();
  }

  const company = result.data;

  return (
    <div className="space-y-6 max-w-7xl mx-auto py-6">
      {/* Back link and Header card */}
      <div className="space-y-4">
        <div>
          <Link
            href="/user/company"
            className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors"
          >
            <ArrowLeft className="h-4 w-4" />
            Назад к списку компаний
          </Link>
        </div>

        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between p-6 rounded-2xl border bg-card text-card-foreground shadow-sm">
          <div className="flex items-start gap-4">
            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary shrink-0">
              <Building2 className="h-6 w-6" />
            </div>
            <div className="space-y-1">
              <h1 className="text-2xl font-bold tracking-tight">{company.name}</h1>
              <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                <span>ИНН: {company.inn}</span>
                <span className="flex items-center gap-1.5">
                  <span className="h-2 w-2 rounded-full bg-emerald-500" />
                  Активна
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Tabs navigation */}
      <CompanyTabs companyId={companyId} />

      {/* Main Content Card Wrapper */}
      <div className="rounded-2xl border bg-card p-6 shadow-sm">
        {children}
      </div>
    </div>
  );
}
