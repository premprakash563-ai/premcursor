"use client";

import { useCallback, useState } from "react";
import { DataTable, PageHeader, StatusPill } from "@/components/ui";
import { useCampus } from "@/components/campus-store";
import {
  Field,
  Modal,
  PrimaryButton,
  SecondaryButton,
  Toolbar,
  inputClass,
  useQueryFilter,
} from "@/components/module-kit";

export default function LibraryPage() {
  const {
    books,
    libraryIssues,
    students,
    addBook,
    issueBook,
    returnBook,
  } = useCampus();
  const [query, setQuery] = useState("");
  const [openBook, setOpenBook] = useState(false);
  const [openIssue, setOpenIssue] = useState(false);
  const [bookForm, setBookForm] = useState({
    title: "",
    author: "",
    isbn: "",
    category: "General",
    copies: "5",
  });
  const [issueForm, setIssueForm] = useState({
    bookId: "",
    borrower: "",
  });

  const getText = useCallback(
    (b: (typeof books)[0]) =>
      `${b.title} ${b.author} ${b.isbn} ${b.category}`,
    [],
  );
  const filteredBooks = useQueryFilter(books, query, getText);

  function submitBook() {
    if (!bookForm.title.trim()) return;
    const copies = Number(bookForm.copies) || 1;
    addBook({
      title: bookForm.title.trim(),
      author: bookForm.author || "Unknown",
      isbn: bookForm.isbn || "—",
      category: bookForm.category,
      copies,
      available: copies,
    });
    setOpenBook(false);
    setBookForm({
      title: "",
      author: "",
      isbn: "",
      category: "General",
      copies: "5",
    });
  }

  function submitIssue() {
    if (!issueForm.bookId || !issueForm.borrower) return;
    issueBook(issueForm.bookId, issueForm.borrower);
    setOpenIssue(false);
    setIssueForm({ bookId: "", borrower: "" });
  }

  return (
    <div>
      <PageHeader
        eyebrow="Resources"
        title="Library"
        description="Catalogue books, issue to students, and track returns."
        action={
          <div className="flex gap-2">
            <SecondaryButton onClick={() => setOpenIssue(true)}>
              Issue book
            </SecondaryButton>
            <PrimaryButton onClick={() => setOpenBook(true)}>
              Add book
            </PrimaryButton>
          </div>
        }
      />
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search catalogue…"
      />
      <h3 className="mb-3 text-sm font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">
        Catalogue
      </h3>
      <DataTable
        columns={["Title", "Author", "ISBN", "Category", "Copies", "Available"]}
        rows={filteredBooks.map((b) => [
          b.title,
          b.author,
          b.isbn,
          b.category,
          b.copies,
          b.available,
        ])}
      />
      <h3 className="mb-3 mt-8 text-sm font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">
        Issues
      </h3>
      <DataTable
        columns={["Book", "Borrower", "Issued", "Due", "Status", "Actions"]}
        rows={libraryIssues.map((i) => [
          i.bookTitle,
          i.borrower,
          i.issueDate,
          i.dueDate,
          <StatusPill key={i.id} status={i.status} />,
          i.status !== "returned" ? (
            <button
              key={`${i.id}-r`}
              type="button"
              className="text-xs font-semibold text-[var(--brand-primary)]"
              onClick={() => returnBook(i.id)}
            >
              Return
            </button>
          ) : (
            "—"
          ),
        ])}
      />

      <Modal
        open={openBook}
        title="Add book"
        onClose={() => setOpenBook(false)}
        footer={
          <>
            <SecondaryButton onClick={() => setOpenBook(false)}>
              Cancel
            </SecondaryButton>
            <PrimaryButton onClick={submitBook}>Save book</PrimaryButton>
          </>
        }
      >
        <Field label="Title">
          <input
            className={inputClass}
            value={bookForm.title}
            onChange={(e) => setBookForm({ ...bookForm, title: e.target.value })}
          />
        </Field>
        <Field label="Author">
          <input
            className={inputClass}
            value={bookForm.author}
            onChange={(e) =>
              setBookForm({ ...bookForm, author: e.target.value })
            }
          />
        </Field>
        <div className="grid grid-cols-2 gap-3">
          <Field label="ISBN">
            <input
              className={inputClass}
              value={bookForm.isbn}
              onChange={(e) =>
                setBookForm({ ...bookForm, isbn: e.target.value })
              }
            />
          </Field>
          <Field label="Copies">
            <input
              type="number"
              className={inputClass}
              value={bookForm.copies}
              onChange={(e) =>
                setBookForm({ ...bookForm, copies: e.target.value })
              }
            />
          </Field>
        </div>
        <Field label="Category">
          <input
            className={inputClass}
            value={bookForm.category}
            onChange={(e) =>
              setBookForm({ ...bookForm, category: e.target.value })
            }
          />
        </Field>
      </Modal>

      <Modal
        open={openIssue}
        title="Issue book"
        onClose={() => setOpenIssue(false)}
        footer={
          <>
            <SecondaryButton onClick={() => setOpenIssue(false)}>
              Cancel
            </SecondaryButton>
            <PrimaryButton onClick={submitIssue}>Issue</PrimaryButton>
          </>
        }
      >
        <Field label="Book">
          <select
            className={inputClass}
            value={issueForm.bookId}
            onChange={(e) =>
              setIssueForm({ ...issueForm, bookId: e.target.value })
            }
          >
            <option value="">Select book</option>
            {books
              .filter((b) => b.available > 0)
              .map((b) => (
                <option key={b.id} value={b.id}>
                  {b.title} ({b.available} left)
                </option>
              ))}
          </select>
        </Field>
        <Field label="Borrower">
          <select
            className={inputClass}
            value={issueForm.borrower}
            onChange={(e) =>
              setIssueForm({ ...issueForm, borrower: e.target.value })
            }
          >
            <option value="">Select student</option>
            {students.map((s) => (
              <option key={s.id} value={s.name}>
                {s.name}
              </option>
            ))}
          </select>
        </Field>
      </Modal>
    </div>
  );
}
