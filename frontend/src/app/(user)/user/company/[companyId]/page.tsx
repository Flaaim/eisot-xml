import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, GraduationCap, CheckCircle2 } from "lucide-react";
import { fetchCompanyAction } from "@/actions/company";
import { WorkerRegistrationForm } from "@/components/User/Company/WorkerRegistrationForm";

interface CompanyOverviewPageProps {
  params: Promise<{ companyId: string }>;
}

export default async function CompanyOverviewPage({ params }: CompanyOverviewPageProps) {
  const { companyId } = await params;

  const result = await fetchCompanyAction(companyId);

  if (!result.ok || !result.data) {
    return null; // Layout notFound() will execute
  }

  const company = result.data;
  const companyStatus = !company?.is_archived ? "Активна" : "В архиве";

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
      value: companyStatus,
      description: "Текущий статус компании",
      icon: CheckCircle2,
    },
  ];

  return (
    <div className="space-y-6">
      {/* Stats Grid */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {stats.map((stat) => (
          <Card key={stat.title} className="shadow-none bg-muted/40 border">
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

      {/* Form Area */}
      <div className="pt-4 border-t space-y-4">
        <div>
          <h2 className="text-lg font-semibold tracking-tight">Регистрация работника</h2>
          <p className="text-sm text-muted-foreground">
            Заполните данные сотрудника и его протоколы обучения.
          </p>
        </div>
        <WorkerRegistrationForm companyId={companyId} company={company} />
      </div>
    </div>
  );
}
