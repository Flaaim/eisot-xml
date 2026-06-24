"use client"

import {Building2, Users, GraduationCap, RotateCcw} from "lucide-react";
import {Card, CardContent, CardFooter, CardHeader, CardTitle} from "@/components/ui/card";
import type { CompanyShort } from "@/interfaces/company.interface";
import {unarchiveCompanyAction} from "@/actions/company";
import {useRouter} from "next/navigation";
import {toast} from "sonner";
import {useState} from "react";

interface CompanyCardProps {
  readonly company: CompanyShort;
}

/**
 * Карточка архивной компании для дашборда.
 * Отображает название, ИНН и статистику (работники, протоколы).
 * Стилизована с faded эффектом и кнопкой восстановления.
 */
export function ArchiveCompanyCard({ company }: CompanyCardProps) {
  const [loading, setLoading] = useState<boolean>(false)
  const router = useRouter();
  const activate = async (id: string) => {
    try {
      setLoading(true)
      const response = await unarchiveCompanyAction(id);
      if (response.ok) {
        toast.success('Компания успешно восстановлена!')
        router.refresh();
      }
    } catch (error) {
      toast.success('Ошибка при восстановлении компании.')
    } finally {
      setLoading(false)
    }
  }

  if(loading) {
    return (
      <div className="pointer-events-none opacity-50">
        <Card className="group transition-all duration-200 flex flex-col justify-between h-full opacity-70">
          <div>
            <CardHeader className="pb-2">
              <div className="flex items-center gap-3">
                <div className="h-10 w-10 animate-pulse rounded-lg bg-muted" />
                <div className="h-5 w-3/4 animate-pulse rounded-md bg-muted" />
              </div>
            </CardHeader>
            <CardContent className="pt-0">
              <div className="h-6 w-24 animate-pulse rounded-md bg-muted" />
            </CardContent>
          </div>
          <CardFooter className="flex items-center justify-between border-t bg-muted/20 px-6 py-3 mt-4">
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
    <Card
      data-testid={`company-card-${company.id}`}
      className="group cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:ring-2 hover:ring-primary/20 flex flex-col justify-between h-full opacity-75 grayscale-[20%]"
    >
      <div>
        <CardHeader className="pb-2">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary/15">
              <Building2 className="h-5 w-5" />
            </div>
            <CardTitle className="text-base leading-snug line-clamp-2 font-semibold">
              {company.name}
            </CardTitle>
          </div>
        </CardHeader>
        <CardContent className="pt-0 flex items-center justify-between">
          <span className="inline-flex items-center rounded-md bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground ring-1 ring-inset ring-border">
            ИНН {company.inn}
          </span>
          <span className="inline-flex items-center rounded-md bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-500 ring-1 ring-inset ring-amber-500/20">
            В архиве
          </span>
        </CardContent>
      </div>
      <CardFooter className="flex items-center justify-between border-t bg-muted/20 px-6 py-3 mt-4">
        <div className="flex items-center gap-4 text-xs text-muted-foreground">
          <div className="flex items-center gap-1.5" title="Количество работников">
            <Users className="h-4 w-4 text-muted-foreground/75" />
            <span className="font-semibold text-foreground/80">{company.workersCount ?? 0}</span>
            <span className="text-muted-foreground/60">раб.</span>
          </div>
          <div className="flex items-center gap-1.5" title="Количество протоколов">
            <GraduationCap className="h-4 w-4 text-muted-foreground/75" />
            <span className="font-semibold text-foreground/80">{company.protocolsCount ?? 0}</span>
            <span className="text-muted-foreground/60">прот.</span>
          </div>
        </div>

        <button
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            activate(company.id)
          }}
          className="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-muted-foreground hover:bg-muted/80 hover:text-primary transition-colors"
        >
          <RotateCcw className="h-4 w-4" />
          <span>Восстановить</span>
        </button>
      </CardFooter>
    </Card>
  );
}

