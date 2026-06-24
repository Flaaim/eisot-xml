"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Archive, RotateCcw } from "lucide-react";
import { Button } from "@/components/ui/button";
import { archiveCompanyAction, unarchiveCompanyAction } from "@/actions/company";
import { toast } from "sonner";

interface CompanyStatusToggleProps {
  companyId: string;
  status: "ACTIVE" | "ARCHIVED";
}

export function CompanyStatusToggle({ companyId, status }: CompanyStatusToggleProps) {
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  const handleToggle = async () => {
    setLoading(true);
    try {
      if (status === "ACTIVE") {
        const res = await archiveCompanyAction(companyId);
        if (res.ok) {
          toast.success("Компания успешно отправлена в архив");
          router.refresh();
        } else {
          toast.error(res.error || "Не удалось отправить компанию в архив");
        }
      } else {
        const res = await unarchiveCompanyAction(companyId);
        if (res.ok) {
          toast.success("Компания успешно восстановлена из архива");
          router.refresh();
        } else {
          toast.error(res.error || "Не удалось восстановить компанию");
        }
      }
    } catch (err) {
      toast.error("Произошла ошибка при смене статуса компании");
    } finally {
      setLoading(false);
    }
  };

  if (status === "ACTIVE") {
    return (
      <Button
        variant="outline"
        onClick={handleToggle}
        disabled={loading}
        className="flex items-center gap-2 text-destructive hover:bg-destructive/10 hover:text-destructive border-destructive/20"
      >
        <Archive className="h-4 w-4" />
        {loading ? "Обработка..." : "Отправить в архив"}
      </Button>
    );
  }

  return (
    <Button
      variant="outline"
      onClick={handleToggle}
      disabled={loading}
      className="flex items-center gap-2"
    >
      <RotateCcw className="h-4 w-4" />
      {loading ? "Обработка..." : "Восстановить из архива"}
    </Button>
  );
}
