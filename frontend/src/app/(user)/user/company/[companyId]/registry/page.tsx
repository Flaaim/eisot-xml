import Link from "next/link";
import { AlertCircle } from "lucide-react";

import { Button } from "@/components/ui/button";
import { getRegistryRecordsAction } from "@/actions/registry";
import { checkSubscriptionAccessAction } from "@/actions/subscription";
import { RegistryTable } from "@/components/User/Company/RegistryTable";

interface CompanyRegistryPageProps {
  params: Promise<{ companyId: string }>;
}

export default async function CompanyRegistryPage({ params }: CompanyRegistryPageProps) {
  const { companyId } = await params;

  const registryResult = await getRegistryRecordsAction(companyId);

  if (!registryResult.ok) {
    return (
      <div className="flex min-h-[300px] flex-col items-center justify-center text-center">
        <div className="flex size-12 items-center justify-center rounded-full bg-destructive/10">
          <AlertCircle className="size-6 text-destructive" />
        </div>
        <h3 className="mt-4 text-lg font-semibold">Ошибка загрузки реестра</h3>
        <p className="mt-2 mb-4 max-w-sm text-sm text-muted-foreground">
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

  const accessResult = await checkSubscriptionAccessAction();
  const hasSubscriptionAccess = accessResult.data?.hasAccess ?? false;
  const trialAvailable = accessResult.data?.trialAvailable ?? false;

  return (
    <div className="space-y-4">
      <RegistryTable
        records={records}
        hasSubscriptionAccess={hasSubscriptionAccess}
        trialAvailable={trialAvailable}
        companyId={companyId}
      />
    </div>
  );
}
