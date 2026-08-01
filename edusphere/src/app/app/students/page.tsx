"use client";

import { DataTable, PageHeader, StatusPill } from "@/components/ui";
import { STUDENTS } from "@/lib/data";

export default function StudentsPage() {
  return (
    <div>
      <PageHeader
        eyebrow="People"
        title="Student management"
        description="Enrollment, fees status, attendance health — one roster for the whole campus."
      />
      <DataTable
        columns={[
          "Name",
          "Roll",
          "Class",
          "Parent",
          "Attendance",
          "Fees",
          "Status",
        ]}
        rows={STUDENTS.map((s) => [
          <div key={`${s.id}-n`}>
            <p className="font-medium">{s.name}</p>
            <p className="text-xs text-[var(--muted)]">{s.email}</p>
          </div>,
          s.rollNo,
          `${s.className}-${s.section}`,
          s.parentName,
          `${s.attendancePct}%`,
          <StatusPill key={`${s.id}-f`} status={s.feesStatus} />,
          <StatusPill key={`${s.id}-s`} status={s.status} />,
        ])}
      />
    </div>
  );
}
