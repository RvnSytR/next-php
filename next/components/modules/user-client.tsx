"use client";

import { actions, messages } from "@/lib/content";
import { useIsMobile, useSession, useUsers } from "@/lib/hooks";
import { appMeta, fieldsMeta, fileMeta } from "@/lib/meta";
import { allRoles, rolesMeta, User } from "@/lib/permission";
import { phpAction } from "@/lib/php";
import { phpMutate } from "@/lib/php-swr";
import { dashboardRoute, signInRoute } from "@/lib/routes";
import { capitalize } from "@/lib/utils";
import { zodSchemas, zodUser } from "@/lib/zod";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  ArrowUpRight,
  LogIn,
  LogOut,
  Save,
  Settings2,
  Trash2,
  TriangleAlert,
  UserRoundPlus,
} from "lucide-react";
import { useRouter } from "next/navigation";
import { useRef, useState } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";
import { z } from "zod";
import { getUserColumn } from "../data-table/column";
import { DataTable } from "../data-table/data-table";
import {
  ErrorFallback,
  LoadingFallback,
  SheetDetails,
} from "../layout/section";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "../ui/alert-dialog";
import { Button, buttonVariants } from "../ui/button";
import { ResetButton } from "../ui/buttons";
import {
  Card,
  CardAction,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "../ui/card";
import { Checkbox } from "../ui/checkbox";
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "../ui/dialog";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "../ui/dropdown-menu";
import { Form, FormControl, FormField } from "../ui/form";
import { FormFieldWrapper, TextFields } from "../ui/form-fields";
import { Loader } from "../ui/icons";
import { Input } from "../ui/input";
import { Label } from "../ui/label";
import { RadioGroupField } from "../ui/radio-group";
import { Separator } from "../ui/separator";
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "../ui/sheet";
import { SidebarMenuButton } from "../ui/sidebar";
import { Skeleton } from "../ui/skeleton";
import { UserAvatar, UserRoleBadge } from "./user";

const { user: userFields } = fieldsMeta;
const sharedText = {
  signIn: "Berhasil masuk - Selamat datang!",
  signOn: (social: string) => `Lanjutkan dengan ${social}`,

  passwordNotMatch: "Kata sandi tidak cocok - silakan periksa kembali.",
  revokeSession: "Cabut Sesi",
};

export function UserDataTable() {
  const {
    data: users,
    error: usersError,
    isLoading: usersLoading,
  } = useUsers();
  const {
    data: session,
    error: sessionError,
    isLoading: sessionLoading,
  } = useSession();

  const isLoading = usersLoading || sessionLoading;
  const error = usersError ?? sessionError;

  if (isLoading) return <LoadingFallback />;
  if (error || !users?.data || !session?.data?.id)
    return <ErrorFallback error={error} />;

  const id = session?.data?.id;

  return (
    <DataTable
      data={users.data}
      columns={getUserColumn(id)}
      searchPlaceholder="Cari Pengguna..."
      enableRowSelection={({ original }) => original.id !== id}
      onRowSelection={(data, table) => {
        const filteredData = data.map(({ original }) => original);
        const clearRowSelection = () => table.resetRowSelection();

        return (
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button size="sm" variant="outline">
                <Settings2 /> {actions.action}
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
              <DropdownMenuLabel className="text-center">
                Akun dipilih: {filteredData.length}
              </DropdownMenuLabel>

              <DropdownMenuSeparator />

              <DropdownMenuItem asChild>
                <AdminActionRemoveUsersDialog
                  data={filteredData}
                  onSuccess={clearRowSelection}
                />
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        );
      }}
    />
  );
}

export function UserDetailSheet({
  data,
  isCurrentUser,
}: {
  data: User;
  isCurrentUser: boolean;
}) {
  const [isOpen, setIsOpen] = useState(false);

  const details = [
    { label: userFields.email.label, content: data.email },
    { label: fieldsMeta.updatedAt, content: messages.dateAgo(data.updatedAt) },
    { label: fieldsMeta.createdAt, content: messages.dateAgo(data.createdAt) },
  ];

  return (
    <Sheet open={isOpen} onOpenChange={setIsOpen}>
      <SheetTrigger className="group hover:cursor-pointer" asChild>
        <div className="flex w-fit gap-x-1">
          <span className="link-group">{data.name}</span>
          <ArrowUpRight className="group-hover:text-primary size-3.5 transition-colors" />
        </div>
      </SheetTrigger>

      <SheetContent>
        <SheetHeader className="flex-row items-center">
          <UserAvatar {...data} className="size-10" />
          <div className="grid">
            <SheetTitle className="text-base">{data.name}</SheetTitle>
            <SheetDescription># {data.id.slice(0, 17)}</SheetDescription>
          </div>
        </SheetHeader>

        <div className="flex flex-col gap-y-3 overflow-y-auto px-4">
          <Separator />

          <div className="flex items-center gap-x-2">
            <UserRoleBadge role={data.role} />
          </div>

          <SheetDetails data={details} />

          {!isCurrentUser && (
            <>
              <Separator />
              <AdminChangeUserRoleForm data={data} setIsOpen={setIsOpen} />
            </>
          )}
        </div>

        {!isCurrentUser && (
          <SheetFooter>
            <AdminRemoveUserDialog data={data} setIsOpen={setIsOpen} />
          </SheetFooter>
        )}
      </SheetContent>
    </Sheet>
  );
}

export function SignOutButton() {
  const router = useRouter();
  const [isLoading, setIsLoading] = useState<boolean>(false);
  return (
    <SidebarMenuButton
      tooltip="Keluar"
      variant="outline_destructive"
      className="text-destructive hover:text-destructive"
      disabled={isLoading}
      onClick={() => {
        setIsLoading(true);
        toast.promise(phpAction("/api/auth/logout", { method: "DELETE" }), {
          loading: messages.loading,
          success: (res) => {
            router.push(signInRoute);
            return res.message;
          },
          error: (e) => {
            setIsLoading(false);
            return e.message;
          },
        });
      }}
    >
      <Loader loading={isLoading} icon={{ base: <LogOut /> }} /> Keluar
    </SidebarMenuButton>
  );
}

export function SignInForm() {
  const router = useRouter();
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const schema = zodUser.pick({ email: true, password: true });

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: { email: "", password: "" },
  });

  const formHandler = (formData: z.infer<typeof schema>) => {
    setIsLoading(true);

    const body = JSON.stringify(formData);
    toast.promise(phpAction("/api/auth/login", { body, method: "POST" }), {
      loading: messages.loading,
      success: (res) => {
        router.push(dashboardRoute);
        return res.message;
      },
      error: (e) => {
        setIsLoading(false);
        return e.message;
      },
    });
  };

  return (
    <Form form={form} onSubmit={formHandler}>
      <FormField
        control={form.control}
        name="email"
        render={({ field }) => (
          <TextFields type="email" field={field} {...userFields.email} />
        )}
      />

      <FormField
        control={form.control}
        name="password"
        render={({ field }) => (
          <TextFields type="password" field={field} {...userFields.password} />
        )}
      />

      <Button type="submit" disabled={isLoading}>
        <Loader loading={isLoading} icon={{ base: <LogIn /> }} />
        Masuk ke Dashboard
      </Button>
    </Form>
  );
}

