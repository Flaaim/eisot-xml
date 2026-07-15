import { fetchUser } from "@/actions/auth";
import { redirect } from "next/navigation";
import RequestChangePassword from "@/components/Auth/Password/Change/ChangePasswordForm";

export default async function changePasswordPage() {
  let profile;
  try {
    profile = await fetchUser();
  } catch (error) {
    console.error("Ошибка авторизации в лейауте, перенаправление...", error);
    redirect("/join/login");
  }
  return <RequestChangePassword profile={profile} />;
}
