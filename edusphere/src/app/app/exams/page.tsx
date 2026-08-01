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

export default function ExamsPage() {
  const { exams, addExam, updateExamStatus } = useCampus();
  const [query, setQuery] = useState("");
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({
    name: "",
    className: "10",
    subject: "Physics",
    date: new Date().toISOString().slice(0, 10),
    maxMarks: "40",
  });

  const getText = useCallback(
    (e: (typeof exams)[0]) =>
      `${e.name} ${e.className} ${e.subject} ${e.status}`,
    [],
  );
  const filtered = useQueryFilter(exams, query, getText);

  function submit() {
    if (!form.name.trim()) return;
    addExam({
      name: form.name.trim(),
      className: form.className,
      subject: form.subject,
      date: form.date,
      maxMarks: Number(form.maxMarks) || 0,
      status: "upcoming",
    });
    setOpen(false);
    setForm({
      name: "",
      className: "10",
      subject: "Physics",
      date: new Date().toISOString().slice(0, 10),
      maxMarks: "40",
    });
  }

  return (
    <div>
      <PageHeader
        eyebrow="Academics"
        title="Exam management"
        description="Schedule exams, update status, and jump into AI paper generation."
        action={
          <a
            href="/app/ai/question-paper"
            className="rounded-xl bg-[var(--brand-primary)] px-5 py-2.5 text-sm font-semibold text-white"
          >
            AI Question Paper
          </a>
        }
      />
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search exams, subject, class…"
        actionLabel="Schedule exam"
        onAction={() => setOpen(true)}
      />
      <DataTable
        columns={[
          "Exam",
          "Class",
          "Subject",
          "Date",
          "Max marks",
          "Status",
          "Actions",
        ]}
        rows={filtered.map((e) => [
          e.name,
          e.className,
          e.subject,
          e.date,
          e.maxMarks,
          <StatusPill key={e.id} status={e.status} />,
          <div key={`${e.id}-a`} className="flex flex-wrap gap-2">
            {e.status === "upcoming" && (
              <button
                type="button"
                className="text-xs font-semibold text-[var(--brand-primary)]"
                onClick={() => updateExamStatus(e.id, "ongoing")}
              >
                Start
              </button>
            )}
            {e.status === "ongoing" && (
              <button
                type="button"
                className="text-xs font-semibold text-[var(--brand-primary)]"
                onClick={() => updateExamStatus(e.id, "completed")}
              >
                Complete
              </button>
            )}
            {e.status === "completed" && "—"}
          </div>,
        ])}
      />

      <Modal
        open={open}
        title="Schedule exam"
        onClose={() => setOpen(false)}
        footer={
          <>
            <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
            <PrimaryButton onClick={submit}>Save exam</PrimaryButton>
          </>
        }
      >
        <Field label="Exam name">
          <input
            className={inputClass}
            value={form.name}
            onChange={(e) => setForm({ ...form, name: e.target.value })}
          />
        </Field>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Class">
            <input
              className={inputClass}
              value={form.className}
              onChange={(e) => setForm({ ...form, className: e.target.value })}
            />
          </Field>
          <Field label="Subject">
            <input
              className={inputClass}
              value={form.subject}
              onChange={(e) => setForm({ ...form, subject: e.target.value })}
            />
          </Field>
        </div>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Date">
            <input
              type="date"
              className={inputClass}
              value={form.date}
              onChange={(e) => setForm({ ...form, date: e.target.value })}
            />
          </Field>
          <Field label="Max marks">
            <input
              type="number"
              className={inputClass}
              value={form.maxMarks}
              onChange={(e) => setForm({ ...form, maxMarks: e.target.value })}
            />
          </Field>
        </div>
      </Modal>
    </div>
  );
}
