"use client";

import { DataTable, PageHeader, StatusPill } from "@/components/ui";
import { BOOKS, LIBRARY_ISSUES } from "@/lib/data";

export default function LibraryPage() {
  return (
    <div>
      <PageHeader
        eyebrow="Resources"
        title="Library"
        description="Catalogue, issue/return, and overdue tracking for the campus library."
      />
      <h3 className="mb-3 font-display text-xl">Catalogue</h3>
      <DataTable
        columns={["Title", "Author", "ISBN", "Category", "Copies", "Available"]}
        rows={BOOKS.map((b) => [
          b.title,
          b.author,
          b.isbn,
          b.category,
          b.copies,
          b.available,
        ])}
      />
      <h3 className="mb-3 mt-8 font-display text-xl">Issues</h3>
      <DataTable
        columns={["Book", "Borrower", "Issued", "Due", "Status"]}
        rows={LIBRARY_ISSUES.map((i) => [
          i.bookTitle,
          i.borrower,
          i.issueDate,
          i.dueDate,
          <StatusPill key={i.id} status={i.status} />,
        ])}
      />
    </div>
  );
}
