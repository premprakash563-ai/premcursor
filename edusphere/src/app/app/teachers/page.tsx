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

export default function TeachersPage() {
  const { teachers, addTeacher, updateTeacher, removeTeacher } = useCampus();
  const [query, setQuery] = useState("");
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({
    name: "",
    employeeId: "",
    subjects: "",
    classes: "",
    email: "",
    phone: "",
  });

  const getText = useCallback(
    (t: (typeof teachers)[0]) =>
      `${t.name} ${t.employeeId} ${t.subjects.join(" ")} ${t.classes.join(" ")}`,
    [],
  );
  const filtered = useQueryFilter(teachers, query, getText);

  function submit() {
    if (!form.name.trim() || !form.employeeId.trim()) return;
    addTeacher({
      name: form.name.trim(),
      employeeId: form.employeeId.trim(),
      subjects: form.subjects
        .split(",")
        .map((s) => s.trim())
        .filter(Boolean),
      classes: form.classes
        .split(",")
        .map((s) => s.trim())
        .filter(Boolean),
      email: form.email || `${form.employeeId.toLowerCase()}@horizon.edu`,
      phone: form.phone || "—",
      status: "active",
    });
    setOpen(false);
    setForm({
      name: "",
      employeeId: "",
      subjects: "",
      classes: "",
      email: "",
      phone: "",
    });
  }

  return (
    <div>
      <PageHeader
        eyebrow="People"
        title="Teacher management"
        description="Maintain faculty records, subjects and leave status."
      />
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search teachers, subjects, classes…"
        actionLabel="Add teacher"
        onAction={() => setOpen(true)}
      />
      <DataTable
        columns={[
          "Name",
          "Employee ID",
          "Subjects",
          "Classes",
          "Contact",
          "Status",
          "Actions",
        ]}
        rows={filtered.map((t) => [
          <div key={`${t.id}-n`}>
            <p className="font-medium text-[var(--ink)]">{t.name}</p>
            <p className="text-xs text-[var(--muted)]">{t.email}</p>
          </div>,
          t.employeeId,
          t.subjects.join(", "),
          t.classes.join(", "),
          t.phone,
          <StatusPill key={`${t.id}-s`} status={t.status} />,
          <div key={`${t.id}-a`} className="flex flex-wrap gap-2">
            <button
              type="button"
              className="text-xs font-semibold text-[var(--brand-primary)]"
              onClick={() =>
                updateTeacher(t.id, {
                  status: t.status === "active" ? "on-leave" : "active",
                })
              }
            >
              {t.status === "active" ? "Mark leave" : "Set active"}
            </button>
            <button
              type="button"
              className="text-xs font-semibold text-rose-600"
              onClick={() => removeTeacher(t.id)}
            >
              Delete
            </button>
          </div>,
        ])}
      />

      <Modal
        open={open}
        title="Add teacher"
        onClose={() => setOpen(false)}
        footer={
          <>
            <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
            <PrimaryButton onClick={submit}>Save teacher</PrimaryButton>
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
        <Field label="Employee ID">
          <input
            className={inputClass}
            value={form.employeeId}
            onChange={(e) => setForm({ ...form, employeeId: e.target.value })}
          />
        </Field>
        <Field label="Subjects (comma separated)">
          <input
            className={inputClass}
            placeholder="Physics, Math"
            value={form.subjects}
            onChange={(e) => setForm({ ...form, subjects: e.target.value })}
          />
        </Field>
        <Field label="Classes (comma separated)">
          <input
            className={inputClass}
            placeholder="10-A, 11-B"
            value={form.classes}
            onChange={(e) => setForm({ ...form, classes: e.target.value })}
          />
        </Field>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Email">
            <input
              className={inputClass}
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
          </Field>
          <Field label="Phone">
            <input
              className={inputClass}
              value={form.phone}
              onChange={(e) => setForm({ ...form, phone: e.target.value })}
            />
          </Field>
        </div>
      </Modal>
    </div>
  );
}
