import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {Users, GraduationCap, CheckCircle2, Badge, Building2, AlertCircle, SearchX} from "lucide-react";
import {fetchCompanyAction} from "@/actions/company";
import Link from "next/link";
import {Button} from "@/components/ui/button";

interface CompanyOverviewPageProps {
  params: Promise<{ companyId: string }>;
}

export default async function CompanyOverviewPage({ params }: CompanyOverviewPageProps) {
  const { companyId } = await params;

  const result = await fetchCompanyAction(companyId);

  if(!result.ok){
    return (
      <div className="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center animate-fade-in">
        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10">
          <AlertCircle className="h-6 w-6 text-destructive" />
        </div>
        <h3 className="mt-4 text-lg font-semibold">Ошибка загрузки данных</h3>
        <p className="mb-4 mt-2 text-sm text-muted-foreground max-w-sm">
          Не удалось получить информацию о компании. {result.error}
        </p>
        <Link href="/user/company">
          <Button variant="outline" size="sm">
            Вернуться к списку
          </Button>
        </Link>
      </div>
    )
  }
  const company = result.data;
  if(!company) {
    return (
      <div className="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center">
      <div className="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
        <SearchX className="h-6 w-6 text-muted-foreground" />
      </div>
      <h3 className="mt-4 text-lg font-semibold">Компания не найдена</h3>
      <p className="mb-4 mt-2 text-sm text-muted-foreground max-w-sm">
        Запрашиваемая компания не существует или была удалена.
      </p>
      <Link href="/user/company">
        <Button size="sm">
          К списку компаний
        </Button>
      </Link>
    </div>
    );
  }

  const companyStatus = !company?.is_archived ? 'Активна' : 'В архиве';
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
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Обзор компании</h1>
        <p className="text-muted-foreground text-sm mt-1">
          Рабочее пространство компании
        </p>
      </div>
      <Card>
        <CardContent className="flex items-center justify-between p-6">
          <div className="space-y-1">
            <div className="flex items-center gap-2">
              <Building2 className="h-5 w-5 text-muted-foreground" />
              <span className="text-sm font-medium text-muted-foreground">Компания</span>
            </div>
            <p className="text-xl font-bold">{company?.name}</p>
            <p className="text-sm text-muted-foreground">ИНН: {company?.inn}</p>
          </div>
        </CardContent>
      </Card>
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
