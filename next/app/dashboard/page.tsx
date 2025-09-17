import { DashboardMain } from "@/components/layout/section";
import { R } from "@/components/ui/motion";
import { routesMeta } from "@/lib/routes";

export default function Page() {
  return (
    <DashboardMain
      currentPage={routesMeta["/dashboard"].displayName}
      className="items-center justify-center"
    >
      <R />
    </DashboardMain>
  );
}
