"use client";

import { DataTable, PageHeader, StatusPill } from "@/components/ui";
import { TEACHERS } from "@/lib/data";

export default function TeachersPage() {
  return (
    <div>
      <PageHeader
        eyebrow="People"
        title="Teacher management"
        description="Subjects, classes, and employment status across your faculty."
      />
      <DataTable
        columns={["Name", "Employee ID", "Subjects", "Classes", "Contact", "Status"]}
        rows={TEACHERS.map((t) => [
          <div key={`${t.id}-n`}>
            <p className="font-medium">{t.name}</p>
            <p className="text-xs text-[var(--muted)]">{t.email}</p>
          </div>,
          t.employeeId,
          t.subjects.join(", "),
          t.classes.join(", "),
          t.phone,
          <StatusPill key={`${t.id}-s`} status={t.status} />,
        ])}
      />
    </div>
  );
}
