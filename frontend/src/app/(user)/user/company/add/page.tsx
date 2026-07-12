import AddCompanyForm from "@/components/User/Company/AddCompanyForm";
import { fetchCompaniesAction } from "@/actions/company";
import { checkSubscriptionAccessAction } from "@/actions/subscription";

export default async function AddCompanyPage() {
  const [companiesResult, accessResult] = await Promise.all([
    fetchCompaniesAction(),
    checkSubscriptionAccessAction(),
  ]);

  const totalCompanyCount = companiesResult.data?.length ?? 0;
  const plan = accessResult.data?.plan ?? null;

  return <AddCompanyForm plan={plan} totalCompanyCount={totalCompanyCount} />;
}
