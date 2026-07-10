"use client";

import { createContext, useCallback, useContext, useState, useEffect, type ReactNode } from "react";

// ───────────────────────────────────────────────
// Types
// ───────────────────────────────────────────────

type ToastVariant = "success" | "error" | "info";

interface Toast {
  readonly id: string;
  readonly message: string;
  readonly variant: ToastVariant;
}

interface ToastContextValue {
  showToast: (message: string, variant?: ToastVariant) => void;
}

// ───────────────────────────────────────────────
// Context
// ───────────────────────────────────────────────

const ToastContext = createContext<ToastContextValue | null>(null);

/**
 * Хук для показа Toast-уведомлений.
 */
export function useToast(): ToastContextValue {
  const ctx = useContext(ToastContext);
  if (!ctx) {
    throw new Error("useToast must be used within <ToastProvider>");
  }
  return ctx;
}

// ───────────────────────────────────────────────
// Variant styles
// ───────────────────────────────────────────────

const VARIANT_STYLES: Record<ToastVariant, string> = {
  success: "border-emerald-500/30 bg-emerald-500/10 text-emerald-300 shadow-emerald-500/10",
  error: "border-red-500/30 bg-red-500/10 text-red-300 shadow-red-500/10",
  info: "border-indigo-500/30 bg-indigo-500/10 text-indigo-300 shadow-indigo-500/10",
};

const VARIANT_ICONS: Record<ToastVariant, ReactNode> = {
  success: (
    <svg className="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
      />
    </svg>
  ),
  error: (
    <svg className="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
      />
    </svg>
  ),
  info: (
    <svg className="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path
        strokeLinecap="round"
        strokeLinejoin="round"
        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"
      />
    </svg>
  ),
};

// ───────────────────────────────────────────────
// Individual Toast
// ───────────────────────────────────────────────

function ToastItem({ toast, onDismiss }: { toast: Toast; onDismiss: (id: string) => void }) {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    // Trigger enter animation
    requestAnimationFrame(() => {
      setIsVisible(true);
    });

    const timer = setTimeout(() => {
      setIsVisible(false);
      setTimeout(() => {
        onDismiss(toast.id);
      }, 300);
    }, 4000);

    return () => {
      clearTimeout(timer);
    };
  }, [toast.id, onDismiss]);

  return (
    <div
      role="alert"
      className={`
        flex items-center gap-3 rounded-xl border px-4 py-3
        text-sm font-medium shadow-lg backdrop-blur-xl
        transition-all duration-300 ease-out
        ${VARIANT_STYLES[toast.variant]}
        ${isVisible ? "translate-x-0 opacity-100" : "translate-x-8 opacity-0"}
      `}
    >
      {VARIANT_ICONS[toast.variant]}
      <span>{toast.message}</span>
      <button
        type="button"
        onClick={() => {
          onDismiss(toast.id);
        }}
        className="ml-auto rounded-lg p-1 opacity-60 transition-opacity hover:opacity-100"
        aria-label="Закрыть"
      >
        <svg
          className="size-4"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          strokeWidth={2}
        >
          <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  );
}

// ───────────────────────────────────────────────
// Provider
// ───────────────────────────────────────────────

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const showToast = useCallback((message: string, variant: ToastVariant = "info") => {
    const id = `${String(Date.now())}-${Math.random().toString(36).slice(2, 9)}`;
    setToasts((prev) => [...prev, { id, message, variant }]);
  }, []);

  const dismissToast = useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  return (
    <ToastContext.Provider value={{ showToast }}>
      {children}

      {/* Toast container — fixed bottom-right */}
      <div className="pointer-events-none fixed right-6 bottom-6 z-50 flex flex-col gap-3">
        {toasts.map((toast) => (
          <div key={toast.id} className="pointer-events-auto">
            <ToastItem toast={toast} onDismiss={dismissToast} />
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}
