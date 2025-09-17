import useSWR, { mutate, SWRConfiguration, SWRResponse } from "swr";
import { ZodType } from "zod";
import { appMeta } from "./meta";
import { zodAPI } from "./zod";

type UseValidatedSWRConfig = {
  fetcher?: RequestInit;
  swr?: SWRConfiguration;
};

export type UseAPISWRConfig = {
  fetcher?: Omit<RequestInit, "credentials">;
  swr?: SWRConfiguration;
};

async function fetcher<T>(
  key: string,
  schema: ZodType<T>,
  config?: RequestInit,
): Promise<T> {
  const res = await fetch(key, config);
  const json = await res.json();

  if (!res.ok) throw json;
  if (!schema) return json;

  try {
    return schema.parse(json);
  } catch (e) {
    console.error(e);
    throw e;
  }
}

function useValidatedSWR<T>(
  key: string,
  schema: ZodType<T>,
  config?: UseValidatedSWRConfig,
): SWRResponse<T> {
  return useSWR(
    key,
    async (key) => await fetcher(key, schema, config?.fetcher),
    config?.swr,
  );
}

export async function action(
  key: string,
  config?: Omit<RequestInit, "credentials">,
) {
  const { origin, credentials } = appMeta.api;
  return fetcher(`${origin}${key}`, zodAPI, { credentials, ...config });
}

export async function mutateApi(key: string) {
  return mutate(`${appMeta.api.origin}${key}`);
}

export function useApiSWR<T>(
  key: string,
  schema: ZodType<T>,
  config?: UseAPISWRConfig,
): SWRResponse<T> {
  const { origin, credentials } = appMeta.api;
  return useValidatedSWR(`${origin}${key}`, schema, {
    fetcher: { credentials, ...config?.fetcher },
    swr: config?.swr,
  });
}
