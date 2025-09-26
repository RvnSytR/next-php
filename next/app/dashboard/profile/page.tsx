import { DashboardMain } from "@/components/layout/section";
import {
  ChangePasswordForm,
  PersonalInformationCard,
} from "@/components/modules/user-client";
import {
  Card,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { routesMeta } from "@/lib/routes";
import { getTitle } from "@/lib/utils";
import { Metadata } from "next";

export const metadata: Metadata = { title: getTitle("/dashboard/profile") };

export default function Page() {
  return (
    <DashboardMain
      currentPage={routesMeta["/dashboard/profile"].displayName}
      className="items-center"
    >
      <PersonalInformationCard className="w-full scroll-m-20 lg:max-w-xl" />

      <Card id="ubah-kata-sandi" className="w-full scroll-m-20 lg:max-w-xl">
        <CardHeader className="border-b">
          <CardTitle>Ubah Kata Sandi</CardTitle>
          <CardDescription>
            Gunakan kata sandi yang kuat untuk menjaga keamanan akun Anda.
          </CardDescription>
        </CardHeader>

        <ChangePasswordForm />
      </Card>
    </DashboardMain>
  );
}