export function SignUpForm() {
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const schema = zodUser
    .pick({
      name: true,
      email: true,
      newPassword: true,
      confirmPassword: true,
      agreement: true,
    })
    .refine((sc) => sc.newPassword === sc.confirmPassword, {
      message: sharedText.passwordNotMatch,
      path: ["confirmPassword"],
    });

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: "",
      email: "",
      newPassword: "",
      confirmPassword: "",
      agreement: false,
    },
  });

  const formHandler = ({ newPassword, ...rest }: z.infer<typeof schema>) => {
    setIsLoading(true);

    const body = JSON.stringify({ password: newPassword, ...rest });
    toast.promise(phpAction("/api/auth/register", { body, method: "POST" }), {
      loading: messages.loading,
      success: () => {
        setIsLoading(false);
        form.reset();
        return "Akun berhasil dibuat! Silakan masuk untuk melanjutkan.";
      },
      error: (e) => {
        setIsLoading(false);
        return e.message;
      },
    });
  };

  return (
    <Form form={form} onSubmit={formHandler}>
      <FormField
        control={form.control}
        name="name"
        render={({ field }) => (
          <TextFields type="text" field={field} {...userFields.name} />
        )}
      />

      <FormField
        control={form.control}
        name="email"
        render={({ field }) => (
          <TextFields type="email" field={field} {...userFields.email} />
        )}
      />

      <FormField
        control={form.control}
        name="newPassword"
        render={({ field }) => (
          <TextFields
            type="password"
            field={field}
            {...userFields.newPassword}
          />
        )}
      />

      <FormField
        control={form.control}
        name="confirmPassword"
        render={({ field }) => (
          <TextFields
            type="password"
            field={field}
            {...userFields.confirmPassword}
          />
        )}
      />

      <FormField
        control={form.control}
        name="agreement"
        render={({ field: { value, onChange } }) => (
          <FormFieldWrapper
            type="checkbox"
            label="Setujui syarat dan ketentuan"
            desc={`Saya menyetujui ketentuan layanan dan kebijakan privasi ${appMeta.name}.`}
          >
            <FormControl>
              <Checkbox checked={value} onCheckedChange={onChange} />
            </FormControl>
          </FormFieldWrapper>
        )}
      />

      <Button type="submit" disabled={isLoading}>
        <Loader loading={isLoading} icon={{ base: <UserRoundPlus /> }} />
        Daftar Sekarang
      </Button>
    </Form>
  );
}

