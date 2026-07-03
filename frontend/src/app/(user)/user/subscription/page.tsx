import { checkSubscriptionAccessAction } from "@/actions/subscription";
import { SubscriptionPlans } from "@/components/User/Subscription/SubscriptionPlans";

export default async function SubscriptionPage() {
  const accessResult = await checkSubscriptionAccessAction();

  const access = accessResult.data ?? {
    hasAccess: false,
    plan: null,
    status: null,
    periodStart: null,
    periodEnd: null,
  };

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 md:p-8">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Тарифы и подписка</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          User Subscription разблокирует формирование XML-реестра (RegistrySet) для всех ваших
          компаний в соответствии с требованиями ЕИСОТ Минтруда России.
        </p>
      </div>
      <SubscriptionPlans initialAccess={access} />
    </div>
  );
}
