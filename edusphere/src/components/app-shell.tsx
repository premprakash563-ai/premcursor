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
  Bell,
  Search,
} from "lucide-react";
import { useMemo, useState } from "react";
import { useAuth, useTenant } from "./providers";
import { roleLabel } from "@/lib/utils";
import type { Role } from "@/lib/types";
import clsx from "clsx";

type NavItem = {
  href: string;
  label: string;
  icon: React.ComponentType<{ className?: string }>;
  roles: Role[];
};

type NavGroup = { title: string; items: NavItem[] };

const NAV_GROUPS: NavGroup[] = [
  {
    title: "Overview",
    items: [
      {
        href: "/app",
        label: "Dashboard",
        icon: LayoutDashboard,
        roles: ["admin", "teacher", "student", "staff"],
      },
    ],
  },
  {
    title: "People",
    items: [
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
    ],
  },
  {
    title: "Academics",
    items: [
      {
        href: "/app/attendance",
        label: "Attendance",
        icon: CalendarCheck,
        roles: ["admin", "teacher", "student"],
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
    ],
  },
  {
    title: "Finance & HR",
    items: [
      {
        href: "/app/fees",
        label: "Fees",
        icon: Receipt,
        roles: ["admin", "staff", "student"],
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
    ],
  },
  {
    title: "AI Studio",
    items: [
      {
        href: "/app/ai/report-card",
        label: "Report Cards",
        icon: Sparkles,
        roles: ["admin", "teacher"],
      },
      {
        href: "/app/ai/question-paper",
        label: "Question Papers",
        icon: FileQuestion,
        roles: ["admin", "teacher"],
      },
      {
        href: "/app/ai/mcq",
        label: "MCQ Generator",
        icon: ListChecks,
        roles: ["admin", "teacher", "student"],
      },
      {
        href: "/app/ai/insights",
        label: "Insights",
        icon: Brain,
        roles: ["admin", "teacher"],
      },
    ],
  },
  {
    title: "Settings",
    items: [
      {
        href: "/app/settings/branding",
        label: "Branding & URL",
        icon: Palette,
        roles: ["admin"],
      },
    ],
  },
];

export function AppShell({ children }: { children: React.ReactNode }) {
  const { user, logout } = useAuth();
  const { school } = useTenant();
  const pathname = usePathname();
  const router = useRouter();
  const [open, setOpen] = useState(false);

  const groups = useMemo(() => {
    if (!user) return [];
    return NAV_GROUPS.map((g) => ({
      ...g,
      items: g.items.filter((n) => n.roles.includes(user.role)),
    })).filter((g) => g.items.length > 0);
  }, [user]);

  if (!user) {
    return (
      <div className="flex min-h-screen flex-col items-center justify-center gap-4 bg-[var(--surface)] px-6">
        <div className="rounded-2xl border border-[var(--line)] bg-[var(--panel)] p-8 text-center shadow-[var(--shadow-md)]">
          <BookOpen className="mx-auto h-10 w-10 text-[var(--brand-primary)]" />
          <h1 className="mt-4 font-display text-2xl text-[var(--ink)]">
            Sign in to continue
          </h1>
          <Link
            href="/login"
            className="mt-5 inline-flex rounded-xl bg-[var(--brand-primary)] px-6 py-2.5 text-sm font-semibold text-white"
          >
            Go to login
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[var(--surface)] text-[var(--ink)]">
      <div className="flex min-h-screen">
        <aside
          className={clsx(
            "fixed inset-y-0 left-0 z-40 flex w-[280px] flex-col bg-[var(--sidebar)] text-white transition-transform duration-300 lg:static lg:translate-x-0",
            open ? "translate-x-0" : "-translate-x-full",
          )}
        >
          <div className="flex items-center justify-between gap-3 border-b border-white/10 px-5 py-5">
            <div className="flex min-w-0 items-center gap-3">
              <div
                className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-lg"
                style={{ background: school.primaryColor }}
              >
                {school.logoText}
              </div>
              <div className="min-w-0">
                <p className="truncate text-[15px] font-semibold tracking-tight">
                  {school.shortName}
                </p>
                <p className="truncate text-[11px] text-white/45">
                  {school.subdomain}.campora.app
                </p>
              </div>
            </div>
            <button
              className="rounded-lg p-1 text-white/70 lg:hidden"
              onClick={() => setOpen(false)}
              aria-label="Close menu"
              type="button"
            >
              <X className="h-5 w-5" />
            </button>
          </div>

          <nav className="nav-scroll flex-1 space-y-5 overflow-y-auto px-3 py-5">
            {groups.map((group) => (
              <div key={group.title}>
                <p className="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/35">
                  {group.title}
                </p>
                <div className="space-y-0.5">
                  {group.items.map((item) => {
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
                          "flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition",
                          active
                            ? "bg-white text-[var(--sidebar)] shadow-sm"
                            : "text-[var(--sidebar-text)] hover:bg-white/8 hover:text-white",
                        )}
                      >
                        <Icon
                          className={clsx(
                            "h-4 w-4 shrink-0",
                            active ? "opacity-100" : "opacity-70",
                          )}
                        />
                        {item.label}
                      </Link>
                    );
                  })}
                </div>
              </div>
            ))}
          </nav>

          <div className="border-t border-white/10 p-4">
            <div className="mb-3 rounded-xl bg-white/5 p-3 ring-1 ring-white/10">
              <p className="text-sm font-medium text-white">{user.name}</p>
              <p className="text-[11px] text-white/45">{roleLabel(user.role)}</p>
            </div>
            <button
              type="button"
              onClick={() => {
                logout();
                router.push("/login");
              }}
              className="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-[13px] text-white/55 transition hover:bg-white/8 hover:text-white"
            >
              <LogOut className="h-4 w-4" />
              Sign out
            </button>
          </div>
        </aside>

        {open && (
          <button
            className="fixed inset-0 z-30 bg-black/50 backdrop-blur-[2px] lg:hidden"
            onClick={() => setOpen(false)}
            aria-label="Close overlay"
            type="button"
          />
        )}

        <div className="flex min-w-0 flex-1 flex-col">
          <header className="sticky top-0 z-20 border-b border-[var(--line)] bg-[var(--panel)]/90 backdrop-blur-xl">
            <div className="flex items-center justify-between gap-4 px-4 py-3 md:px-8">
              <div className="flex min-w-0 items-center gap-3">
                <button
                  className="rounded-xl border border-[var(--line)] p-2 hover:bg-[var(--surface)] lg:hidden"
                  onClick={() => setOpen(true)}
                  aria-label="Open menu"
                  type="button"
                >
                  <Menu className="h-5 w-5" />
                </button>
                <div className="min-w-0">
                  <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--muted)]">
                    Campus OS
                  </p>
                  <h1 className="truncate text-[15px] font-semibold tracking-tight md:text-base">
                    {school.name}
                  </h1>
                </div>
              </div>

              <div className="flex items-center gap-2">
                <div className="relative hidden md:block">
                  <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--muted)]" />
                  <input
                    className="w-56 rounded-xl border border-[var(--line)] bg-[var(--surface)] py-2 pl-9 pr-3 text-sm outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-primary)]/15 lg:w-72"
                    placeholder="Search campus…"
                    readOnly
                  />
                </div>
                <button
                  type="button"
                  className="relative rounded-xl border border-[var(--line)] p-2.5 hover:bg-[var(--surface)]"
                  aria-label="Notifications"
                >
                  <Bell className="h-4 w-4 text-[var(--ink-soft)]" />
                  <span className="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-[var(--danger)]" />
                </button>
                <div
                  className="hidden h-9 w-9 items-center justify-center rounded-xl text-xs font-bold text-white sm:flex"
                  style={{ background: school.primaryColor }}
                >
                  {user.name
                    .split(" ")
                    .map((n) => n[0])
                    .slice(0, 2)
                    .join("")}
                </div>
              </div>
            </div>
          </header>
          <main className="flex-1 px-4 py-6 md:px-8 md:py-8">{children}</main>
        </div>
      </div>
    </div>
  );
}
