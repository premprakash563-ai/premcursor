"use client";

import { useCallback, useState } from "react";
import { DataTable, PageHeader, StatusPill } from "@/components/ui";
import { useCampus } from "@/components/campus-store";
import { formatINR } from "@/lib/utils";
import { Toolbar, useQueryFilter } from "@/components/module-kit";

export default function PayrollPage() {
  const { payroll, markPayrollPaid } = useCampus();
  const [query, setQuery] = useState("");

  const getText = useCallback(
    (p: (typeof payroll)[0]) => `${p.name} ${p.role} ${p.month} ${p.status}`,
    [],
  );
  const filtered = useQueryFilter(payroll, query, getText);

  return (
    <div>
      <PageHeader
        eyebrow="Finance · HR"
        title="Payroll"
        description="Process salary payouts for teachers and staff."
      />
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search payroll records…"
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
          "Actions",
        ]}
        rows={filtered.map((p) => [
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
          p.status !== "paid" ? (
            <button
              key={`${p.id}-a`}
              type="button"
              className="text-xs font-semibold text-[var(--brand-primary)]"
              onClick={() => markPayrollPaid(p.id)}
            >
              Mark paid
            </button>
          ) : (
            "—"
          ),
        ])}
      />
    </div>
  );
}
