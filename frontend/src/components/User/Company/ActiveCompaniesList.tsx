import Link from "next/link";
import { Building2, PlusCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import { ActiveCompanyCard } from "@/components/User/Company/ActiveCompanyCard";
import type { CompanyShort } from "@/interfaces/company.interface";

interface CompaniesListProps {
  readonly companies: CompanyShort[];
}

/**
 * Список компаний пользователя.
 */
export function ActiveCompaniesList({ companies }: CompaniesListProps) {
  if (companies.length === 0) {
    return (
      <div
        data-testid="companies-empty"
        className="flex flex-col items-center justify-center min-h-[40vh] text-center space-y-4 max-w-md mx-auto"
      >
        <div className="h-20 w-20 bg-primary/10 text-primary rounded-full flex items-center justify-center">
          <Building2 className="h-10 w-10" />
        </div>
        <h2 className="text-2xl font-bold tracking-tight">Компании не найдены</h2>
        <p className="text-muted-foreground">
          Добавьте вашу первую компанию, чтобы начать работу
        </p>
        <Button  className="mt-2">
          <Link href="/user/company/add" className="flex items-center gap-2">
            <PlusCircle className="h-4 w-4" />
            Добавить компанию
          </Link>
        </Button>
      </div>
    );
  }

  return (
    <div data-testid="companies-grid" className="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
      {companies.map((company) => (
        <ActiveCompanyCard key={company.id} company={company} />
      ))}
    </div>
  );
}
