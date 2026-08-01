"use client";

import Link from "next/link";
import { PageHeader, StatCard, SectionCard, StatusPill } from "@/components/ui";
import { useAuth, useTenant } from "@/components/providers";
import { useCampus } from "@/components/campus-store";
import { CLASS_PERFORMANCE } from "@/lib/data";
import { formatINR } from "@/lib/utils";
import { ArrowUpRight, Sparkles, TrendingUp } from "lucide-react";
import { useMemo } from "react";

export default function DashboardPage() {
  const { user } = useAuth();
  const { school } = useTenant();
  const { students, teachers, staff, fees, leaves, attendance } = useCampus();

  const collected = useMemo(
    () => fees.reduce((a, f) => a + f.paid, 0),
    [fees],
  );
  const pending = useMemo(
    () => fees.reduce((a, f) => a + Math.max(0, f.amount - f.paid), 0),
    [fees],
  );
  const avgAttendance = useMemo(() => {
    if (!attendance.length) return 0;
    return (
      Math.round(
        (attendance.reduce((s, a) => s + a.percentage, 0) / attendance.length) *
          10,
      ) / 10
    );
  }, [attendance]);

  return (
    <div>
      <PageHeader
        eyebrow="Campus overview"
        title={`Welcome back, ${user?.name?.split(" ")[0] ?? "there"}`}
        description={`${school.name} · live operational snapshot`}
        action={
          <Link
            href="/app/ai/report-card"
            className="inline-flex items-center gap-2 rounded-xl bg-[var(--brand-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-[var(--shadow-sm)]"
          >
            <Sparkles className="h-4 w-4" />
            AI Studio
          </Link>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Students"
          value={students.length.toLocaleString()}
          hint="Active roster"
        />
        <StatCard
          label="Teachers"
          value={String(teachers.length)}
          hint={`${staff.length} staff members`}
        />
        <StatCard
          label="Attendance avg"
          value={`${avgAttendance}%`}
          hint="Across tracked students"
        />
        <StatCard
          label="Fees collected"
          value={formatINR(collected)}
          hint={`${formatINR(pending)} pending`}
        />
      </div>

      <div className="mt-6 grid gap-5 lg:grid-cols-3">
        <SectionCard
          title="Class performance"
          action={
            <Link
              href="/app/exams"
              className="text-xs font-semibold text-[var(--brand-primary)]"
            >
              View exams
            </Link>
          }
        >
          <div className="space-y-4">
            {CLASS_PERFORMANCE.map((c) => (
              <div key={c.className}>
                <div className="mb-1.5 flex justify-between text-sm">
                  <span className="font-medium text-[var(--ink-soft)]">
                    Class {c.className}
                  </span>
                  <span className="tabular-nums font-semibold">{c.avg}%</span>
                </div>
                <div className="h-2 overflow-hidden rounded-full bg-[var(--surface)]">
                  <div
                    className="h-full rounded-full bg-gradient-to-r from-[var(--brand-primary)] to-[#1a8a93]"
                    style={{ width: `${c.avg}%` }}
                  />
                </div>
              </div>
            ))}
          </div>
        </SectionCard>

        <div className="space-y-5">
          <Link
            href="/app/ai/report-card"
            className="relative block overflow-hidden rounded-2xl bg-[var(--ink)] p-6 text-white shadow-[var(--shadow-md)]"
          >
            <div className="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-[var(--brand-primary)]/40 blur-2xl" />
            <Sparkles className="relative h-6 w-6 text-[var(--brand-accent)]" />
            <p className="relative mt-4 font-display text-2xl tracking-tight">
              AI Report Card
            </p>
            <p className="relative mt-2 text-sm leading-relaxed text-white/65">
              Generate polished parent-ready narratives from marks and
              attendance.
            </p>
            <span className="relative mt-5 inline-flex items-center gap-1 text-sm font-semibold">
              Open studio <ArrowUpRight className="h-4 w-4" />
            </span>
          </Link>

          <SectionCard title="Pending leaves">
            <ul className="space-y-3">
              {leaves.filter((l) => l.status === "pending").length === 0 && (
                <li className="text-sm text-[var(--muted)]">No pending leaves</li>
              )}
              {leaves
                .filter((l) => l.status === "pending")
                .slice(0, 4)
                .map((l) => (
                  <li
                    key={l.id}
                    className="flex items-start justify-between gap-2 rounded-xl bg-[var(--surface)] px-3 py-2.5"
                  >
                    <div>
                      <p className="text-sm font-medium">{l.name}</p>
                      <p className="text-xs text-[var(--muted)]">
                        {l.type} · {l.from}
                      </p>
                    </div>
                    <StatusPill status={l.status} />
                  </li>
                ))}
            </ul>
          </SectionCard>
        </div>

        <SectionCard
          title="Fee health"
          action={
            <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-[var(--success)]">
              <TrendingUp className="h-3.5 w-3.5" /> Live
            </span>
          }
        >
          <ul className="divide-y divide-[var(--line)]">
            {fees.slice(0, 5).map((f) => (
              <li
                key={f.id}
                className="flex items-center justify-between py-3 text-sm"
              >
                <div>
                  <p className="font-medium">{f.studentName}</p>
                  <p className="text-xs text-[var(--muted)]">
                    {f.category} · {f.className}
                  </p>
                </div>
                <div className="text-right">
                  <p className="font-semibold tabular-nums">
                    {formatINR(f.amount - f.paid)}
                  </p>
                  <div className="mt-1 flex justify-end">
                    <StatusPill status={f.status} />
                  </div>
                </div>
              </li>
            ))}
          </ul>
        </SectionCard>
      </div>

      <div className="mt-5">
        <SectionCard
          title="Attendance watchlist"
          action={
            <Link
              href="/app/attendance"
              className="text-xs font-semibold text-[var(--brand-primary)]"
            >
              Open attendance
            </Link>
          }
        >
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {[...attendance]
              .sort((a, b) => a.percentage - b.percentage)
              .map((a) => (
                <div
                  key={a.id}
                  className="rounded-xl border border-[var(--line)] bg-[var(--surface)] px-4 py-3"
                >
                  <p className="text-sm font-medium">{a.name}</p>
                  <p className="text-xs text-[var(--muted)]">{a.className}</p>
                  <p
                    className={`mt-2 font-display text-2xl tabular-nums ${
                      a.percentage < 80 ? "text-rose-600" : "text-[var(--ink)]"
                    }`}
                  >
                    {a.percentage}%
                  </p>
                </div>
              ))}
          </div>
        </SectionCard>
      </div>
    </div>
  );
}
