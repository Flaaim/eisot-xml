"use client";

import { forwardRef } from "react";
import { PatternFormat } from "react-number-format";

import { Input } from "@/components/ui/input";
import { normalizeInn } from "@/lib/inn";
import { cn } from "@/lib/utils";

export interface InnInputProps {
  readonly id?: string;
  readonly value: string;
  readonly onChange: (value: string) => void;
  readonly onBlur?: () => void;
  readonly disabled?: boolean;
  readonly "aria-invalid"?: boolean;
  readonly placeholder?: string;
  readonly className?: string;
}

/** Маска ИНН: только цифры, до 12 символов. */
export const InnInput = forwardRef<HTMLInputElement, InnInputProps>(function InnInput(
  {
    id,
    value,
    onChange,
    onBlur,
    disabled,
    "aria-invalid": ariaInvalid,
    placeholder = "7707083893",
    className,
  },
  ref,
) {
  return (
    <PatternFormat
      format="############"
      value={normalizeInn(value)}
      onValueChange={(values) => onChange(values.value)}
      getInputRef={ref}
      customInput={Input}
      id={id}
      disabled={disabled}
      onBlur={onBlur}
      inputMode="numeric"
      placeholder={placeholder}
      aria-invalid={ariaInvalid}
      className={cn(className)}
    />
  );
});
