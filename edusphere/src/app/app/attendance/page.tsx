"use client";

import { useCallback, useState } from "react";
import { DataTable, PageHeader, StatCard } from "@/components/ui";
import { useCampus } from "@/components/campus-store";
import { Toolbar, useQueryFilter } from "@/components/module-kit";

export default function AttendancePage() {
  const { attendance, setAttendanceMark } = useCampus();
  const [query, setQuery] = useState("");

  const getText = useCallback(
    (a: (typeof attendance)[0]) => `${a.name} ${a.className}`,
    [],
  );
  const filtered = useQueryFilter(attendance, query, getText);
  const avg =
    attendance.length === 0
      ? 0
      : Math.round(
          attendance.reduce((s, a) => s + a.percentage, 0) / attendance.length,
        );
  const atRisk = attendance.filter((a) => a.percentage < 80).length;

  return (
    <div>
      <PageHeader
        eyebrow="Operations"
        title="Attendance"
        description="Mark present / absent / late. Percentages update instantly."
      />
      <div className="mb-6 grid gap-4 sm:grid-cols-3">
        <StatCard label="Average" value={`${avg}%`} />
        <StatCard label="At risk (<80%)" value={String(atRisk)} />
        <StatCard label="Tracked students" value={String(attendance.length)} />
      </div>
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search student or class…"
      />
      <DataTable
        columns={[
          "Student",
          "Class",
          "Present",
          "Absent",
          "Late",
          "%",
          "Mark today",
        ]}
        rows={filtered.map((a) => [
          a.name,
          a.className,
          a.present,
          a.absent,
          a.late,
          <span
            key={a.id}
            className={
              a.percentage < 80
                ? "font-semibold text-rose-600"
                : "font-semibold text-[var(--ink)]"
            }
          >
            {a.percentage}%
          </span>,
          <div key={`${a.id}-m`} className="flex flex-wrap gap-1.5">
            <button
              type="button"
              className="rounded-lg bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700"
              onClick={() => setAttendanceMark(a.id, "present")}
            >
              Present
            </button>
            <button
              type="button"
              className="rounded-lg bg-rose-50 px-2 py-1 text-[11px] font-semibold text-rose-700"
              onClick={() => setAttendanceMark(a.id, "absent")}
            >
              Absent
            </button>
            <button
              type="button"
              className="rounded-lg bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-800"
              onClick={() => setAttendanceMark(a.id, "late")}
            >
              Late
            </button>
          </div>,
        ])}
      />
    </div>
  );
}
