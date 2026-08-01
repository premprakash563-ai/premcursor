"use client";

import { DataTable, PageHeader, StatCard, StatusPill } from "@/components/ui";
import { FEES, DASHBOARD_STATS } from "@/lib/data";
import { formatINR } from "@/lib/utils";

export default function FeesPage() {
  return (
    <div>
      <PageHeader
        eyebrow="Finance"
        title="Fees management"
        description="Tuition, transport, lab — track collections, partials, and overdue reminders."
      />
      <div className="mb-6 grid gap-4 sm:grid-cols-3">
        <StatCard
          label="Collected"
          value={formatINR(DASHBOARD_STATS.feeCollected)}
        />
        <StatCard
          label="Pending"
          value={formatINR(DASHBOARD_STATS.feePending)}
        />
        <StatCard
          label="Overdue accounts"
          value={String(FEES.filter((f) => f.status === "overdue").length)}
        />
      </div>
      <DataTable
        columns={["Student", "Class", "Category", "Amount", "Paid", "Due", "Status"]}
        rows={FEES.map((f) => [
          f.studentName,
          f.className,
          f.category,
          formatINR(f.amount),
          formatINR(f.paid),
          f.dueDate,
          <StatusPill key={f.id} status={f.status} />,
        ])}
      />
    </div>
  );
}