export function PersonalInformationCard({ className }: { className?: string }) {
  const { data, error, isLoading } = useSession();
  return (
    <Card id="informasi-pribadi" className={className}>
      <CardHeader className="border-b">
        <CardTitle>Informasi Pribadi</CardTitle>
        <CardDescription>
          Perbarui dan kelola informasi profil {appMeta.name} Anda.
        </CardDescription>
        <CardAction>
          {data?.data ? (
            <UserRoleBadge role={data.data.role} />
          ) : (
            <Skeleton className="w-18 h-6" />
          )}
        </CardAction>
      </CardHeader>

      {isLoading && <LoadingFallback />}
      {error && <ErrorFallback error={error} className="mx-6" hideText />}
      {data?.data && <PersonalInformation {...data.data} />}
    </Card>
  );
}

function ProfilePicture({ name, image }: Pick<User, "name" | "image">) {
  const inputAvatarRef = useRef<HTMLInputElement>(null);
  const [isChange, setIsChange] = useState<boolean>(false);
  const [isRemoved, setIsRemoved] = useState<boolean>(false);

  const contentType = "image";
  const schema = zodSchemas.file(contentType);

  const changeHandler = async (fileList: FileList) => {
    setIsChange(true);
    const files = Array.from(fileList).map((f) => f);

    const parseRes = schema.safeParse(files);
    if (!parseRes.success) return toast.error(parseRes.error.message);

    const body = new FormData();
    body.append("image", files[0]);

    toast.promise(phpAction("/api/me", { body, method: "POST" }), {
      loading: messages.loading,
      success: (res) => {
        setIsChange(false);
        phpMutate("/api/me");
        return res.message;
      },
      error: (e) => {
        setIsChange(false);
        return e.message;
      },
    });
  };

  const deleteHandler = async () => {
    setIsRemoved(true);
    toast.promise(phpAction("/api/me/avatar", { method: "DELETE" }), {
      loading: messages.loading,
      success: (res) => {
        setIsRemoved(false);
        phpMutate("/api/me");
        return res.message;
      },
      error: (e) => {
        setIsRemoved(false);
        return e.message;
      },
    });
  };

  return (
    <div className="flex items-center gap-x-4">
      <UserAvatar name={name} image={image} className="size-24" />

      <input
        type="file"
        ref={inputAvatarRef}
        accept={fileMeta[contentType].mimeTypes.join(", ")}
        className="hidden"
        onChange={(e) => {
          const fileList = e.currentTarget.files;
          if (fileList) changeHandler(fileList);
        }}
      />

      <div className="flex flex-col gap-y-2">
        <Label>{userFields.avatar}</Label>
        <div className="flex flex-wrap gap-2">
          <Button
            type="button"
            size="sm"
            variant="outline"
            disabled={isChange || isRemoved}
            onClick={() => inputAvatarRef.current?.click()}
          >
            <Loader loading={isChange} /> {actions.upload} Avatar
          </Button>

          <AlertDialog>
            <AlertDialogTrigger asChild>
              <Button
                type="button"
                size="sm"
                variant="outline_destructive"
                disabled={!image || isChange || isRemoved}
              >
                <Loader loading={isRemoved} /> {actions.remove}
              </Button>
            </AlertDialogTrigger>

            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Hapus Foto Profil</AlertDialogTitle>
                <AlertDialogDescription>
                  Aksi ini akan menghapus foto profil Anda saat ini. Yakin ingin
                  melanjutkan?
                </AlertDialogDescription>
              </AlertDialogHeader>

              <AlertDialogFooter>
                <AlertDialogCancel>{actions.cancel}</AlertDialogCancel>
                <AlertDialogAction
                  className={buttonVariants({ variant: "destructive" })}
                  onClick={() => deleteHandler()}
                >
                  {actions.confirm}
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </div>
      </div>
    </div>
  );
}

