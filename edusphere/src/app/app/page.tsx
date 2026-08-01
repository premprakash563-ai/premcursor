"use client";

import Link from "next/link";
import { PageHeader, StatCard } from "@/components/ui";
import { useAuth, useTenant } from "@/components/providers";
import {
  ATTENDANCE,
  CLASS_PERFORMANCE,
  DASHBOARD_STATS,
  FEES,
  LEAVES,
} from "@/lib/data";
import { formatINR } from "@/lib/utils";
import { ArrowUpRight, Sparkles } from "lucide-react";

export default function DashboardPage() {
  const { user } = useAuth();
  const { school } = useTenant();

  return (
    <div>
      <PageHeader
        eyebrow="Campus overview"
        title={`Welcome, ${user?.name?.split(" ")[0] ?? "there"}`}
        description={`${school.name} · ${school.tagline}`}
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Students"
          value={DASHBOARD_STATS.students.toLocaleString()}
          hint="Active enrollments"
        />
        <StatCard
          label="Teachers"
          value={String(DASHBOARD_STATS.teachers)}
          hint={`${DASHBOARD_STATS.staff} staff members`}
        />
        <StatCard
          label="Today's attendance"
          value={`${DASHBOARD_STATS.attendanceToday}%`}
          hint="Across all classes"
        />
        <StatCard
          label="Fees collected"
          value={formatINR(DASHBOARD_STATS.feeCollected)}
          hint={`${formatINR(DASHBOARD_STATS.feePending)} pending`}
        />
      </div>

      <div className="mt-8 grid gap-6 lg:grid-cols-3">
        <div className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6 lg:col-span-2">
          <div className="flex items-center justify-between">
            <h3 className="font-display text-xl">Class performance</h3>
            <Link
              href="/app/exams"
              className="text-sm text-[var(--brand-primary)]"
            >
              View exams
            </Link>
          </div>
          <div className="mt-6 space-y-4">
            {CLASS_PERFORMANCE.map((c) => (
              <div key={c.className}>
                <div className="mb-1 flex justify-between text-sm">
                  <span>Class {c.className}</span>
                  <span className="font-medium">{c.avg}%</span>
                </div>
                <div className="h-2.5 overflow-hidden rounded-full bg-[var(--surface)]">
                  <div
                    className="h-full rounded-full transition-all"
                    style={{
                      width: `${c.avg}%`,
                      background: "var(--brand-primary)",
                    }}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="space-y-6">
          <Link
            href="/app/ai/report-card"
            className="block rounded-3xl border border-[var(--line)] bg-gradient-to-br from-[var(--brand-primary)] to-[#0a4f3d] p-6 text-white"
          >
            <Sparkles className="h-6 w-6" />
            <p className="mt-4 font-display text-2xl">AI Report Card</p>
            <p className="mt-2 text-sm text-white/80">
              Generate narrative report cards in seconds.
            </p>
            <span className="mt-4 inline-flex items-center gap-1 text-sm font-medium">
              Open studio <ArrowUpRight className="h-4 w-4" />
            </span>
          </Link>

          <div className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6">
            <h3 className="font-display text-xl">Pending leaves</h3>
            <ul className="mt-4 space-y-3">
              {LEAVES.filter((l) => l.status === "pending").map((l) => (
                <li
                  key={l.id}
                  className="flex items-start justify-between gap-2 text-sm"
                >
                  <div>
                    <p className="font-medium">{l.name}</p>
                    <p className="text-[var(--muted)]">
                      {l.type} · {l.from}
                    </p>
                  </div>
                  <span className="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-800">
                    pending
                  </span>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>

      <div className="mt-6 grid gap-6 md:grid-cols-2">
        <div className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6">
          <h3 className="font-display text-xl">Fee snapshot</h3>
          <ul className="mt-4 divide-y divide-[var(--line)]">
            {FEES.slice(0, 4).map((f) => (
              <li
                key={f.id}
                className="flex items-center justify-between py-3 text-sm"
              >
                <div>
                  <p className="font-medium">{f.studentName}</p>
                  <p className="text-[var(--muted)]">
                    {f.category} · {f.className}
                  </p>
                </div>
                <div className="text-right">
                  <p>{formatINR(f.amount - f.paid)}</p>
                  <p className="text-xs capitalize text-[var(--muted)]">
                    {f.status}
                  </p>
                </div>
              </li>
            ))}
          </ul>
        </div>
        <div className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6">
          <h3 className="font-display text-xl">Attendance watchlist</h3>
          <ul className="mt-4 divide-y divide-[var(--line)]">
            {[...ATTENDANCE]
              .sort((a, b) => a.percentage - b.percentage)
              .map((a) => (
                <li
                  key={a.id}
                  className="flex items-center justify-between py-3 text-sm"
                >
                  <div>
                    <p className="font-medium">{a.name}</p>
                    <p className="text-[var(--muted)]">{a.className}</p>
                  </div>
                  <p
                    className={
                      a.percentage < 80
                        ? "font-semibold text-rose-700"
                        : "font-medium"
                    }
                  >
                    {a.percentage}%
                  </p>
                </li>
              ))}
          </ul>
        </div>
      </div>
    </div>
  );
}
