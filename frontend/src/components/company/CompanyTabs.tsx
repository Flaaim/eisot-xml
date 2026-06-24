"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/utils";

interface CompanyTabsProps {
  readonly companyId: string;
}

export function CompanyTabs({ companyId }: CompanyTabsProps) {
  const pathname = usePathname();

  const tabs = [
    {
      title: "СОТРУДНИКИ",
      subtitle: "Формируем XML по сотрудникам",
      href: `/user/company/${companyId}`,
      isActive: pathname === `/user/company/${companyId}`,
    },
    {
      title: "РЕЕСТР ПРОТОКОЛОВ",
      subtitle: "Просмотр и выгрузка XML",
      href: `/user/company/${companyId}/registry`,
      isActive: pathname.startsWith(`/user/company/${companyId}/registry`),
    },
    {
      title: "EXCEL",
      subtitle: "Формируем XML из таблицы EXCEL",
      href: `/user/company/${companyId}/excel`,
      isActive: pathname === `/user/company/${companyId}/excel`,
    },
  ];

  return (
    <div className="flex w-full rounded-xl bg-muted/60 p-1.5 gap-1.5 shadow-inner">
      {tabs.map((tab) => (
        <Link
          key={tab.title}
          href={tab.href}
          className={cn(
            "flex-1 flex flex-col items-center justify-center py-3 px-4 rounded-lg transition-all duration-200 text-center cursor-pointer select-none",
            tab.isActive
              ? "bg-background text-primary shadow-sm"
              : "text-muted-foreground/80 hover:bg-background/40 hover:text-foreground/90"
          )}
        >
          <span className={cn(
            "text-sm tracking-wide font-semibold",
            tab.isActive ? "text-foreground font-bold" : ""
          )}>
            {tab.title}
          </span>
          <span className="text-xs text-muted-foreground mt-1 font-normal hidden md:inline">
            {tab.subtitle}
          </span>
        </Link>
      ))}
    </div>
  );
}
