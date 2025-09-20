import z from "zod";
import { usePhpSWR, usePhpSWRConfig } from "../php";
import { zodAPI, zodUserData } from "../zod";

export function useSession(config?: usePhpSWRConfig) {
  return usePhpSWR(
    "/api/me",
    zodAPI.extend({ data: zodUserData.nullable() }),
    config,
  );
}

export function useUsers(config?: usePhpSWRConfig) {
  const data = z.array(zodUserData);
  return usePhpSWR("/api/users", zodAPI.extend({ data }), config);
}
