"use client";

import { Suspense, useCallback, useState } from "react";
import { useSearchParams } from "next/navigation";
import {
  DataTable,
  PageHeader,
  StatusPill,
} from "@/components/ui";
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

function StudentsPageInner() {
  const { students, addStudent, updateStudent, removeStudent } = useCampus();
  const searchParams = useSearchParams();
  const [query, setQuery] = useState(searchParams.get("q") ?? "");
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({
    name: "",
    rollNo: "",
    className: "10",
    section: "A",
    gender: "M",
    parentName: "",
    phone: "",
    email: "",
  });

  const getText = useCallback(
    (s: (typeof students)[0]) =>
      `${s.name} ${s.rollNo} ${s.className}${s.section} ${s.parentName} ${s.email}`,
    [],
  );
  const filtered = useQueryFilter(students, query, getText);

  function submit() {
    if (!form.name.trim() || !form.rollNo.trim()) return;
    addStudent({
      name: form.name.trim(),
      rollNo: form.rollNo.trim(),
      className: form.className,
      section: form.section,
      gender: form.gender,
      parentName: form.parentName || "—",
      phone: form.phone || "—",
      email: form.email || `${form.rollNo.toLowerCase()}@student.edu`,
      feesStatus: "due",
      attendancePct: 100,
      status: "active",
    });
    setOpen(false);
    setForm({
      name: "",
      rollNo: "",
      className: "10",
      section: "A",
      gender: "M",
      parentName: "",
      phone: "",
      email: "",
    });
  }

  return (
    <div>
      <PageHeader
        eyebrow="People"
        title="Student management"
        description="Add, search, activate or archive students. Data saves in this browser."
      />
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search name, roll, class, parent…"
        actionLabel="Add student"
        onAction={() => setOpen(true)}
      />
      <DataTable
        columns={[
          "Name",
          "Roll",
          "Class",
          "Parent",
          "Attendance",
          "Fees",
          "Status",
          "Actions",
        ]}
        rows={filtered.map((s) => [
          <div key={`${s.id}-n`}>
            <p className="font-medium text-[var(--ink)]">{s.name}</p>
            <p className="text-xs text-[var(--muted)]">{s.email}</p>
          </div>,
          s.rollNo,
          `${s.className}-${s.section}`,
          s.parentName,
          `${s.attendancePct}%`,
          <StatusPill key={`${s.id}-f`} status={s.feesStatus} />,
          <StatusPill key={`${s.id}-s`} status={s.status} />,
          <div key={`${s.id}-a`} className="flex flex-wrap gap-2">
            <button
              type="button"
              className="text-xs font-semibold text-[var(--brand-primary)]"
              onClick={() =>
                updateStudent(s.id, {
                  status: s.status === "active" ? "inactive" : "active",
                })
              }
            >
              {s.status === "active" ? "Archive" : "Activate"}
            </button>
            <button
              type="button"
              className="text-xs font-semibold text-rose-600"
              onClick={() => removeStudent(s.id)}
            >
              Delete
            </button>
          </div>,
        ])}
      />

      <Modal
        open={open}
        title="Add student"
        onClose={() => setOpen(false)}
        footer={
          <>
            <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
            <PrimaryButton onClick={submit}>Save student</PrimaryButton>
          </>
        }
      >
        <Field label="Full name">
          <input
            className={inputClass}
            value={form.name}
            onChange={(e) => setForm({ ...form, name: e.target.value })}
          />
        </Field>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Roll no">
            <input
              className={inputClass}
              value={form.rollNo}
              onChange={(e) => setForm({ ...form, rollNo: e.target.value })}
            />
          </Field>
          <Field label="Gender">
            <select
              className={inputClass}
              value={form.gender}
              onChange={(e) => setForm({ ...form, gender: e.target.value })}
            >
              <option value="M">Male</option>
              <option value="F">Female</option>
              <option value="O">Other</option>
            </select>
          </Field>
        </div>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Class">
            <input
              className={inputClass}
              value={form.className}
              onChange={(e) => setForm({ ...form, className: e.target.value })}
            />
          </Field>
          <Field label="Section">
            <input
              className={inputClass}
              value={form.section}
              onChange={(e) => setForm({ ...form, section: e.target.value })}
            />
          </Field>
        </div>
        <Field label="Parent name">
          <input
            className={inputClass}
            value={form.parentName}
            onChange={(e) => setForm({ ...form, parentName: e.target.value })}
          />
        </Field>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Phone">
            <input
              className={inputClass}
              value={form.phone}
              onChange={(e) => setForm({ ...form, phone: e.target.value })}
            />
          </Field>
          <Field label="Email">
            <input
              className={inputClass}
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
          </Field>
        </div>
      </Modal>
    </div>
  );
}


export default function StudentsPage() {
  return (
    <Suspense fallback={<div className="text-sm text-[var(--muted)]">Loading students…</div>}>
      <StudentsPageInner />
    </Suspense>
  );
}
