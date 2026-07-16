import React from "react";
import { Metadata } from "next";
import { Toaster } from "sonner";
import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { DashboardSidebar } from "@/components/User/Dashboard/DashboardSidebar";
import { fetchUser } from "@/actions/auth";
import { checkSubscriptionAccessAction } from "@/actions/subscription";
import { SubscriptionStatusBadge } from "@/components/User/Subscription/SubscriptionStatusBadge";
import Link from "next/link";
import { redirect } from "next/navigation";

export const metadata: Metadata = {
  title: "Панель пользователя",
  description: "Описание страницы",
};

export default async function UserDashboardLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  let profile;
  try {
    profile = await fetchUser();
  } catch (error) {
    console.error("Ошибка авторизации в лейауте, перенаправление...", error);
    redirect("/join/login");
  }

  const accessResult = await checkSubscriptionAccessAction();
  const subscriptionAccess = accessResult.data ?? {
    hasAccess: false,
    plan: null,
    status: null,
    periodStart: null,
    periodEnd: null,
    trialUsed: false,
    trialAvailable: false,
  };

  return (
    <SidebarProvider>
      <div className="grid min-h-screen w-full grid-cols-[auto_1fr] max-[765px]:grid-cols-1">
        <DashboardSidebar email={profile.email} />
        <div className="flex min-h-screen flex-col">
          <header className="flex h-16 shrink-0 items-center gap-2 border-b bg-background px-4">
            <SidebarTrigger className="-ml-1" />
            <div className="mx-2 my-auto h-4 w-px bg-border" />
            <span className="font-medium">Панель пользователя</span>
            <div className="ml-auto flex items-center gap-3">
              <Link href="/user/subscription">
                <SubscriptionStatusBadge access={subscriptionAccess} />
              </Link>
            </div>
          </header>
          <main className="flex-1 p-6 max-[765px]:p-2.5">{children}</main>
          <footer className="border-t p-4 text-sm text-muted-foreground">
            <small>Григорьев Александр Иванович, ИНН 272497691420, flaeim@gmail.com</small>
          </footer>
        </div>
      </div>

      <Toaster position="top-center" richColors />
    </SidebarProvider>
  );
}
