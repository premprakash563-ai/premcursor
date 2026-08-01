import type { Role } from "./types";

export const BRAND = {
  product: "Campora",
  tagline: "The white-label OS for schools & coaching institutes",
  url: "campora.app",
};

export function roleLabel(role: Role) {
  const map: Record<Role, string> = {
    superadmin: "Platform Admin",
    admin: "School Admin",
    teacher: "Teacher",
    student: "Student",
    staff: "Staff",
    parent: "Parent",
  };
  return map[role];
}

export function formatINR(n: number) {
  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0,
  }).format(n);
}

export function statusColor(status: string) {
  const s = status.toLowerCase();
  if (["paid", "approved", "active", "completed", "returned"].includes(s))
    return "bg-emerald-50 text-emerald-800 ring-emerald-200";
  if (["pending", "processing", "partial", "upcoming", "issued"].includes(s))
    return "bg-amber-50 text-amber-900 ring-amber-200";
  if (["overdue", "rejected", "due", "inactive", "on-leave"].includes(s))
    return "bg-rose-50 text-rose-800 ring-rose-200";
  if (["ongoing"].includes(s)) return "bg-sky-50 text-sky-800 ring-sky-200";
  return "bg-stone-50 text-stone-700 ring-stone-200";
}

export const AUTH_STORAGE_KEY = "campora_session";
export const TENANT_STORAGE_KEY = "campora_tenant";
