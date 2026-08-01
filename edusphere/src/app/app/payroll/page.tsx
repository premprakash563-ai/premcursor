"use client";

import { DataTable, PageHeader, StatusPill } from "@/components/ui";
import { PAYROLL } from "@/lib/data";
import { formatINR } from "@/lib/utils";

export default function PayrollPage() {
  return (
    <div>
      <PageHeader
        eyebrow="Finance · HR"
        title="Payroll"
        description="Salary processing for teachers and staff with allowances and deductions."
      />
      <DataTable
        columns={[
          "Name",
          "Role",
          "Basic",
          "Allowances",
          "Deductions",
          "Net",
          "Month",
          "Status",
        ]}
        rows={PAYROLL.map((p) => [
          p.name,
          p.role,
          formatINR(p.basic),
          formatINR(p.allowances),
          formatINR(p.deductions),
          <span key={`${p.id}-n`} className="font-semibold">
            {formatINR(p.net)}
          </span>,
          p.month,
          <StatusPill key={p.id} status={p.status} />,
        ])}
      />
    </div>
  );
}
