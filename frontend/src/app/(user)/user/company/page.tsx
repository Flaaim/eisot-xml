import { PlusCircle, AlertCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import Link from "next/link";
import { fetchCompaniesAction } from "@/actions/company";
import { CompaniesList } from "@/components/User/Company/CompaniesList";

export default async function CompanyPage() {
  const result = await fetchCompaniesAction();
  console.log(result);
  if (!result.ok || !result.data) {
    return (
      <div className="mx-auto max-w-4xl p-4 md:p-8">
        <div className="flex flex-col items-center justify-center min-h-[40vh] text-center space-y-4">
          <div className="h-16 w-16 bg-destructive/10 text-destructive rounded-full flex items-center justify-center">
            <AlertCircle className="h-8 w-8" />
          </div>
          <h2 className="text-xl font-semibold">Не удалось загрузить компании</h2>
          <p className="text-muted-foreground text-sm">
            {result.error ?? "Произошла непредвиденная ошибка"}
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-4xl p-4 md:p-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Мои компании</h1>
          <p className="text-muted-foreground text-sm mt-1">
            Выберите компанию для перехода в её рабочее пространство
          </p>
        </div>
        {result.data.length > 0 && (
          <Button >
            <Link href="/user/company/add" className="flex items-center gap-2">
              <PlusCircle className="h-4 w-4" />
              Добавить
            </Link>
          </Button>
        )}
      </div>
      <CompaniesList companies={result.data} />
    </div>
  );
}
