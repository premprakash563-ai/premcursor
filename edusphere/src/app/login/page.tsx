"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { GraduationCap, UserCog, Users, Wallet } from "lucide-react";
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
    <div className="relative min-h-screen overflow-hidden">
      <div className="hero-mesh absolute inset-0" />
      <div className="grain absolute inset-0 opacity-30" />
      <div className="relative z-10 mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-5 py-12 md:px-8">
        <Link href="/" className="mb-10 inline-flex items-center gap-3">
          <div
            className="flex h-12 w-12 items-center justify-center rounded-2xl text-sm font-bold text-white"
            style={{ background: school.primaryColor }}
          >
            {school.logoText}
          </div>
          <div>
            <p className="font-display text-2xl">{school.name}</p>
            <p className="text-sm text-[var(--muted)]">
              {school.subdomain}.campora.app · powered by Campora
            </p>
          </div>
        </Link>

        <h1 className="font-display text-4xl md:text-5xl">Sign in to campus</h1>
        <p className="mt-3 max-w-xl text-[var(--muted)]">
          Demo mode — pick a role to explore the full School & Coaching OS. No
          password needed.
        </p>

        <div className="mt-10 grid gap-4 sm:grid-cols-2">
          {ROLES.map((r) => (
            <button
              key={r.role}
              onClick={() => enter(r.role)}
              className="group rounded-3xl border border-[var(--line)] bg-[var(--panel)]/90 p-5 text-left transition hover:-translate-y-0.5 hover:shadow-lg"
            >
              <r.icon className="h-7 w-7 text-[var(--brand-primary)] transition group-hover:scale-110" />
              <p className="mt-4 font-display text-xl">{r.title}</p>
              <p className="mt-1 text-sm text-[var(--muted)]">{r.blurb}</p>
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}
