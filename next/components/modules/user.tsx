import { appMeta } from "@/lib/meta";
import { Role, rolesMeta, User } from "@/lib/permission";
import { cn } from "@/lib/utils";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import { Badge } from "../ui/badge";
import { Tooltip, TooltipContent, TooltipTrigger } from "../ui/tooltip";

export function UserRoleBadge({
  role,
  className,
}: {
  role: Role;
  className?: string;
}) {
  const { displayName, desc, icon: Icon, color } = rolesMeta[role];
  return (
    <Tooltip>
      <TooltipTrigger className={className} asChild>
        <Badge
          variant="outline"
          style={
            {
              "--badge-color-light":
                typeof color === "string" ? color : color.light,
              "--badge-color-dark":
                typeof color === "string" ? color : color.dark,
            } as React.CSSProperties
          }
          className={cn(
            "border-[var(--badge-color-light)] capitalize text-[var(--badge-color-light)] dark:border-[var(--badge-color-dark)] dark:text-[var(--badge-color-dark)]",
          )}
        >
          <Icon /> {displayName ?? role}
        </Badge>
      </TooltipTrigger>
      <TooltipContent>{desc}</TooltipContent>
    </Tooltip>
  );
}

export function UserAvatar({
  image,
  name,
  className,
  classNames,
}: Pick<User, "image" | "name"> & {
  className?: string;
  classNames?: { image?: string; fallback?: string };
}) {
  return (
    <Avatar className={cn("rounded-xl", className)}>
      <AvatarImage
        src={image ? `${appMeta.php.host}${image}` : undefined}
        className={cn("rounded-xl", classNames?.image)}
      />
      <AvatarFallback className={cn("rounded-xl", classNames?.fallback)}>
        {name.slice(0, 2)}
      </AvatarFallback>
    </Avatar>
  );
}
