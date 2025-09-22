import z from "zod";
import { zodUserData } from "../zod";
import { usePhpSWR, usePhpSWRConfig } from "./swr";

export function useSession(config?: usePhpSWRConfig) {
  return usePhpSWR("/api/me", zodUserData.nullable(), config);
}

export function useUsers(config?: usePhpSWRConfig) {
  return usePhpSWR("/api/users", z.array(zodUserData), config);
}
