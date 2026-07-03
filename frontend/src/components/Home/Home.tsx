import Link from "next/link";
import {
  ArrowRight,
  Building2,
  CheckCircle2,
  FileCode2,
  Layers,
  ShieldCheck,
  Upload,
  UserCheck,
  Zap,
} from "lucide-react";

import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";

const BENEFITS = [
  {
    icon: ShieldCheck,
    title: "Автоматическая проверка данных",
    description:
      "Система сама проверит правильность СНИЛС и ИНН работодателя, учтёт особые правила для иностранных сотрудников и не даст отправить отчёт с ошибкой.",
  },
  {
    icon: Layers,
    title: "До 5 000 сотрудников в одном файле",
    description:
      "Формируйте большие реестры за один раз — не нужно вручную собирать таблицы, разбивать на части или бояться превысить лимиты системы.",
  },
  {
    icon: FileCode2,
    title: "Полное соответствие требованиям Минтруда",
    description:
      "Все созданные документы точно проходят проверку в личном кабинете ЕИСОТ — мы следим за актуальной версией формата и обновляем сервис.",
  },
] as const;

const STEPS = [
  {
    step: 1,
    icon: Building2,
    title: "Укажите данные организации",
    description:
      "Введите ИНН и название компании. Система запомнит их и будет автоматически подставлять во все будущие реестры.",
  },
  {
    step: 2,
    icon: UserCheck,
    title: "Добавьте сотрудников",
    description:
      "Внесите ФИО, СНИЛС, должности и данные о пройденном обучении. Можно добавить как одного человека, так и загрузить список целиком.",
  },
  {
    step: 3,
    icon: Zap,
    title: "Сформируйте файл в один клик",
    description:
      "Нажмите кнопку — и система сама создаст готовый XML-файл, проверив все данные перед сохранением. Никакой ручной правки кода.",
  },
  {
    step: 4,
    icon: Upload,
    title: "Загрузите в личный кабинет Минтруда",
    description:
      "Полученный файл сразу готов к загрузке в раздел «Записи, ожидающие добавления» на портале ЕИСОТ.",
  },
] as const;

function HeroSection() {
  return (
    <section className="relative overflow-hidden rounded-2xl border border-border bg-gradient-to-br from-primary/10 via-background to-muted/50 px-6 py-14 sm:px-10 sm:py-16">
      <div className="relative mx-auto max-w-3xl text-center">
        <p className="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-xs font-medium text-primary">
          <CheckCircle2 className="size-3.5" />
          Актуальная версия — июнь 2026
        </p>
        <h1 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl lg:text-5xl">
          Подготовка реестров для ЕИСОТ без ошибок
        </h1>
        <p className="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-muted-foreground sm:text-lg">
          Больше не нужно вручную собирать XML-файлы и переживать из-за ошибок при загрузке. Просто
          заполните данные о сотрудниках, а система сама сформирует правильный документ для
          Минтруда. Специалисты по охране труда уже экономят часы работы с нашим сервисом.
        </p>
        <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
          <Button
            nativeButton={false}
            render={<Link href="/user/company" />}
            size="lg"
            className="min-w-[220px]"
          >
            Создать реестр
            <ArrowRight className="size-4" />
          </Button>
          <Button
            nativeButton={false}
            render={<Link href="/join/login" />}
            variant="outline"
            size="lg"
            className="min-w-[160px]"
          >
            Войти в систему
          </Button>
        </div>
      </div>
    </section>
  );
}

function BenefitsSection() {
  return (
    <section className="mt-16" aria-labelledby="benefits-heading">
      <div className="mb-8 text-center">
        <h2 id="benefits-heading" className="text-2xl font-semibold tracking-tight sm:text-3xl">
          Почему это удобно
        </h2>
        <p className="mt-2 text-muted-foreground">
          Мы встроили все требования законодательства прямо в процесс заполнения
        </p>
      </div>
      <div className="grid gap-6 md:grid-cols-3">
        {BENEFITS.map(({ icon: Icon, title, description }) => (
          <Card
            key={title}
            className="border-border/80 shadow-sm transition-shadow hover:shadow-md"
          >
            <CardHeader>
              <div className="mb-2 flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <Icon className="size-5" />
              </div>
              <CardTitle className="text-lg">{title}</CardTitle>
            </CardHeader>
            <CardContent>
              <CardDescription className="text-sm leading-relaxed">{description}</CardDescription>
            </CardContent>
          </Card>
        ))}
      </div>
    </section>
  );
}

function UserJourneySection() {
  return (
    <section className="mt-16" aria-labelledby="journey-heading">
      <div className="mb-10 text-center">
        <h2 id="journey-heading" className="text-2xl font-semibold tracking-tight sm:text-3xl">
          Как это работает
        </h2>
        <p className="mt-2 text-muted-foreground">
          Четыре простых шага от заполнения до отправки в Минтруд
        </p>
      </div>
      <ol className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {STEPS.map(({ step, icon: Icon, title, description }) => (
          <li key={step} className="relative flex flex-col">
            <Card className="h-full border-border/80 shadow-sm">
              <CardHeader className="pb-2">
                <div className="mb-3 flex items-center gap-3">
                  <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                    {step}
                  </span>
                  <Icon className="size-5 text-primary" aria-hidden />
                </div>
                <CardTitle className="text-base leading-snug">{title}</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-sm leading-relaxed">{description}</CardDescription>
              </CardContent>
            </Card>
          </li>
        ))}
      </ol>
    </section>
  );
}

function TechnicalSection() {
  return (
    <section className="mt-16" aria-labelledby="tech-heading">
      <Card className="border-primary/20 bg-muted/30">
        <CardHeader className="sm:flex-row sm:items-start sm:gap-6">
          <div className="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <FileCode2 className="size-6" />
          </div>
          <div>
            <CardTitle id="tech-heading" className="text-xl">
              Нам можно доверять
            </CardTitle>
            <CardDescription className="mt-2 text-base leading-relaxed text-muted-foreground">
              Мы тщательно следим за всеми изменениями в требованиях Минтруда и обновляем сервис.
              Каждый созданный файл проходит многоступенчатую проверку: контрольные суммы СНИЛС,
              соответствие формату ЕИСОТ, корректность данных для иностранных сотрудников. Вы
              получаете готовый документ, который гарантированно загрузится с первого раза.
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent>
          <ul className="grid gap-2 text-sm text-muted-foreground sm:grid-cols-2">
            <li className="flex items-start gap-2">
              <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-primary" />
              <span>Проверка СНИЛС по контрольной сумме ПФР</span>
            </li>
            <li className="flex items-start gap-2">
              <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-primary" />
              <span>Автоматическая подстановка ИНН организации</span>
            </li>
            <li className="flex items-start gap-2">
              <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-primary" />
              <span>Экспорт в формате, одобренном Минтрудом (версия 1.0.9)</span>
            </li>
            <li className="flex items-start gap-2">
              <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-primary" />
              <span>Поддержка данных иностранных работников</span>
            </li>
          </ul>
        </CardContent>
      </Card>
    </section>
  );
}

export default function Home() {
  return (
    <div className="pb-8">
      <HeroSection />
      <BenefitsSection />
      <UserJourneySection />
      <TechnicalSection />
      <p className="mt-12 text-center text-xs text-muted-foreground">
        Сервис подготовки реестров для ЕИСОТ · Версия формата 1.0.9 · Актуально на июнь 2026
      </p>
    </div>
  );
}