function PersonalInformation({ ...props }: User) {
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const { name, email } = props;
  const schema = zodUser.pick({ name: true, email: true });

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: { name, email },
  });

  const formHandler = ({ name: newName }: z.infer<typeof schema>) => {
    if (newName === name) return toast.info(messages.noChanges("profil Anda"));

    setIsLoading(true);
    const body = new FormData();
    body.append("name", newName);

    toast.promise(phpAction("/api/me", { body, method: "POST" }), {
      loading: messages.loading,
      success: (res) => {
        setIsLoading(false);
        phpMutate("/api/me");
        return res.message;
      },
      error: (e) => {
        setIsLoading(false);
        return e.message;
      },
    });
  };

  return (
    <Form form={form} onSubmit={formHandler}>
      <CardContent className="flex flex-col gap-y-4">
        <ProfilePicture {...props} />

        <FormField
          control={form.control}
          name="email"
          render={({ field }) => (
            <TextFields
              type="email"
              field={field}
              disabled
              {...userFields.email}
            />
          )}
        />

        <FormField
          control={form.control}
          name="name"
          render={({ field }) => (
            <TextFields type="text" field={field} {...userFields.name} />
          )}
        />
      </CardContent>

      <CardFooter className="flex-col items-stretch border-t md:flex-row md:items-center">
        <Button type="submit" disabled={isLoading}>
          <Loader loading={isLoading} icon={{ base: <Save /> }} />
          {actions.update}
        </Button>

        <ResetButton onClick={() => form.reset()} />
      </CardFooter>
    </Form>
  );
}

export function ChangePasswordForm() {
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const schema = zodUser
    .pick({
      currentPassword: true,
      newPassword: true,
      confirmPassword: true,
    })
    .refine((sc) => sc.newPassword === sc.confirmPassword, {
      message: sharedText.passwordNotMatch,
      path: ["confirmPassword"],
    });

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: {
      currentPassword: "",
      newPassword: "",
      confirmPassword: "",
    },
  });

  const formHandler = ({
    currentPassword,
    ...rest
  }: z.infer<typeof schema>) => {
    setIsLoading(true);

    const body = JSON.stringify({ password: currentPassword, ...rest });
    toast.promise(phpAction("/api/me/password", { body, method: "POST" }), {
      loading: messages.loading,
      success: (res) => {
        setIsLoading(false);
        form.reset();
        return res.message;
      },
      error: (e) => {
        setIsLoading(false);
        return e.message;
      },
    });
  };

  return (
    <Form form={form} onSubmit={formHandler}>
      <CardContent className="flex flex-col gap-y-4">
        <FormField
          control={form.control}
          name="currentPassword"
          render={({ field }) => (
            <TextFields
              type="password"
              field={field}
              {...userFields.currentPassword}
            />
          )}
        />

        <FormField
          control={form.control}
          name="newPassword"
          render={({ field }) => (
            <TextFields
              type="password"
              field={field}
              {...userFields.newPassword}
            />
          )}
        />

        <FormField
          control={form.control}
          name="confirmPassword"
          render={({ field }) => (
            <TextFields
              type="password"
              field={field}
              {...userFields.confirmPassword}
            />
          )}
        />
      </CardContent>

      <CardFooter className="flex-col items-stretch border-t md:flex-row md:items-center">
        <Button type="submit" disabled={isLoading}>
          <Loader loading={isLoading} icon={{ base: <Save /> }} />
          {actions.update}
        </Button>

        <ResetButton onClick={() => form.reset()} />
      </CardFooter>
    </Form>
  );
}

/*
 * --- ADMIN ---
 */

