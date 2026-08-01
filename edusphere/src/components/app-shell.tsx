"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import {
  BookOpen,
  Brain,
  CalendarCheck,
  ClipboardList,
  GraduationCap,
  LayoutDashboard,
  Library,
  LogOut,
  Menu,
  Palette,
  Receipt,
  Sparkles,
  Users,
  UserCog,
  Wallet,
  X,
  FileQuestion,
  ListChecks,
  PlaneTakeoff,
} from "lucide-react";
import { useMemo, useState } from "react";
import { useAuth, useTenant } from "./providers";
import { roleLabel } from "@/lib/utils";
import type { Role } from "@/lib/types";
import clsx from "clsx";

const NAV: {
  href: string;
  label: string;
  icon: React.ComponentType<{ className?: string }>;
  roles: Role[];
}[] = [
  {
    href: "/app",
    label: "Dashboard",
    icon: LayoutDashboard,
    roles: ["admin", "teacher", "student", "staff"],
  },
  {
    href: "/app/students",
    label: "Students",
    icon: GraduationCap,
    roles: ["admin", "teacher", "staff"],
  },
  {
    href: "/app/teachers",
    label: "Teachers",
    icon: Users,
    roles: ["admin"],
  },
  {
    href: "/app/staff",
    label: "Staff",
    icon: UserCog,
    roles: ["admin"],
  },
  {
    href: "/app/attendance",
    label: "Attendance",
    icon: CalendarCheck,
    roles: ["admin", "teacher", "student"],
  },
  {
    href: "/app/fees",
    label: "Fees",
    icon: Receipt,
    roles: ["admin", "staff", "student"],
  },
  {
    href: "/app/exams",
    label: "Exams",
    icon: ClipboardList,
    roles: ["admin", "teacher", "student"],
  },
  {
    href: "/app/library",
    label: "Library",
    icon: Library,
    roles: ["admin", "teacher", "student", "staff"],
  },
  {
    href: "/app/leave",
    label: "Leave",
    icon: PlaneTakeoff,
    roles: ["admin", "teacher", "staff", "student"],
  },
  {
    href: "/app/payroll",
    label: "Payroll",
    icon: Wallet,
    roles: ["admin", "staff"],
  },
  {
    href: "/app/ai/report-card",
    label: "AI Report Card",
    icon: Sparkles,
    roles: ["admin", "teacher"],
  },
  {
    href: "/app/ai/question-paper",
    label: "AI Question Paper",
    icon: FileQuestion,
    roles: ["admin", "teacher"],
  },
  {
    href: "/app/ai/mcq",
    label: "AI MCQ Generator",
    icon: ListChecks,
    roles: ["admin", "teacher", "student"],
  },
  {
    href: "/app/ai/insights",
    label: "AI Insights",
    icon: Brain,
    roles: ["admin", "teacher"],
  },
  {
    href: "/app/settings/branding",
    label: "Branding & URL",
    icon: Palette,
    roles: ["admin"],
  },
];

export function AppShell({ children }: { children: React.ReactNode }) {
  const { user, logout } = useAuth();
  const { school } = useTenant();
  const pathname = usePathname();
  const router = useRouter();
  const [open, setOpen] = useState(false);

  const items = useMemo(() => {
    if (!user) return [];
    return NAV.filter((n) => n.roles.includes(user.role));
  }, [user]);

  if (!user) {
    return (
      <div className="flex min-h-screen flex-col items-center justify-center gap-4 bg-[var(--surface)] px-6">
        <BookOpen className="h-10 w-10 text-[var(--brand-primary)]" />
        <h1 className="font-display text-2xl text-[var(--ink)]">
          Sign in to continue
        </h1>
        <Link
          href="/login"
          className="rounded-full bg-[var(--brand-primary)] px-6 py-2.5 text-sm font-medium text-white"
        >
          Go to login
        </Link>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[var(--surface)] text-[var(--ink)]">
      <div className="flex min-h-screen">
        <aside
          className={clsx(
            "fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-[var(--line)] bg-[var(--panel)] transition-transform lg:static lg:translate-x-0",
            open ? "translate-x-0" : "-translate-x-full",
          )}
        >
          <div className="flex items-center justify-between gap-3 border-b border-[var(--line)] px-5 py-5">
            <div className="flex items-center gap-3">
              <div
                className="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-bold text-white shadow-sm"
                style={{ background: school.primaryColor }}
              >
                {school.logoText}
              </div>
              <div className="min-w-0">
                <p className="truncate font-display text-lg leading-tight">
                  {school.shortName}
                </p>
                <p className="truncate text-xs text-[var(--muted)]">
                  {school.subdomain}.campora.app
                </p>
              </div>
            </div>
            <button
              className="rounded-lg p-1 lg:hidden"
              onClick={() => setOpen(false)}
              aria-label="Close menu"
            >
              <X className="h-5 w-5" />
            </button>
          </div>

          <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            {items.map((item) => {
              const active =
                item.href === "/app"
                  ? pathname === "/app"
                  : pathname.startsWith(item.href);
              const Icon = item.icon;
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  onClick={() => setOpen(false)}
                  className={clsx(
                    "flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition",
                    active
                      ? "bg-[var(--brand-primary)] text-white shadow-sm"
                      : "text-[var(--ink-soft)] hover:bg-[var(--surface)]",
                  )}
                >
                  <Icon className="h-4 w-4 shrink-0 opacity-90" />
                  {item.label}
                </Link>
              );
            })}
          </nav>

          <div className="border-t border-[var(--line)] p-4">
            <div className="mb-3 rounded-2xl bg-[var(--surface)] p-3">
              <p className="text-sm font-medium">{user.name}</p>
              <p className="text-xs text-[var(--muted)]">
                {roleLabel(user.role)}
              </p>
            </div>
            <button
              onClick={() => {
                logout();
                router.push("/login");
              }}
              className="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-[var(--muted)] hover:bg-[var(--surface)] hover:text-[var(--ink)]"
            >
              <LogOut className="h-4 w-4" />
              Sign out
            </button>
          </div>
        </aside>

        {open && (
          <button
            className="fixed inset-0 z-30 bg-black/30 lg:hidden"
            onClick={() => setOpen(false)}
            aria-label="Close overlay"
          />
        )}

        <div className="flex min-w-0 flex-1 flex-col">
          <header className="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-[var(--line)] bg-[var(--panel)]/90 px-4 py-3 backdrop-blur md:px-8">
            <div className="flex items-center gap-3">
              <button
                className="rounded-lg p-2 hover:bg-[var(--surface)] lg:hidden"
                onClick={() => setOpen(true)}
                aria-label="Open menu"
              >
                <Menu className="h-5 w-5" />
              </button>
              <div>
                <p className="text-xs uppercase tracking-[0.18em] text-[var(--muted)]">
                  Powered by Campora
                </p>
                <h1 className="font-display text-lg md:text-xl">
                  {school.name}
                </h1>
              </div>
            </div>
            <div className="hidden items-center gap-2 sm:flex">
              <span
                className="rounded-full px-3 py-1 text-xs font-medium text-white"
                style={{ background: school.accentColor, color: "#1a1a1a" }}
              >
                Live campus
              </span>
            </div>
          </header>
          <main className="flex-1 px-4 py-6 md:px-8 md:py-8">{children}</main>
        </div>
      </div>
    </div>
  );
}
