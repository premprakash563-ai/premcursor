"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { GraduationCap, UserCog, Users, Wallet, ArrowRight } from "lucide-react";
import { useAuth, useTenant } from "@/components/providers";
import type { Role } from "@/lib/types";

const ROLES: {
  role: Role;
  title: string;
  blurb: string;
  icon: React.ComponentType<{ className?: string }>;
}[] = [
  {
    role: "admin",
    title: "School Admin",
    blurb: "Full campus control, branding, fees, payroll",
    icon: UserCog,
  },
  {
    role: "teacher",
    title: "Teacher",
    blurb: "Attendance, exams, AI papers & report cards",
    icon: Users,
  },
  {
    role: "student",
    title: "Student",
    blurb: "Fees, library, leave, practice MCQs",
    icon: GraduationCap,
  },
  {
    role: "staff",
    title: "Staff",
    blurb: "Accounts, library ops, payroll view",
    icon: Wallet,
  },
];

export default function LoginPage() {
  const { login } = useAuth();
  const { school } = useTenant();
  const router = useRouter();

  function enter(role: Role) {
    login(role);
    router.push("/app");
  }

  return (
    <div className="relative min-h-screen overflow-hidden bg-[var(--surface)]">
      <div className="hero-mesh absolute inset-y-0 left-0 hidden w-[42%] lg:block">
        <div className="grain absolute inset-0 opacity-40" />
        <div className="relative z-10 flex h-full flex-col justify-between p-10 text-white">
          <Link href="/" className="inline-flex items-center gap-3">
            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white font-display text-lg text-[var(--ink)]">
              C
            </div>
            <span className="font-display text-2xl">Campora</span>
          </Link>
          <div>
            <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
              White-label campus OS
            </p>
            <h2 className="mt-3 max-w-sm font-display text-4xl leading-tight">
              One portal. Every school&apos;s brand.
            </h2>
            <p className="mt-4 max-w-sm text-sm leading-relaxed text-white/60">
              Demo access is instant — pick a role and explore the full
              professional workspace.
            </p>
          </div>
          <p className="text-xs text-white/35">
            {school.subdomain}.campora.app · powered by Campora
          </p>
        </div>
      </div>

      <div className="relative z-10 mx-auto flex min-h-screen max-w-lg flex-col justify-center px-5 py-12 lg:ml-[42%] lg:max-w-none lg:px-16 xl:px-24">
        <Link href="/" className="mb-10 inline-flex items-center gap-3 lg:hidden">
          <div
            className="flex h-11 w-11 items-center justify-center rounded-xl text-sm font-bold text-white"
            style={{ background: school.primaryColor }}
          >
            {school.logoText}
          </div>
          <div>
            <p className="font-display text-xl">{school.name}</p>
            <p className="text-xs text-[var(--muted)]">Campora demo</p>
          </div>
        </Link>

        <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">
          Secure campus access
        </p>
        <h1 className="mt-2 font-display text-4xl tracking-tight md:text-5xl">
          Sign in
        </h1>
        <p className="mt-3 max-w-md text-sm leading-relaxed text-[var(--muted)]">
          Choose a role to enter{" "}
          <span className="font-medium text-[var(--ink)]">{school.name}</span>.
          No password required for this demo.
        </p>

        <div className="mt-10 grid gap-3 sm:grid-cols-2 lg:max-w-2xl">
          {ROLES.map((r) => (
            <button
              key={r.role}
              type="button"
              onClick={() => enter(r.role)}
              className="group rounded-2xl border border-[var(--line)] bg-[var(--panel)] p-5 text-left shadow-[var(--shadow-sm)] transition hover:-translate-y-0.5 hover:border-[var(--brand-primary)]/30 hover:shadow-[var(--shadow-md)]"
            >
              <div className="flex items-start justify-between gap-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--brand-primary-soft)] text-[var(--brand-primary)] transition group-hover:bg-[var(--brand-primary)] group-hover:text-white">
                  <r.icon className="h-5 w-5" />
                </div>
                <ArrowRight className="h-4 w-4 text-[var(--muted)] opacity-0 transition group-hover:translate-x-0.5 group-hover:opacity-100" />
              </div>
              <p className="mt-4 text-[15px] font-semibold tracking-tight">
                {r.title}
              </p>
              <p className="mt-1 text-sm leading-relaxed text-[var(--muted)]">
                {r.blurb}
              </p>
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}
