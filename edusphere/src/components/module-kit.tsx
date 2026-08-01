"use client";

import { useMemo, type ReactNode } from "react";
import { Plus, Search, X } from "lucide-react";
import {
  Field,
  PrimaryButton,
  SecondaryButton,
  inputClass,
} from "@/components/ui";

export function useQueryFilter<T>(
  items: T[],
  query: string,
  getText: (item: T) => string,
) {
  return useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return items;
    return items.filter((item) => getText(item).toLowerCase().includes(q));
  }, [items, query, getText]);
}

export function Toolbar({
  query,
  onQuery,
  placeholder,
  actionLabel,
  onAction,
}: {
  query: string;
  onQuery: (v: string) => void;
  placeholder: string;
  actionLabel?: string;
  onAction?: () => void;
}) {
  return (
    <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div className="relative w-full sm:max-w-sm">
        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--muted)]" />
        <input
          className={`${inputClass} pl-9`}
          value={query}
          onChange={(e) => onQuery(e.target.value)}
          placeholder={placeholder}
        />
      </div>
      {actionLabel && onAction ? (
        <PrimaryButton onClick={onAction}>
          <Plus className="h-4 w-4" />
          {actionLabel}
        </PrimaryButton>
      ) : null}
    </div>
  );
}

export function Modal({
  open,
  title,
  onClose,
  children,
  footer,
}: {
  open: boolean;
  title: string;
  onClose: () => void;
  children: ReactNode;
  footer?: ReactNode;
}) {
  if (!open) return null;
  return (
    <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/45 p-4 backdrop-blur-[2px] sm:items-center">
      <div
        className="absolute inset-0"
        onClick={onClose}
        aria-hidden
      />
      <div className="relative z-10 w-full max-w-lg overflow-hidden rounded-2xl border border-[var(--line)] bg-[var(--panel)] shadow-[var(--shadow-lg)]">
        <div className="flex items-center justify-between border-b border-[var(--line)] px-5 py-4">
          <h3 className="text-base font-semibold tracking-tight">{title}</h3>
          <button
            type="button"
            onClick={onClose}
            className="rounded-lg p-1.5 hover:bg-[var(--surface)]"
            aria-label="Close"
          >
            <X className="h-4 w-4" />
          </button>
        </div>
        <div className="max-h-[70vh] space-y-4 overflow-y-auto px-5 py-4">
          {children}
        </div>
        {footer ? (
          <div className="flex justify-end gap-2 border-t border-[var(--line)] px-5 py-4">
            {footer}
          </div>
        ) : null}
      </div>
    </div>
  );
}

export { Field, PrimaryButton, SecondaryButton, inputClass };
