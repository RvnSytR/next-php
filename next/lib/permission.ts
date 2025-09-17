import { LucideIcon, ShieldUser, UserRound } from "lucide-react";
import z from "zod";
import { zodUserData } from "./zod";

export type User = z.infer<typeof zodUserData>;

export type Role = (typeof allRoles)[number];
export const allRoles = ["user", "admin"] as const;

export const rolesMeta: Record<
  Role,
  {
    displayName: string;
    desc: string;
    icon: LucideIcon;
    color: string | { light: string; dark: string };
  }
> = {
  user: {
    displayName: "Pengguna",
    icon: UserRound,
    desc: "Pengguna standar dengan akses dan izin dasar.",
    color: "var(--primary)",
  },
  admin: {
    displayName: "Admin",
    icon: ShieldUser,
    desc: "Administrator dengan akses penuh dan kontrol pengelolaan sistem.",
    color: "var(--rvns)",
  },
};
