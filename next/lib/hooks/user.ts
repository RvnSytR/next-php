import z from "zod";
import { useApiSWR, UseAPISWRConfig } from "../fetcher";
import { zodAPI, zodUserData } from "../zod";

export function useSession(config?: UseAPISWRConfig) {
  return useApiSWR(
    "/api/profile",
    zodAPI.extend({ data: zodUserData.nullable() }),
    config,
  );
}

export function useUser(config?: UseAPISWRConfig) {
  const data = z.array(zodUserData);
  return useApiSWR("/api/user", zodAPI.extend({ data }), config);
}
