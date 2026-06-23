import { CompanyCardSkeleton } from "@/components/User/Company/CompanyCardSkeleton";

export default function CompanyLoading() {
  return (
    <div className="mx-auto max-w-4xl p-4 md:p-8">
      <div className="mb-8 space-y-2">
        <div className="h-8 w-48 animate-pulse rounded-md bg-muted" />
        <div className="h-4 w-80 animate-pulse rounded-md bg-muted" />
      </div>
      <div
        data-testid="companies-loading"
        className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
      >
        {Array.from({ length: 6 }).map((_, i) => (
          <CompanyCardSkeleton key={i} />
        ))}
      </div>
    </div>
  );
}
