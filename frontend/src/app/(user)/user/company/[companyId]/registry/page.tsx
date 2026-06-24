import Link from "next/link";
import { Building2, ArrowLeft, AlertCircle, FileText } from "lucide-react";

import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { fetchCompanyAction } from "@/actions/company";
import { getRegistryRecordsAction } from "@/actions/registry";
import { RegistryTable } from "@/components/User/Company/RegistryTable";

interface CompanyRegistryPageProps {
  params: Promise<{ companyId: string }>;
}

export default async function CompanyRegistryPage({ params }: CompanyRegistryPageProps) {
  const { companyId } = await params;

  // Load both company details and registry records
  const [companyResult, registryResult] = await Promise.all([
    fetchCompanyAction(companyId),
    getRegistryRecordsAction(companyId),
  ]);

  if (!companyResult.ok) {
    return (
      <div className="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center">
        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10">
          <AlertCircle className="h-6 w-6 text-destructive" />
        </div>
        <h3 className="mt-4 text-lg font-semibold">Ошибка загрузки данных</h3>
        <p className="mb-4 mt-2 text-sm text-muted-foreground max-w-sm">
          Не удалось получить информацию о компании. {companyResult.error}
        </p>
        <Link href="/user/company">
          <Button variant="outline" size="sm" className="cursor-pointer">
            Вернуться к списку
          </Button>
        </Link>
      </div>
    );
  }

  const company = companyResult.data;
  if (!company) {
    return (
      <div className="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center">
        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
          <AlertCircle className="h-6 w-6 text-muted-foreground" />
        </div>
        <h3 className="mt-4 text-lg font-semibold">Компания не найдена</h3>
        <p className="mb-4 mt-2 text-sm text-muted-foreground max-w-sm">
          Запрашиваемая компания не существует или была удалена.
        </p>
        <Link href="/user/company">
          <Button size="sm" className="cursor-pointer">
            К списку компаний
          </Button>
        </Link>
      </div>
    );
  }

  if (!registryResult.ok) {
    return (
      <div className="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center">
        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10">
          <AlertCircle className="h-6 w-6 text-destructive" />
        </div>
        <h3 className="mt-4 text-lg font-semibold">Ошибка загрузки реестра</h3>
        <p className="mb-4 mt-2 text-sm text-muted-foreground max-w-sm">
          Не удалось получить список протоколов обучения. {registryResult.error}
        </p>
        <Link href={`/user/company/${companyId}`}>
          <Button variant="outline" size="sm" className="cursor-pointer">
            Вернуться в обзор
          </Button>
        </Link>
      </div>
    );
  }

  const records = registryResult.data ?? [];

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div className="flex items-center gap-2 mb-2 text-sm text-muted-foreground">
            <Link href={`/user/company/${companyId}`} className="hover:text-foreground flex items-center gap-1">
              <ArrowLeft className="h-4 w-4" />
              Назад в обзор
            </Link>
          </div>
          <h1 className="text-2xl font-bold tracking-tight">Реестр ЕИСОТ</h1>
          <p className="text-muted-foreground text-sm mt-1">
            Выгрузка протоколов проверки знаний в Минтруд РФ
          </p>
        </div>
      </div>

      <Card>
        <CardContent className="flex items-center justify-between p-6">
          <div className="space-y-1">
            <div className="flex items-center gap-2">
              <Building2 className="h-5 w-5 text-muted-foreground" />
              <span className="text-sm font-medium text-muted-foreground">Активная компания</span>
            </div>
            <p className="text-xl font-bold">{company.name}</p>
            <p className="text-sm text-muted-foreground">ИНН: {company.inn}</p>
          </div>
        </CardContent>
      </Card>

      <RegistryTable records={records} />
    </div>
  );
}
