"use client";

import Link from "next/link";
import { Archive, Building2, Users, GraduationCap, Settings } from "lucide-react";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import type { CompanyShort } from "@/interfaces/company.interface";
import { archiveCompanyAction } from "@/actions/company";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import { useState } from "react";

interface CompanyCardProps {
  readonly company: CompanyShort;
}

/**
 * Карточка компании для дашборда.
 * Отображает название, ИНН и статистику (работники, протоколы).
 * Стилизована с hover-эффектами.
 */
export function ActiveCompanyCard({ company }: CompanyCardProps) {
  const [loading, setLoading] = useState<boolean>(false);
  const router = useRouter();
  const archive = async (id: string) => {
    try {
      setLoading(true);
      const response = await archiveCompanyAction(id);
      if (response.ok) {
        toast.success("Компания успешно перенесена в архив!");
        router.refresh();
      }
    } catch (error) {
      console.error("Archive company error:", error);
      toast.error("Ошибка при удалении компании.");
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="pointer-events-none opacity-50">
        <Card className="group flex h-full flex-col justify-between transition-all duration-200">
          <div>
            <CardHeader className="pb-2">
              <div className="flex items-center gap-3">
                <div className="size-10 animate-pulse rounded-lg bg-muted" />
                <div className="h-5 w-3/4 animate-pulse rounded-md bg-muted" />
              </div>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="h-6 w-24 animate-pulse rounded-md bg-muted" />
            </CardContent>
          </div>
          <CardFooter className="mt-4 flex items-center justify-between border-t bg-muted/20 px-6 py-3">
            <div className="flex gap-4">
              <div className="h-4 w-10 animate-pulse rounded bg-muted" />
              <div className="h-4 w-10 animate-pulse rounded bg-muted" />
            </div>
            <div className="h-5 w-16 animate-pulse rounded bg-muted" />
          </CardFooter>
        </Card>
      </div>
    );
  }

  return (
    <Card data-testid={`company-card-${company.id}`} className="h-full">
      <Link
        href={`/user/company/${company.id}`}
        className="block flex-1 rounded-t-xl focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
      >
        <CardHeader className="pb-2">
          <div className="flex items-center gap-3">
            <div className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary/15">
              <Building2 className="size-5" />
            </div>
            <CardTitle className="line-clamp-2 text-base leading-snug font-semibold">
              {company.name}
            </CardTitle>
          </div>
        </CardHeader>
        <CardContent className="pt-0">
          <span className="inline-flex items-center rounded-md bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground ring-1 ring-border ring-inset">
            ИНН {company.inn}
          </span>
        </CardContent>
      </Link>
      <CardFooter className="mt-4 flex items-center justify-between border-t bg-muted/20 px-6 py-3">
        <div className="flex items-center gap-4 text-xs text-muted-foreground">
          <div className="flex items-center gap-1.5" title="Количество работников">
            <Users className="size-4 text-muted-foreground/75" />
            <span className="font-semibold text-foreground/80">{company.workersCount}</span>
            <span className="text-muted-foreground/60">раб.</span>
          </div>
          <div className="flex items-center gap-1.5" title="Количество протоколов">
            <GraduationCap className="size-4 text-muted-foreground/75" />
            <span className="font-semibold text-foreground/80">{company.protocolsCount}</span>
            <span className="text-muted-foreground/60">прот.</span>
          </div>
        </div>

        <div className="flex items-center gap-1">
          <Link
            href={`/user/company/${company.id}/settings`}
            className="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-muted-foreground transition-colors hover:bg-muted/80 hover:text-foreground"
          >
            <Settings className="size-4" />
            <span>Настройки</span>
          </Link>
          <button
            type="button"
            onClick={() => {
              void archive(company.id);
            }}
            className="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-muted-foreground transition-colors hover:bg-muted/80 hover:text-destructive"
          >
            <Archive className="size-4" />
            <span>В архив</span>
          </button>
        </div>
      </CardFooter>
    </Card>
  );
}
