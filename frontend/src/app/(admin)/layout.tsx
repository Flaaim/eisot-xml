import { Metadata } from "next";
import { redirect } from "next/navigation";
import { Toaster } from "sonner";
import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { AdminSidebar } from "@/components/Admin/AdminSidebar";
import { fetchUser } from "@/actions/auth";

export const metadata: Metadata = {
  title: "Admin Panel",
  description: "Мониторинг пользователей и подписок eisot-xml",
};

export default async function AdminLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  let profile;
  try {
    profile = await fetchUser();
  } catch {
    redirect("/join/login");
  }

  if (profile.role !== "admin") {
    redirect("/user/company");
  }

  return (
    <SidebarProvider>
      <div className="grid min-h-screen w-full grid-cols-[auto_1fr] max-[765px]:grid-cols-1">
        <AdminSidebar email={profile.email} />
        <div className="flex min-h-screen flex-col">
          <header className="flex h-16 shrink-0 items-center gap-2 border-b bg-background px-4">
            <SidebarTrigger className="-ml-1" />
            <div className="mx-2 my-auto h-4 w-px bg-border" />
            <span className="font-medium">Admin Panel</span>
          </header>
          <main className="flex-1 p-6 max-[765px]:p-2.5">{children}</main>
        </div>
      </div>
      <Toaster position="top-center" richColors />
    </SidebarProvider>
  );
}
