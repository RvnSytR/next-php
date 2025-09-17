import z from "zod";
import { usePhpSWR, usePhpSWRConfig } from "../php";
import { zodAPI, zodUserData } from "../zod";

export function useSession(config?: usePhpSWRConfig) {
  return usePhpSWR(
    "/api/profile",
    zodAPI.extend({ data: zodUserData.nullable() }),
    config,
  );
}

export function useUser(config?: usePhpSWRConfig) {
  const data = z.array(zodUserData);
  return usePhpSWR("/api/user", zodAPI.extend({ data }), config);
}
