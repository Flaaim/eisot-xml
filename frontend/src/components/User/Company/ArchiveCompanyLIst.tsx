import { Archive } from "lucide-react";
import type { CompanyShort } from "@/interfaces/company.interface";
import { ArchiveCompanyCard } from "@/components/User/Company/ArchiveCompanyCard";

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
        className="mx-auto flex min-h-[40vh] max-w-md flex-col items-center justify-center space-y-4 text-center"
      >
        <div className="flex size-20 items-center justify-center rounded-full bg-primary/10 text-primary">
          <Archive className="size-10" />
        </div>
        <h2 className="text-2xl font-bold tracking-tight">Архив пуст...</h2>
        <p className="text-muted-foreground">Но это пока ненадолго ;)</p>
      </div>
    );
  }

  return (
    <div data-testid="companies-grid" className="grid gap-4 lg:grid-cols-2">
      {companies.map((company) => (
        <ArchiveCompanyCard key={company.id} company={company} />
      ))}
    </div>
  );
}
