import useSWR, { mutate, SWRConfiguration, SWRResponse } from "swr";
import { ZodType } from "zod";
import { appMeta } from "./meta";
import { PHPResponse } from "./php";
import { fetcher } from "./utils";
import { zodPHPResponse } from "./zod";

export type usePhpSWRConfig = {
  fetcher?: Omit<RequestInit, "credentials">;
  swr?: SWRConfiguration;
};

export function usePhpSWR<T>(
  key: string,
  schema: ZodType<T>,
  config?: usePhpSWRConfig,
): SWRResponse<PHPResponse & { data: T }> {
  const { host, credentials } = appMeta.php;

  const ky = `${host}${key}`;
  const sc = zodPHPResponse.extend({ data: schema });
  const fn = async () =>
    await fetcher(ky, sc, { credentials, ...config?.fetcher });

  return useSWR(ky, fn, config?.swr);
}

export function phpMutate(key: string) {
  return mutate(`${appMeta.php.host}${key}`);
}
