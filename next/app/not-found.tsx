import { Button } from "@/components/ui/button";
import { LinkLoader } from "@/components/ui/buttons-client";
import Link from "next/link";

export default function NotFound() {
  return (
    <div className="mask-radial-from-75% mask-alpha flex min-h-dvh flex-col items-center justify-center gap-y-4">
      <p className="text-2xl font-light">404 | Halaman Tidak Ditemukan</p>
      <Button variant="link" className="font-light" asChild>
        <Link href="/">
          <LinkLoader /> Kembali ke Beranda
        </Link>
      </Button>
    </div>
  );
}
