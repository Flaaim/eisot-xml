"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { LayoutDashboard, Users, CreditCard, LogOut, Shield } from "lucide-react";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar";
import { Logout } from "@/actions/auth";

const items = [
  { title: "Дашборд", url: "/admin", icon: LayoutDashboard },
  { title: "Пользователи", url: "/admin/users", icon: Users },
  { title: "Транзакции", url: "/admin/transactions", icon: CreditCard },
];

interface AdminSidebarProps {
  readonly email: string;
}

export function AdminSidebar({ email }: AdminSidebarProps) {
  const pathname = usePathname();

  const handleLogout = async () => {
    await Logout();
  };

  return (
    <Sidebar>
      <SidebarHeader>
        <div className="flex items-center gap-2 px-2 py-1.5 text-sm font-semibold">
          <Shield className="size-4 text-primary" />
          Admin Panel
        </div>
      </SidebarHeader>
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel>Мониторинг</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {items.map((item) => {
                const isActive =
                  item.url === "/admin" ? pathname === "/admin" : pathname.startsWith(item.url);

                return (
                  <SidebarMenuItem key={item.title}>
                    <SidebarMenuButton
                      isActive={isActive}
                      render={<Link href={item.url} />}
                      className="cursor-pointer"
                    >
                      <item.icon />
                      <span>{item.title}</span>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                );
              })}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg">
              <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate text-xs text-muted-foreground">{email}</span>
              </div>
            </SidebarMenuButton>
            <SidebarMenuButton
              onClick={() => {
                void handleLogout();
              }}
              className="cursor-pointer text-destructive"
            >
              <LogOut className="mr-2 size-4" />
              <span>Выйти из системы</span>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarFooter>
    </Sidebar>
  );
}
