"use client";

import { cn } from "@/lib/utils";
import { CircleIcon } from "lucide-react";
import { RadioGroup as RadioGroupPrimitive } from "radix-ui";
import { ComponentProps } from "react";
import { FormControl, FormItem, FormLabel } from "./form";
import { IconOrText } from "./icons";

type RadioGroupProps = ComponentProps<typeof RadioGroupPrimitive.Root>;
type RadioGroupItemProps = ComponentProps<typeof RadioGroupPrimitive.Item>;

export function RadioGroup({ className, ...props }: RadioGroupProps) {
  return (
    <RadioGroupPrimitive.Root
      data-slot="radio-group"
      className={cn("flex gap-2", className)}
      {...props}
    />
  );
}

export function RadioGroupItem({ className, ...props }: RadioGroupItemProps) {
  return (
    <RadioGroupPrimitive.Item
      data-slot="radio-group-item"
      className={cn(
        "border-input text-primary focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 shadow-xs aspect-square size-4 shrink-0 rounded-full border outline-none transition-[color,box-shadow] focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50",
        className,
      )}
      {...props}
    >
      <RadioGroupPrimitive.Indicator
        data-slot="radio-group-indicator"
        className="relative flex items-center justify-center"
      >
        <CircleIcon className="fill-primary absolute left-1/2 top-1/2 size-2 -translate-x-1/2 -translate-y-1/2" />
      </RadioGroupPrimitive.Indicator>
    </RadioGroupPrimitive.Item>
  );
}

export function RadioGroupField({
  data,
  ...props
}: RadioGroupProps & {
  data: {
    value: string;
    label?: string;
    desc?: string;
    icon?: IconOrText;
    className?: boolean;
    disabled?: boolean;
  }[];
}) {
  return (
    <RadioGroup {...props}>
      {data.map(({ value, label, desc, icon: Icon, className, disabled }) => (
        <FormItem
          key={value}
          className={cn(
            "dark:bg-input/30 has-data-[state=checked]:border-primary border-input shadow-xs relative items-start rounded-md border p-4",
            disabled && "opacity-50",
            className,
          )}
        >
          <div className="flex w-full justify-between gap-x-2">
            <FormLabel className="flex items-center">
              {Icon && (typeof Icon === "string" ? Icon : <Icon />)}
              {label ?? value}
            </FormLabel>

            <FormControl>
              <RadioGroupItem
                value={value}
                className="after:absolute after:inset-0"
                disabled={disabled}
              />
            </FormControl>
          </div>

          {desc && (
            <small className="text-muted-foreground text-pretty text-xs">
              {desc}
            </small>
          )}
        </FormItem>
      ))}
    </RadioGroup>
  );
}
