"use client";

import { DataTable, PageHeader } from "@/components/ui";
import { STAFF } from "@/lib/data";
import { formatINR } from "@/lib/utils";

export default function StaffPage() {
  return (
    <div>
      <PageHeader
        eyebrow="People"
        title="Staff management"
        description="Non-teaching workforce — accounts, library, transport, HR and more."
      />
      <DataTable
        columns={["Name", "ID", "Role", "Department", "Phone", "Salary"]}
        rows={STAFF.map((s) => [
          <div key={`${s.id}-n`}>
            <p className="font-medium">{s.name}</p>
            <p className="text-xs text-[var(--muted)]">{s.email}</p>
          </div>,
          s.employeeId,
          s.role,
          s.department,
          s.phone,
          formatINR(s.salary),
        ])}
      />
    </div>
  );
}
