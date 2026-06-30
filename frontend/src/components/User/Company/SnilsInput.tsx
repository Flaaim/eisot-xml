"use client";

import InputMask from "react-input-mask";
import { forwardRef } from "react";

import { Input } from "@/components/ui/input";
import { SNILS_INPUT_MASK } from "@/lib/snils";
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
 * UI-компонент без бизнес-логики — только форматирование ввода.
 */
export const SnilsInput = forwardRef<HTMLInputElement, SnilsInputProps>(
  function SnilsInput(
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
      <InputMask
        mask={SNILS_INPUT_MASK}
        maskChar={null}
        value={value}
        disabled={disabled}
        onChange={(e) => onChange(e.target.value)}
        onBlur={onBlur}
      >
        {(inputProps) => (
          <Input
            {...inputProps}
            ref={ref}
            id={id}
            inputMode="numeric"
            placeholder={placeholder}
            aria-invalid={ariaInvalid}
            className={cn(className)}
          />
        )}
      </InputMask>
    );
  }
);
