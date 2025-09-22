// * This file contains PHP-related utilities, functions and helpers.

import { mutate } from "swr";
import z, { ZodType } from "zod";
import { appMeta } from "./meta";
import { fetcher } from "./utils";
import { zodPHPResponse } from "./zod";

export type PHPResponse = z.infer<typeof zodPHPResponse>;
type PHPFetcherConfig = Omit<RequestInit, "credentials">;

export function phpFetcher<T>(
  key: string,
  schema: ZodType<T>,
  config?: PHPFetcherConfig,
): Promise<PHPResponse & { data: T }> {
  const { host, credentials } = appMeta.php;
  const sc = zodPHPResponse.extend({ data: schema });
  return fetcher(`${host}${key}`, sc, { credentials, ...config });
}

export function phpAction(key: string, config?: PHPFetcherConfig) {
  return phpFetcher(key, z.null(), config);
}

export function phpMutate(key: string) {
  return mutate(`${appMeta.php.host}${key}`);
}
