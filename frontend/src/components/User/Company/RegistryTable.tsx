"use client";

import { useState } from "react";
import { toast } from "sonner";
import { Download, CheckSquare, Square, FileCode, CheckCircle2, XCircle } from "lucide-react";

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Checkbox } from "@/components/ui/checkbox";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  CardFooter,
} from "@/components/ui/card";
import { RegistryRecordDto, exportRegistryToXmlAction } from "@/actions/registry";
import { AccessRestrictedDialog } from "@/components/User/Subscription/AccessRestrictedDialog";

interface RegistryTableProps {
  readonly records: RegistryRecordDto[];
  readonly hasSubscriptionAccess: boolean;
}

export function RegistryTable({ records, hasSubscriptionAccess }: RegistryTableProps) {
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());
  const [isExporting, setIsExporting] = useState(false);
  const [accessDialogOpen, setAccessDialogOpen] = useState(false);

  const isAllSelected = records.length > 0 && selectedIds.size === records.length;
  const isSomeSelected = selectedIds.size > 0 && selectedIds.size < records.length;

  const toggleSelectAll = () => {
    if (isAllSelected) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(records.map((r) => r.id)));
    }
  };

  const toggleRecord = (id: string) => {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  };

  const handleExport = async () => {
    if (selectedIds.size === 0) {
      toast.warning("Выберите хотя бы одну запись для формирования экспорта.");
      return;
    }

    if (!hasSubscriptionAccess) {
      setAccessDialogOpen(true);
      return;
    }

    setIsExporting(true);
    try {
      const result = await exportRegistryToXmlAction(Array.from(selectedIds));

      if (!result.ok) {
        if (result.error === "subscription_required") {
          setAccessDialogOpen(true);
          return;
        }
        toast.error(result.error || "Не удалось экспортировать данные в XML.");
        return;
      }

      const xmlContent = result.data?.xmlContent;
      if (!xmlContent) {
        toast.error("Получен пустой XML-контент.");
        return;
      }

      // Create blob download
      const blob = new Blob([xmlContent], { type: "application/xml;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = `eisot-export-${new Date().toISOString().slice(0, 10)}.xml`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);

      toast.success("XML-файл успешно сформирован и скачан!");
    } catch (error) {
      console.error("Export XML Error:", error);
      toast.error("Произошла непредвиденная ошибка во время экспорта.");
    } finally {
      setIsExporting(false);
    }
  };

  return (
    <>
      <AccessRestrictedDialog open={accessDialogOpen} onOpenChange={setAccessDialogOpen} />
      <Card className="shadow-sm">
        <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-4">
          <div>
            <CardTitle className="text-lg font-semibold flex items-center gap-2">
              <FileCode className="h-5 w-5 text-primary" />
              Реестр протоколов обучения
            </CardTitle>
            <CardDescription>
              Выделите сотрудников и протоколы для выгрузки в формате XML для ЕИСОТ Минтруда.
            </CardDescription>
          </div>
          <div className="flex items-center gap-3">
            <span className="text-xs font-medium text-muted-foreground">
              Выбрано: {selectedIds.size} из {records.length}
            </span>
            <Button
              onClick={handleExport}
              disabled={selectedIds.size === 0 || isExporting}
              className="flex items-center gap-2 cursor-pointer min-w-[170px]"
            >
              {isExporting ? (
                <>
                  <svg className="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle
                      className="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      strokeWidth="4"
                    />
                    <path
                      className="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    />
                  </svg>
                  Формирование...
                </>
              ) : (
                <>
                  <Download className="h-4 w-4" />
                  Сформировать XML
                </>
              )}
            </Button>
          </div>
        </CardHeader>
        <CardContent className="p-0 border-t">
          {records.length === 0 ? (
            <div className="flex flex-col items-center justify-center p-8 text-center min-h-[250px]">
              <p className="text-sm text-muted-foreground">
                Записи об обучении отсутствуют. Сначала зарегистрируйте сотрудников и протоколы.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-12 text-center">
                      <Checkbox
                        checked={isAllSelected}
                        onCheckedChange={toggleSelectAll}
                        aria-label="Выбрать все записи"
                      />
                    </TableHead>
                    <TableHead>ФИО</TableHead>
                    <TableHead>СНИЛС</TableHead>
                    <TableHead>Профессия</TableHead>
                    <TableHead className="max-w-[200px]">Программа обучения</TableHead>
                    <TableHead>Результат</TableHead>
                    <TableHead>Дата</TableHead>
                    <TableHead>№ Протокола</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {records.map((row) => {
                    const isSelected = selectedIds.has(row.id);
                    const isPassed = row.result === "удовлетворительно";

                    return (
                      <TableRow key={row.id} data-state={isSelected ? "selected" : undefined}>
                        <TableCell className="text-center">
                          <Checkbox
                            checked={isSelected}
                            onCheckedChange={() => toggleRecord(row.id)}
                            aria-label={`Выбрать запись ${row.workerFullName}`}
                          />
                        </TableCell>
                        <TableCell className="font-medium">{row.workerFullName}</TableCell>
                        <TableCell className="whitespace-nowrap">{row.snils}</TableCell>
                        <TableCell>{row.profession}</TableCell>
                        <TableCell className="max-w-[200px] truncate" title={row.programTitle}>
                          {row.programTitle}
                        </TableCell>
                        <TableCell>
                          {isPassed ? (
                            <span className="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                              <CheckCircle2 className="h-3.5 w-3.5" />
                              Удовл.
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 text-xs font-medium text-destructive">
                              <XCircle className="h-3.5 w-3.5" />
                              Неудовл.
                            </span>
                          )}
                        </TableCell>
                        <TableCell className="whitespace-nowrap">
                          {new Date(row.date).toLocaleDateString("ru-RU", {
                            day: "2-digit",
                            month: "2-digit",
                            year: "numeric",
                          })}
                        </TableCell>
                        <TableCell className="font-mono text-xs">{row.protocolNumber}</TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>
    </>
  );
}
