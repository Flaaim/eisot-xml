import {Archive, Building2, PlusCircle} from "lucide-react";
import { Button } from "@/components/ui/button";
import type { CompanyShort } from "@/interfaces/company.interface";
import {ArchiveCompanyCard} from "@/components/User/Company/ArchiveCompanyCard";

interface CompaniesListProps {
  readonly companies: CompanyShort[];
}

/**
 * Список архивных компаний пользователя.
 */
export function ArchiveCompaniesList({ companies }: CompaniesListProps) {
  if (companies.length === 0) {
    return (
      <div
        data-testid="companies-empty"
        className="flex flex-col items-center justify-center min-h-[40vh] text-center space-y-4 max-w-md mx-auto"
      >
        <div className="h-20 w-20 bg-primary/10 text-primary rounded-full flex items-center justify-center">
          <Archive className="h-10 w-10" />
        </div>
        <h2 className="text-2xl font-bold tracking-tight">Архив пуст...</h2>
        <p className="text-muted-foreground">
          Но это пока ненадолго ;)
        </p>
      </div>
    );
  }

  return (
    <div data-testid="companies-grid" className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {companies.map((company) => (
        <ArchiveCompanyCard key={company.id} company={company} />
      ))}
    </div>
  );
}
