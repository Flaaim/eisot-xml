import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import React from "react";
import YandexMetrica from "@/components/Yandex/YandexMetrica";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["cyrillic"],
  weight: ["400"],
});

export const metadata: Metadata = {
  title: "Rtn-tests.ru",
  description: "Шаблон",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ru">
      <body className={`${inter.className} flex min-h-full flex-col antialiased`}>{children}</body>

      <YandexMetrica counterId="110731514" />
    </html>
  );
}