export function AdminCreateUserDialog() {
  const isMobile = useIsMobile();
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const schema = zodUser
    .pick({
      name: true,
      email: true,
      newPassword: true,
      confirmPassword: true,
      role: true,
    })
    .refine((sc) => sc.newPassword === sc.confirmPassword, {
      message: sharedText.passwordNotMatch,
      path: ["confirmPassword"],
    });

  const Icon = UserRoundPlus;

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: "",
      email: "",
      newPassword: "",
      confirmPassword: "",
      role: "user",
    },
  });

  const formHandler = ({ newPassword, ...rest }: z.infer<typeof schema>) => {
    setIsLoading(true);

    const body = JSON.stringify({ password: newPassword, ...rest });
    toast.promise(phpAction("/api/users", { body, method: "POST" }), {
      loading: messages.loading,
      success: (res) => {
        phpMutate("/api/users");
        setIsLoading(false);
        form.reset();
        return res.message;
      },
      error: (e) => {
        setIsLoading(false);
        return e.message;
      },
    });
  };

  return (
    <Dialog>
      <DialogTrigger asChild>
        <Button size={isMobile ? "default" : "sm"} className="w-full">
          <Icon /> Tambah Pengguna
        </Button>
      </DialogTrigger>

      <DialogContent>
        <DialogHeader>
          <DialogTitle>Tambah Pengguna</DialogTitle>
          <DialogDescription>
            Isi detail pengguna baru dengan lengkap. Pastikan semua bidang wajib
            diisi.
          </DialogDescription>
        </DialogHeader>

        <Form form={form} onSubmit={formHandler}>
          <FormField
            control={form.control}
            name="name"
            render={({ field }) => (
              <TextFields type="text" field={field} {...userFields.name} />
            )}
          />

          <FormField
            control={form.control}
            name="email"
            render={({ field }) => (
              <TextFields type="email" field={field} {...userFields.email} />
            )}
          />

          <FormField
            control={form.control}
            name="newPassword"
            render={({ field }) => (
              <TextFields
                type="password"
                field={field}
                {...userFields.newPassword}
              />
            )}
          />

          <FormField
            control={form.control}
            name="confirmPassword"
            render={({ field }) => (
              <TextFields
                type="password"
                field={field}
                {...userFields.confirmPassword}
              />
            )}
          />

          <FormField
            control={form.control}
            name="role"
            render={({ field: { value, onChange } }) => (
              <FormFieldWrapper label={userFields.role}>
                <RadioGroupField
                  defaultValue={value}
                  onValueChange={onChange}
                  className="grid grid-cols-2 gap-2"
                  data={allRoles.map((value) => {
                    const { displayName, ...rest } = rolesMeta[value];
                    return { value, label: displayName, ...rest };
                  })}
                  required
                />
              </FormFieldWrapper>
            )}
          />

          <Separator />

          <DialogFooter>
            <DialogClose>{actions.cancel}</DialogClose>
            <Button type="submit" disabled={isLoading}>
              <Loader loading={isLoading} icon={{ base: <Icon /> }} />
              {actions.add}
            </Button>
          </DialogFooter>
        </Form>
      </DialogContent>
    </Dialog>
  );
}

function AdminChangeUserRoleForm({
  data: { id, name, role },
  setIsOpen,
}: {
  data: User;
  setIsOpen: React.Dispatch<React.SetStateAction<boolean>>;
}) {
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const schema = zodUser.pick({ role: true });

  const form = useForm<z.infer<typeof schema>>({
    resolver: zodResolver(schema),
    defaultValues: { role: role === "user" ? "admin" : "user" },
  });

  const formHandler = ({ role: newRole }: z.infer<typeof schema>) => {
    if (newRole === role)
      return toast.info(messages.noChanges(`${name}'s role`));

    setIsLoading(true);

    const body = JSON.stringify({ role: newRole });
    toast.promise(phpAction(`/api/users/${id}`, { body, method: "POST" }), {
      loading: messages.loading,
      success: (res) => {
        phpMutate("/api/users");
        setIsLoading(false);
        setIsOpen(false);
        return res.message;
      },
      error: (e) => {
        setIsLoading(false);
        return e.message;
      },
    });
  };

  return (
    <Form form={form} onSubmit={formHandler}>
      <FormField
        control={form.control}
        name="role"
        render={({ field: { value, onChange } }) => (
          <FormFieldWrapper
            label={capitalize(`Ubah ${userFields.role}`, "first")}
          >
            <RadioGroupField
              defaultValue={value}
              onValueChange={onChange}
              className="grid grid-cols-2 gap-2"
              data={allRoles.map((value) => {
                const { displayName, ...rest } = rolesMeta[value];
                const disabled = value === role;
                return { value, label: displayName, disabled, ...rest };
              })}
              required
            />
          </FormFieldWrapper>
        )}
      />

      <Button type="submit" disabled={isLoading}>
        <Loader loading={isLoading} icon={{ base: <Save /> }} />
        {actions.update}
      </Button>
    </Form>
  );
}

