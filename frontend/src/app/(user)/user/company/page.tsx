import { AlertCircle, PlusCircle } from "lucide-react";
import { fetchCompaniesAction } from "@/actions/company";
import { ActiveCompaniesList } from "@/components/User/Company/ActiveCompaniesList";
import { ArchiveCompaniesList } from "@/components/User/Company/ArchiveCompanyLIst";
import Link from "next/link";
import { Button } from "@/components/ui/button";

export default async function CompanyPage() {
  const result = await fetchCompaniesAction();

  const archivedCompanies = result.data?.filter((company) => company.status === "ARCHIVED");

  const activeCompanies = result.data?.filter((company) => company.status === "ACTIVE");

  const hasArchiveCompanies = archivedCompanies && archivedCompanies.length > 0;

  if (!result.ok || !result.data) {
    return (
      <div className="mx-auto max-w-4xl p-4 md:p-8">
        <div className="flex min-h-[40vh] flex-col items-center justify-center space-y-4 text-center">
          <div className="flex size-16 items-center justify-center rounded-full bg-destructive/10 text-destructive">
            <AlertCircle className="size-8" />
          </div>
          <h2 className="text-xl font-semibold">Не удалось загрузить компании</h2>
          <p className="text-sm text-muted-foreground">
            {result.error ?? "Произошла непредвиденная ошибка"}
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-4xl p-4 md:p-8">
      <div className="mb-8 flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Активные компании</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Выберите компанию для перехода в её рабочее пространство
          </p>
        </div>
        {result.data.length > 0 && (
          <Button>
            <Link href="/user/company/add" className="flex items-center gap-2">
              <PlusCircle className="size-4" />
              Добавить
            </Link>
          </Button>
        )}
      </div>
      <ActiveCompaniesList companies={activeCompanies} />

      {hasArchiveCompanies ? (
        <div className="mt-8">
          <div className="mb-8 flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Архив компаний</h1>
              <p className="mt-1 text-sm text-muted-foreground">
                Архив содержит компании, с которыми вы когда-то работали
              </p>
            </div>
          </div>
          <ArchiveCompaniesList companies={archivedCompanies} />
        </div>
      ) : (
        ""
      )}
    </div>
  );
}
