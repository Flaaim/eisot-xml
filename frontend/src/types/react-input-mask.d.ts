declare module "react-input-mask" {
  import * as React from "react";

  export interface BeforeMaskedStateChangeStates {
    previousState: {
      value: string;
      selection: { start: number; end: number } | null;
    };
    currentState: {
      value: string;
      selection: { start: number; end: number } | null;
    };
    nextState: {
      value: string;
      selection: { start: number; end: number } | null;
    };
  }

  export interface InputState {
    value: string;
    selection: { start: number; end: number } | null;
  }

  export interface MaskOptions {
    mask: string | Array<string | RegExp>;
    maskChar?: string | null;
    formatChars?: Record<string, string>;
    alwaysShowMask?: boolean;
    beforeMaskedValueChange?: (
      states: BeforeMaskedStateChangeStates
    ) => InputState;
  }

  export interface InputMaskProps extends MaskOptions {
    value?: string;
    defaultValue?: string;
    disabled?: boolean;
    readOnly?: boolean;
    onChange?: (event: React.ChangeEvent<HTMLInputElement>) => void;
    onBlur?: (event: React.FocusEvent<HTMLInputElement>) => void;
    onFocus?: (event: React.FocusEvent<HTMLInputElement>) => void;
    children: (inputProps: React.InputHTMLAttributes<HTMLInputElement>) => React.ReactNode;
  }

  export default class InputMask extends React.Component<InputMaskProps> {}
}
