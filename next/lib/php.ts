// * This file contains PHP-related utilities, functions and helpers.

import useSWR, { mutate, SWRConfiguration, SWRResponse } from "swr";
import { ZodType } from "zod";
import { appMeta } from "./meta";
import { fetcher } from "./utils";
import { zodAPI } from "./zod";

export type usePhpSWRConfig = {
  fetcher?: Omit<RequestInit, "credentials">;
  swr?: SWRConfiguration;
};

export function usePhpSWR<T>(
  key: string,
  schema: ZodType<T>,
  config?: usePhpSWRConfig,
): SWRResponse<T> {
  const { origin, credentials } = appMeta.api;
  const swrKey = `${origin}${key}`;
  return useSWR(
    swrKey,
    async () =>
      await fetcher(swrKey, schema, { credentials, ...config?.fetcher }),
    config?.swr,
  );
}

export async function phpAction(
  key: string,
  config?: Omit<RequestInit, "credentials">,
) {
  const { origin, credentials } = appMeta.api;
  return await fetcher(`${origin}${key}`, zodAPI, { credentials, ...config });
}

export async function phpMutate(key: string) {
  return await mutate(`${appMeta.api.origin}${key}`);
}