function AdminRemoveUserDialog({
  data: { id, name },
  setIsOpen,
}: {
  data: Pick<User, "id" | "name">;
  setIsOpen: React.Dispatch<React.SetStateAction<boolean>>;
}) {
  const [input, setInput] = useState<string>("");
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const clickHandler = async () => {
    setIsLoading(true);

    toast.promise(phpAction(`/api/users/${id}`, { method: "DELETE" }), {
      loading: messages.loading,
      success: (res) => {
        phpMutate("/api/users");
        setIsLoading(false);
        setIsOpen(false);
        return res.message;
      },
      error: (e) => {
        setIsLoading(false);
        return e.message;
      },
    });
  };

  return (
    <AlertDialog>
      <AlertDialogTrigger asChild>
        <Button variant="outline_destructive" disabled={isLoading}>
          <Loader loading={isLoading} icon={{ base: <Trash2 /> }} />
          {`${actions.remove} ${name}`}
        </Button>
      </AlertDialogTrigger>

      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle className="text-destructive flex items-center gap-x-2">
            <TriangleAlert /> Hapus Akun {name}
          </AlertDialogTitle>
          <AlertDialogDescription>
            PERINGATAN: Tindakan ini akaPn menghapus akun {name} beserta seluruh
            datanya secara permanen. Harap berhati-hati karena aksi ini tidak
            dapat dibatalkan.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div className="grid gap-2">
          <Label>{messages.removeLabel(name)}</Label>
          <Input
            value={input}
            onChange={(e) => setInput(e.target.value)}
            placeholder={name}
          />
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel>{actions.cancel}</AlertDialogCancel>
          <AlertDialogAction
            className={buttonVariants({ variant: "destructive" })}
            onClick={clickHandler}
            disabled={input !== name}
          >
            {actions.confirm}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}

function AdminActionRemoveUsersDialog({
  data,
  onSuccess,
}: {
  data: Pick<User, "id" | "name" | "image">[];
  onSuccess: () => void;
}) {
  const [input, setInput] = useState<string>("");
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const clickHandler = async () => {
    setIsLoading(true);

    const body = JSON.stringify({ ids: data.map(({ id }) => id) });
    toast.promise(phpAction(`/api/users`, { body, method: "DELETE" }), {
      loading: messages.loading,
      success: (res) => {
        phpMutate("/api/users");
        setIsLoading(false);
        onSuccess();
        return res.message;
      },
      error: (e) => {
        setIsLoading(false);
        return e.message;
      },
    });
  };

  return (
    <AlertDialog>
      <AlertDialogTrigger asChild>
        <Button size="sm" variant="ghost_destructive" disabled={isLoading}>
          <Loader loading={isLoading} icon={{ base: <Trash2 /> }} />
          {actions.remove}
        </Button>
      </AlertDialogTrigger>

      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle className="text-destructive flex items-center gap-x-2">
            <TriangleAlert /> Hapus {data.length} Akun
          </AlertDialogTitle>
          <AlertDialogDescription>
            PERINGATAN: Tindakan ini akan menghapus {data.length} akun yang
            dipilih beserta seluruh datanya secara permanen. Harap berhati-hati
            karena aksi ini tidak dapat dibatalkan.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div className="grid gap-2">
          <Label>{messages.removeLabel(String(data.length))}</Label>
          <Input
            value={input}
            onChange={(e) => setInput(e.target.value)}
            placeholder={String(data.length)}
          />
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel>{actions.cancel}</AlertDialogCancel>

          <AlertDialogAction
            className={buttonVariants({ variant: "destructive" })}
            onClick={clickHandler}
          >
            {actions.confirm}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}
