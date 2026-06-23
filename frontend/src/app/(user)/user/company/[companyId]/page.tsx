import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, GraduationCap, CheckCircle2 } from "lucide-react";

interface CompanyOverviewPageProps {
  params: Promise<{ companyId: string }>;
}

export default async function CompanyOverviewPage({ params }: CompanyOverviewPageProps) {
  const { companyId } = await params;

  const stats = [
    {
      title: "Работники",
      value: "—",
      description: "Зарегистрировано в системе",
      icon: Users,
    },
    {
      title: "Протоколы",
      value: "—",
      description: "Протоколов обучения",
      icon: GraduationCap,
    },
    {
      title: "Статус",
      value: "Активна",
      description: "Текущий статус компании",
      icon: CheckCircle2,
    },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Обзор компании</h1>
        <p className="text-muted-foreground text-sm mt-1">
          Рабочее пространство компании
        </p>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {stats.map((stat) => (
          <Card key={stat.title}>
            <CardHeader className="flex flex-row items-center justify-between pb-2">
              <CardTitle className="text-sm font-medium text-muted-foreground">
                {stat.title}
              </CardTitle>
              <stat.icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stat.value}</div>
              <p className="text-xs text-muted-foreground mt-1">{stat.description}</p>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
