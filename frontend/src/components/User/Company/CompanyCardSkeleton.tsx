import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";

/**
 * Скелетон-плейсхолдер для карточки компании.
 */
export function CompanyCardSkeleton() {
  return (
    <Card data-testid="company-card-skeleton">
      <CardHeader className="pb-2">
        <div className="flex items-center gap-3">
          <Skeleton className="size-10 rounded-lg" />
          <Skeleton className="h-4 w-3/4" />
        </div>
      </CardHeader>
      <CardContent className="pt-0">
        <Skeleton className="h-6 w-28 rounded-md" />
      </CardContent>
    </Card>
  );
}
