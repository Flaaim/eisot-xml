import Link from "next/link";
import React from "react";
import { Metadata } from "next";
import { Toaster } from "sonner";
import { FileCode2 } from "lucide-react";

import { Button } from "@/components/ui/button";

export const metadata: Metadata = {
  title: "ЕИСОТ XML — автоматизация реестра обученных лиц",
  description:
    "Инструмент для формирования XML-файлов реестра обученных лиц для Минтруда по схеме XSD 1.0.9. Валидация СНИЛС, ИНН и программ обучения.",
};

export default function SiteLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <div className="flex min-h-screen flex-col bg-background">
      <header className="sticky top-0 z-50 border-b border-border/80 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
        <div className="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
          <Link href="/" className="flex items-center gap-2 font-semibold text-foreground">
            <FileCode2 className="size-5 text-primary" />
            <span>eisot-xml</span>
          </Link>
          <nav className="flex items-center gap-2">
            <Button render={<Link href="/join/login" />} variant="ghost" size="sm">
              Вход
            </Button>
            <Button render={<Link href="/join/register" />} size="sm">
              Регистрация
            </Button>
          </nav>
        </div>
      </header>

      <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 sm:py-10">
        {children}
        <Toaster position="top-center" richColors />
      </main>

      <footer className="border-t border-border bg-muted/30">
        <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-6 text-center text-sm text-muted-foreground sm:flex-row sm:px-6 sm:text-left">
          <p>© 2026 eisot-xml · подготовка реестров для ЕИСОТ</p>
          <p className="text-xs">
            Схема XSD v1.0.9 · актуальная редакция июнь 2026
            <br />
            <small>Григорьев Александр Иванович, ИНН 272497691420, flaeim@gmail.com</small>
          </p>
        </div>
      </footer>
    </div>
  );
}
