"use client";

import { LayoutDashboard, User, LogOut, Crown } from "lucide-react";
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
import Link from "next/link";

const items = [
  { title: "Компания", url: "/user/company", icon: LayoutDashboard },
  { title: "Подписка", url: "/user/subscription", icon: Crown },
];
export interface DashboardSidebarProps {
  email: string;
}
export function DashboardSidebar({ email }: DashboardSidebarProps) {
  const handleLogout = async () => {
    await Logout();
  };
  return (
    <Sidebar>
      <SidebarHeader />
      <SidebarContent>
        <SidebarGroup />
        <SidebarGroupLabel>Управление</SidebarGroupLabel>
        <SidebarGroupContent>
          <SidebarMenu>
            {items.map((item) => (
              <SidebarMenuItem key={item.title}>
                <SidebarMenuButton>
                  <item.icon />
                  <a href={item.url}>
                    <span>{item.title}</span>
                  </a>
                </SidebarMenuButton>
              </SidebarMenuItem>
            ))}
          </SidebarMenu>
        </SidebarGroupContent>
        <SidebarGroup />
      </SidebarContent>
      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton
              size="lg"
              className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            >
              <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                <User className="size-4" />
              </div>
              <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate text-xs">
                  <Link href="/user/profile">{email}</Link>
                </span>
              </div>
            </SidebarMenuButton>
            <SidebarMenuButton onClick={() => { void handleLogout(); }} className="cursor-pointer text-destructive">
              <LogOut className="mr-2 size-4" />
              <span>Выйти из системы</span>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarFooter>
    </Sidebar>
  );
}
