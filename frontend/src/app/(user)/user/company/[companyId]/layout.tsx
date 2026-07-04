import { notFound } from "next/navigation";
import Link from "next/link";
import { Building2, ArrowLeft } from "lucide-react";

import { fetchCompanyAction } from "@/actions/company";
import { CompanyTabs } from "@/components/company/CompanyTabs";
import { CompanyStatusToggle } from "@/components/company/CompanyStatusToggle";

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
    <div className="mx-auto max-w-7xl space-y-6 py-6">
      <div className="space-y-4">
        <div>
          <Link
            href="/user/company"
            className="inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
          >
            <ArrowLeft className="size-4" />
            Назад к списку компаний
          </Link>
        </div>

        <div className="flex flex-col gap-4 rounded-2xl border bg-card p-6 text-card-foreground shadow-sm md:flex-row md:items-center md:justify-between">
          <div className="flex items-start gap-4">
            <div className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <Building2 className="size-6" />
            </div>
            <div className="space-y-1">
              <h1 className="text-2xl font-bold tracking-tight">{company.name}</h1>
              <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                <span>ИНН: {company.inn}</span>
                <span className="flex items-center gap-1.5">
                  <span
                    className={`size-2 rounded-full ${company.status === "ACTIVE" ? "bg-emerald-500" : "bg-amber-500"}`}
                  />
                  {company.status === "ACTIVE" ? "Активна" : "В архиве"}
                </span>
              </div>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <CompanyStatusToggle companyId={companyId} status={company.status} />
          </div>
        </div>
      </div>

      <CompanyTabs companyId={companyId} />

      <div className="rounded-2xl border bg-card p-6 shadow-sm">{children}</div>
    </div>
  );
}
