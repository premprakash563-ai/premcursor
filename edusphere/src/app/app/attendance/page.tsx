"use client";

import { DataTable, PageHeader, StatCard } from "@/components/ui";
import { ATTENDANCE, DASHBOARD_STATS } from "@/lib/data";

export default function AttendancePage() {
  return (
    <div>
      <PageHeader
        eyebrow="Operations"
        title="Attendance"
        description="Daily presence, late marks, and early risk flags for low attendance."
      />
      <div className="mb-6 grid gap-4 sm:grid-cols-3">
        <StatCard
          label="Campus today"
          value={`${DASHBOARD_STATS.attendanceToday}%`}
        />
        <StatCard
          label="At risk (&lt;80%)"
          value={String(ATTENDANCE.filter((a) => a.percentage < 80).length)}
        />
        <StatCard label="Tracked students" value={String(ATTENDANCE.length)} />
      </div>
      <DataTable
        columns={["Student", "Class", "Present", "Absent", "Late", "%"]}
        rows={ATTENDANCE.map((a) => [
          a.name,
          a.className,
          a.present,
          a.absent,
          a.late,
          <span
            key={a.id}
            className={
              a.percentage < 80 ? "font-semibold text-rose-700" : "font-medium"
            }
          >
            {a.percentage}%
          </span>,
        ])}
      />
    </div>
  );
}
