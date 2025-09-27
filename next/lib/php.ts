import z, { ZodType } from "zod";
import { appMeta } from "./meta";
import { fetcher } from "./utils";
import { zodPHPResponse } from "./zod";

export type PHPResponse<T> = z.infer<typeof zodPHPResponse> & { data: T };
type PHPFetcherConfig = Omit<RequestInit, "credentials">;

export function phpFetcher<T>(
  key: string,
  schema: ZodType<T>,
  config?: PHPFetcherConfig,
): Promise<PHPResponse<T>> {
  const { host, credentials } = appMeta.php;
  const sc = zodPHPResponse.extend({ data: schema });
  return fetcher(`${host}${key}`, sc, { credentials, ...config });
}

export function phpAction(key: string, config?: PHPFetcherConfig) {
  return phpFetcher(key, z.null(), config);
}
