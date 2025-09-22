import useSWR, { SWRConfiguration, SWRResponse } from "swr";
import { ZodType } from "zod";
import { appMeta } from "../meta";
import { PHPResponse } from "../php";
import { fetcher } from "../utils";
import { zodPHPResponse } from "../zod";

export type usePhpSWRConfig = {
  fetcher?: Omit<RequestInit, "credentials">;
  swr?: SWRConfiguration;
};

export function useValidatedSWR<T>(
  key: string,
  schema: ZodType<T>,
  config?: { fetcher?: RequestInit; swr?: SWRConfiguration },
): SWRResponse<T> {
  const fn = async () => await fetcher(key, schema, config?.fetcher);
  return useSWR(key, fn, config?.swr);
}

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
