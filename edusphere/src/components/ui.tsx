import clsx from "clsx";
import { statusColor } from "@/lib/utils";

export function PageHeader({
  eyebrow,
  title,
  description,
  action,
}: {
  eyebrow?: string;
  title: string;
  description?: string;
  action?: React.ReactNode;
}) {
  return (
    <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        {eyebrow && (
          <p className="mb-1 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">
            {eyebrow}
          </p>
        )}
        <h2 className="font-display text-3xl tracking-tight text-[var(--ink)] md:text-4xl">
          {title}
        </h2>
        {description && (
          <p className="mt-2 max-w-2xl text-[var(--muted)]">{description}</p>
        )}
      </div>
      {action}
    </div>
  );
}

export function StatCard({
  label,
  value,
  hint,
}: {
  label: string;
  value: string;
  hint?: string;
}) {
  return (
    <div className="relative overflow-hidden rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-5">
      <div
        className="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full opacity-20"
        style={{ background: "var(--brand-primary)" }}
      />
      <p className="text-xs font-medium uppercase tracking-wider text-[var(--muted)]">
        {label}
      </p>
      <p className="mt-2 font-display text-3xl text-[var(--ink)]">{value}</p>
      {hint && <p className="mt-1 text-sm text-[var(--muted)]">{hint}</p>}
    </div>
  );
}

export function StatusPill({ status }: { status: string }) {
  return (
    <span
      className={clsx(
        "inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset capitalize",
        statusColor(status),
      )}
    >
      {status}
    </span>
  );
}

export function DataTable({
  columns,
  rows,
}: {
  columns: string[];
  rows: React.ReactNode[][];
}) {
  return (
    <div className="overflow-hidden rounded-3xl border border-[var(--line)] bg-[var(--panel)]">
      <div className="overflow-x-auto">
        <table className="min-w-full text-left text-sm">
          <thead className="border-b border-[var(--line)] bg-[var(--surface)]/80 text-xs uppercase tracking-wider text-[var(--muted)]">
            <tr>
              {columns.map((c) => (
                <th key={c} className="px-4 py-3 font-medium md:px-5">
                  {c}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {rows.map((row, i) => (
              <tr
                key={i}
                className="border-b border-[var(--line)] last:border-0 hover:bg-[var(--surface)]/50"
              >
                {row.map((cell, j) => (
                  <td key={j} className="px-4 py-3.5 md:px-5">
                    {cell}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export function EmptyHint({ children }: { children: React.ReactNode }) {
  return (
    <div className="rounded-3xl border border-dashed border-[var(--line)] bg-[var(--panel)] px-6 py-12 text-center text-[var(--muted)]">
      {children}
    </div>
  );
}

export function PrimaryButton({
  children,
  className,
  ...props
}: React.ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      className={clsx(
        "inline-flex items-center justify-center gap-2 rounded-full bg-[var(--brand-primary)] px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50",
        className,
      )}
      {...props}
    >
      {children}
    </button>
  );
}

export function SecondaryButton({
  children,
  className,
  ...props
}: React.ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      className={clsx(
        "inline-flex items-center justify-center gap-2 rounded-full border border-[var(--line)] bg-[var(--panel)] px-5 py-2.5 text-sm font-medium text-[var(--ink)] transition hover:bg-[var(--surface)] disabled:opacity-50",
        className,
      )}
      {...props}
    >
      {children}
    </button>
  );
}

export function Field({
  label,
  children,
}: {
  label: string;
  children: React.ReactNode;
}) {
  return (
    <label className="block space-y-1.5">
      <span className="text-xs font-medium uppercase tracking-wider text-[var(--muted)]">
        {label}
      </span>
      {children}
    </label>
  );
}

export const inputClass =
  "w-full rounded-2xl border border-[var(--line)] bg-[var(--panel)] px-4 py-2.5 text-sm outline-none ring-[var(--brand-primary)] focus:ring-2";
