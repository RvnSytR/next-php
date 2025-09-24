"use client";

import { useSession } from "@/lib/hooks";
import { routesMeta } from "@/lib/routes";
import { cn, getActiveRoute, getMenuByRole, toKebabCase } from "@/lib/utils";
import { ChevronRight } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Collapsible as CollapsiblePrimitive } from "radix-ui";
import { ComponentProps, useEffect, useState } from "react";
import { UserAvatar } from "../modules/user";
import { LinkLoader } from "../ui/buttons";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "../ui/collapsible";
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuAction,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  useSidebar,
} from "../ui/sidebar";
import { Skeleton } from "../ui/skeleton";
import { SWRErrorFallback, SWRLoadingFallback } from "./section";

export function SCHeaderMenuButton() {
  const { data, error, isLoading } = useSession();

  if (isLoading) return <Skeleton className="h-12" />;
  if (error || !data?.data)
    return <SWRErrorFallback error={error} className="h-12" hideText />;

  const { email, name, image } = data.data;

  return (
    <SidebarMenuButton
      size="lg"
      className="group/head-button group-data-[collapsible=icon]:my-2 group-data-[collapsible=icon]:p-0"
      asChild
    >
      <Link href="/dashboard/profile">
        <UserAvatar
          name={name}
          image={image}
          className="rounded-md"
          classNames={{
            image: "rounded-md group-hover/head-button:scale-125",
            fallback: "rounded-md group-hover/head-button:scale-125",
          }}
        />

        <div className="grid break-all [&>span]:line-clamp-1">
          <span className="text-sm font-semibold">{name}</span>
          <span className="text-xs">{email}</span>
        </div>
      </Link>
    </SidebarMenuButton>
  );
}

function SCCollapsible({
  isActive,
  ...props
}: ComponentProps<typeof CollapsiblePrimitive.Root> & {
  isActive: boolean;
}) {
  const [isOpen, setIsOpen] = useState(isActive);
  useEffect(() => {
    if (isActive) setIsOpen(true);
  }, [isActive]);
  return <Collapsible open={isOpen} onOpenChange={setIsOpen} {...props} />;
}

export function SCSidebarContent() {
  const pathname = usePathname();
  const { data, error, isLoading } = useSession();
  const { isMobile, toggleSidebar } = useSidebar();

  if (isLoading) return <SWRLoadingFallback className="h-full" />;
  if (error || !data?.data)
    return <SWRErrorFallback error={error} className="m-2 h-full" />;

  const menu = getMenuByRole(data.data.role);
  return menu.map(({ section, content }, i) => (
    <SidebarGroup key={i}>
      <SidebarGroupLabel>{section}</SidebarGroupLabel>

      <SidebarMenu>
        {content.map(({ route, icon: Icon, disabled, subMenu }) => {
          const { displayName } = routesMeta[route];
          const isActive = route === getActiveRoute(pathname);

          if (disabled) {
            return (
              <SidebarMenuItem key={route}>
                <SidebarMenuButton disabled>
                  {Icon && <Icon />} {displayName}
                </SidebarMenuButton>
              </SidebarMenuItem>
            );
          }

          return (
            <SCCollapsible key={route} isActive={isActive} asChild>
              <SidebarMenuItem>
                <SidebarMenuButton
                  tooltip={displayName}
                  onClick={() => isMobile && toggleSidebar()}
                  isActive={isActive}
                  asChild
                >
                  <Link href={route}>
                    <LinkLoader icon={{ base: Icon && <Icon /> }} />
                    {displayName}
                  </Link>
                </SidebarMenuButton>

                {subMenu && (
                  <>
                    <CollapsibleTrigger asChild>
                      <SidebarMenuAction className="data-[state=open]:rotate-90">
                        <ChevronRight />
                      </SidebarMenuAction>
                    </CollapsibleTrigger>

                    <CollapsibleContent>
                      <SidebarMenuSub>
                        {subMenu.map(
                          ({ label, href, className, ...props }, idx) => (
                            <SidebarMenuSubItem key={idx}>
                              <SidebarMenuSubButton
                                className={cn(
                                  "flex justify-between",
                                  className,
                                )}
                                asChild
                                {...props}
                              >
                                <Link
                                  href={
                                    href ?? `${route}/#${toKebabCase(label)}`
                                  }
                                >
                                  {label} <LinkLoader />
                                </Link>
                              </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                          ),
                        )}
                      </SidebarMenuSub>
                    </CollapsibleContent>
                  </>
                )}
              </SidebarMenuItem>
            </SCCollapsible>
          );
        })}
      </SidebarMenu>
    </SidebarGroup>
  ));
}
