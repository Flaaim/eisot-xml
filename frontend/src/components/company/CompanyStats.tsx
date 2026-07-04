import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, GraduationCap, CheckCircle2 } from "lucide-react";
import { getCompanyStatsAction } from "@/actions/company";
import { Skeleton } from "@/components/ui/skeleton";

interface CompanyStatsProps {
  companyId: string;
}

export async function CompanyStats({ companyId }: CompanyStatsProps) {
  const statsData = await getCompanyStatsAction(companyId);

  const stats = [
    {
      title: "Работники",
      value: statsData.workersCount.toString(),
      description: "Зарегистрировано в системе",
      icon: Users,
    },
    {
      title: "Протоколы",
      value: statsData.protocolsCount.toString(),
      description: "Протоколов обучения",
      icon: GraduationCap,
    },
    {
      title: "Статус",
      value: statsData.status,
      description: "Текущий статус компании",
      icon: CheckCircle2,
    },
  ];

  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {stats.map((stat) => (
        <Card key={stat.title} className="border bg-muted/40 shadow-none">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              {stat.title}
            </CardTitle>
            <stat.icon className="size-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stat.value}</div>
            <p className="mt-1 text-xs text-muted-foreground">{stat.description}</p>
          </CardContent>
        </Card>
      ))}
    </div>
  );
}

export function CompanyStatsSkeleton() {
  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {Array.from({ length: 3 }).map((_, i) => (
        <Card key={i} className="border bg-muted/40 shadow-none">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <Skeleton className="h-4 w-[100px]" />
            <Skeleton className="size-4 rounded-full" />
          </CardHeader>
          <CardContent>
            <Skeleton className="h-8 w-[60px]" />
            <Skeleton className="mt-2 h-3 w-[150px]" />
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
