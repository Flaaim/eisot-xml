"use client";

import { forwardRef } from "react";
import { PatternFormat } from "react-number-format";

import { Input } from "@/components/ui/input";
import { normalizeSnils } from "@/lib/snils";
import { cn } from "@/lib/utils";

export interface SnilsInputProps {
  readonly id?: string;
  readonly value: string;
  readonly onChange: (value: string) => void;
  readonly onBlur?: () => void;
  readonly disabled?: boolean;
  readonly "aria-invalid"?: boolean;
  readonly placeholder?: string;
  readonly className?: string;
}

/**
 * Поле ввода СНИЛС с маской ЕИСОТ: 999-999-999 99.
 * В form state сохраняется форматированное значение для XSD/API.
 */
export const SnilsInput = forwardRef<HTMLInputElement, SnilsInputProps>(function SnilsInput(
  {
    id,
    value,
    onChange,
    onBlur,
    disabled,
    "aria-invalid": ariaInvalid,
    placeholder = "111-222-333 45",
    className,
  },
  ref
) {
  return (
    <PatternFormat
      format="###-###-### ##"
      value={normalizeSnils(value)}
      onValueChange={(values) => onChange(values.formattedValue)}
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
