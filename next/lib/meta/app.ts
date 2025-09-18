import { Language } from "./other";

const isProd = process.env.NODE_ENV === "production";

export const appMeta = {
  name: "Nama Proyek",
  description: "Deskripsi Proyek",
  keywords: ["next starter"],

  // logo: "/some-image.png"
  lang: "id" satisfies Language as Language,

  php: {
    host: isProd ? "" : "http://localhost",
    credentials: (isProd ? "same-origin" : "include") as RequestCredentials,
  },
};
