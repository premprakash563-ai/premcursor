"use client";

import { DataTable, PageHeader, StatusPill } from "@/components/ui";
import { EXAMS } from "@/lib/data";
import Link from "next/link";

export default function ExamsPage() {
  return (
    <div>
      <PageHeader
        eyebrow="Academics"
        title="Exam management"
        description="Schedule assessments, track status, and jump into AI paper generation."
        action={
          <Link
            href="/app/ai/question-paper"
            className="rounded-full bg-[var(--brand-primary)] px-5 py-2.5 text-sm font-medium text-white"
          >
            AI Question Paper
          </Link>
        }
      />
      <DataTable
        columns={["Exam", "Class", "Subject", "Date", "Max marks", "Status"]}
        rows={EXAMS.map((e) => [
          e.name,
          e.className,
          e.subject,
          e.date,
          e.maxMarks,
          <StatusPill key={e.id} status={e.status} />,
        ])}
      />
    </div>
  );
}
