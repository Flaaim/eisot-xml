"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { cn } from "@/lib/utils";

/** Способ №2: «Добавление автоматически» — включить, когда будет готов импорт Excel. */
const EXCEL_IMPORT_ENABLED = process.env.NEXT_PUBLIC_EXCEL_IMPORT_ENABLED === "true";

interface CompanyTabsProps {
  readonly companyId: string;
}

interface CompanyTab {
  readonly title: string;
  readonly subtitle: string;
  readonly href: string;
  readonly isActive: boolean;
  readonly disabled?: boolean;
  readonly disabledTooltip?: string;
  readonly comingSoonLabel?: string;
}

interface CompanyTabItemProps {
  readonly tab: CompanyTab;
}

function CompanyTabItem({ tab }: CompanyTabItemProps) {
  const content = (
    <>
      <span
        className={cn(
          "flex items-center justify-center gap-2 text-sm font-semibold tracking-wide",
          tab.isActive && !tab.disabled ? "font-bold text-foreground" : ""
        )}
      >
        {tab.title}
        {tab.comingSoonLabel ? (
          <span className="rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium tracking-wide text-muted-foreground uppercase ring-1 ring-border">
            {tab.comingSoonLabel}
          </span>
        ) : null}
      </span>
      <span className="mt-1 hidden text-xs font-normal text-muted-foreground md:inline">
        {tab.subtitle}
      </span>
    </>
  );

  const tabClassName = cn(
    "flex flex-1 flex-col items-center justify-center rounded-lg px-4 py-3 text-center transition-all duration-200 select-none",
    tab.disabled ? "cursor-not-allowed opacity-50" : "cursor-pointer",
    tab.isActive && !tab.disabled
      ? "bg-background text-primary shadow-sm"
      : tab.disabled
        ? "text-muted-foreground/80"
        : "text-muted-foreground/80 hover:bg-background/40 hover:text-foreground/90"
  );

  if (tab.disabled) {
    const stub = (
      <span
        role="tab"
        aria-disabled="true"
        aria-selected={false}
        tabIndex={-1}
        className={tabClassName}
      >
        {content}
      </span>
    );

    if (!tab.disabledTooltip) {
      return stub;
    }

    return (
      <Tooltip>
        <TooltipTrigger render={stub} />
        <TooltipContent side="bottom" className="max-w-xs text-center">
          {tab.disabledTooltip}
        </TooltipContent>
      </Tooltip>
    );
  }

  return (
    <Link href={tab.href} role="tab" aria-selected={tab.isActive} className={tabClassName}>
      {content}
    </Link>
  );
}

export function CompanyTabs({ companyId }: CompanyTabsProps) {
  const pathname = usePathname();

  const tabs: CompanyTab[] = [
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
      isActive: EXCEL_IMPORT_ENABLED
        ? pathname === `/user/company/${companyId}/excel`
        : false,
      disabled: !EXCEL_IMPORT_ENABLED,
      comingSoonLabel: "В разработке",
      disabledTooltip:
        "Функция формирования XML из Excel (редакция от июня 2026) будет доступна позже",
    },
  ];

  return (
    <TooltipProvider>
      <div
        className="flex w-full gap-1.5 rounded-xl bg-muted/60 p-1.5 shadow-inner"
        role="tablist"
        aria-label="Разделы работы с реестром"
      >
        {tabs.map((tab) => (
          <CompanyTabItem key={tab.title} tab={tab} />
        ))}
      </div>
    </TooltipProvider>
  );
}
