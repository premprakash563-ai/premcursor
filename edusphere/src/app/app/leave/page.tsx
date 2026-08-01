"use client";

import { DataTable, PageHeader, PrimaryButton, StatusPill } from "@/components/ui";
import { LEAVES } from "@/lib/data";
import { useState } from "react";

export default function LeavePage() {
  const [rows, setRows] = useState(LEAVES);

  function setStatus(id: string, status: "approved" | "rejected") {
    setRows((prev) => prev.map((r) => (r.id === id ? { ...r, status } : r)));
  }

  return (
    <div>
      <PageHeader
        eyebrow="HR & students"
        title="Leave management"
        description="Approve teacher, staff, and student leave with a single workflow."
      />
      <DataTable
        columns={["Name", "Role", "Type", "From", "To", "Reason", "Status", ""]}
        rows={rows.map((l) => [
          l.name,
          l.role,
          l.type,
          l.from,
          l.to,
          l.reason,
          <StatusPill key={`${l.id}-s`} status={l.status} />,
          l.status === "pending" ? (
            <div key={`${l.id}-a`} className="flex gap-2">
              <PrimaryButton
                className="!px-3 !py-1.5 !text-xs"
                onClick={() => setStatus(l.id, "approved")}
              >
                Approve
              </PrimaryButton>
              <button
                className="rounded-full border border-[var(--line)] px-3 py-1.5 text-xs"
                onClick={() => setStatus(l.id, "rejected")}
              >
                Reject
              </button>
            </div>
          ) : (
            "—"
          ),
        ])}
      />
    </div>
  );
}
