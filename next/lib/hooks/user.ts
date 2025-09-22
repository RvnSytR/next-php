import z from "zod";
import { usePhpSWR, usePhpSWRConfig } from "../php-swr";
import { zodUserData } from "../zod";

export function useSession(config?: usePhpSWRConfig) {
  return usePhpSWR("/api/me", zodUserData.nullable(), config);
}

export function useUsers(config?: usePhpSWRConfig) {
  return usePhpSWR("/api/users", z.array(zodUserData), config);
}
