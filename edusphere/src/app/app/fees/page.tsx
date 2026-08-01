"use client";

import { useCallback, useMemo, useState } from "react";
import { DataTable, PageHeader, StatCard, StatusPill } from "@/components/ui";
import { useCampus } from "@/components/campus-store";
import { formatINR } from "@/lib/utils";
import {
  Field,
  Modal,
  PrimaryButton,
  SecondaryButton,
  Toolbar,
  inputClass,
  useQueryFilter,
} from "@/components/module-kit";

export default function FeesPage() {
  const { fees, students, addFee, markFeePaid } = useCampus();
  const [query, setQuery] = useState("");
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({
    studentName: "",
    className: "",
    category: "Tuition",
    amount: "40000",
    dueDate: new Date().toISOString().slice(0, 10),
  });

  const getText = useCallback(
    (f: (typeof fees)[0]) =>
      `${f.studentName} ${f.className} ${f.category} ${f.status}`,
    [],
  );
  const filtered = useQueryFilter(fees, query, getText);

  const collected = useMemo(
    () => fees.reduce((a, f) => a + f.paid, 0),
    [fees],
  );
  const pending = useMemo(
    () => fees.reduce((a, f) => a + Math.max(0, f.amount - f.paid), 0),
    [fees],
  );
  const overdue = fees.filter((f) => f.status === "overdue").length;

  function submit() {
    if (!form.studentName.trim()) return;
    const amount = Number(form.amount) || 0;
    addFee({
      studentName: form.studentName.trim(),
      className: form.className || "—",
      category: form.category,
      amount,
      paid: 0,
      dueDate: form.dueDate,
      status: "pending",
    });
    setOpen(false);
  }

  return (
    <div>
      <PageHeader
        eyebrow="Finance"
        title="Fees management"
        description="Create fee invoices, collect payments, and track overdue accounts."
      />
      <div className="mb-6 grid gap-4 sm:grid-cols-3">
        <StatCard label="Collected" value={formatINR(collected)} />
        <StatCard label="Pending" value={formatINR(pending)} />
        <StatCard label="Overdue accounts" value={String(overdue)} />
      </div>
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search student, category, status…"
        actionLabel="Create invoice"
        onAction={() => setOpen(true)}
      />
      <DataTable
        columns={[
          "Student",
          "Class",
          "Category",
          "Amount",
          "Paid",
          "Due",
          "Status",
          "Actions",
        ]}
        rows={filtered.map((f) => [
          f.studentName,
          f.className,
          f.category,
          formatINR(f.amount),
          formatINR(f.paid),
          f.dueDate,
          <StatusPill key={f.id} status={f.status} />,
          f.status !== "paid" ? (
            <button
              key={`${f.id}-p`}
              type="button"
              className="text-xs font-semibold text-[var(--brand-primary)]"
              onClick={() => markFeePaid(f.id)}
            >
              Mark paid
            </button>
          ) : (
            "—"
          ),
        ])}
      />

      <Modal
        open={open}
        title="Create fee invoice"
        onClose={() => setOpen(false)}
        footer={
          <>
            <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
            <PrimaryButton onClick={submit}>Create</PrimaryButton>
          </>
        }
      >
        <Field label="Student">
          <select
            className={inputClass}
            value={form.studentName}
            onChange={(e) => {
              const st = students.find((s) => s.name === e.target.value);
              setForm({
                ...form,
                studentName: e.target.value,
                className: st ? `${st.className}-${st.section}` : form.className,
              });
            }}
          >
            <option value="">Select student</option>
            {students.map((s) => (
              <option key={s.id} value={s.name}>
                {s.name} ({s.className}-{s.section})
              </option>
            ))}
          </select>
        </Field>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Category">
            <select
              className={inputClass}
              value={form.category}
              onChange={(e) => setForm({ ...form, category: e.target.value })}
            >
              <option>Tuition</option>
              <option>Transport</option>
              <option>Lab</option>
              <option>Exam</option>
              <option>Hostel</option>
            </select>
          </Field>
          <Field label="Amount">
            <input
              type="number"
              className={inputClass}
              value={form.amount}
              onChange={(e) => setForm({ ...form, amount: e.target.value })}
            />
          </Field>
        </div>
        <Field label="Due date">
          <input
            type="date"
            className={inputClass}
            value={form.dueDate}
            onChange={(e) => setForm({ ...form, dueDate: e.target.value })}
          />
        </Field>
      </Modal>
    </div>
  );
}
