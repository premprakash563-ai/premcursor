"use client";

import { useCallback, useState } from "react";
import { DataTable, PageHeader } from "@/components/ui";
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

export default function StaffPage() {
  const { staff, addStaff, removeStaff } = useCampus();
  const [query, setQuery] = useState("");
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({
    name: "",
    employeeId: "",
    role: "",
    department: "",
    email: "",
    phone: "",
    salary: "35000",
  });

  const getText = useCallback(
    (s: (typeof staff)[0]) =>
      `${s.name} ${s.employeeId} ${s.role} ${s.department}`,
    [],
  );
  const filtered = useQueryFilter(staff, query, getText);

  function submit() {
    if (!form.name.trim() || !form.employeeId.trim()) return;
    addStaff({
      name: form.name.trim(),
      employeeId: form.employeeId.trim(),
      role: form.role || "Staff",
      department: form.department || "General",
      email: form.email || `${form.employeeId.toLowerCase()}@horizon.edu`,
      phone: form.phone || "—",
      salary: Number(form.salary) || 0,
    });
    setOpen(false);
    setForm({
      name: "",
      employeeId: "",
      role: "",
      department: "",
      email: "",
      phone: "",
      salary: "35000",
    });
  }

  return (
    <div>
      <PageHeader
        eyebrow="People"
        title="Staff management"
        description="Non-teaching workforce — accounts, library, transport, HR."
      />
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search staff, role, department…"
        actionLabel="Add staff"
        onAction={() => setOpen(true)}
      />
      <DataTable
        columns={["Name", "ID", "Role", "Department", "Phone", "Salary", ""]}
        rows={filtered.map((s) => [
          <div key={`${s.id}-n`}>
            <p className="font-medium text-[var(--ink)]">{s.name}</p>
            <p className="text-xs text-[var(--muted)]">{s.email}</p>
          </div>,
          s.employeeId,
          s.role,
          s.department,
          s.phone,
          formatINR(s.salary),
          <button
            key={`${s.id}-d`}
            type="button"
            className="text-xs font-semibold text-rose-600"
            onClick={() => removeStaff(s.id)}
          >
            Delete
          </button>,
        ])}
      />

      <Modal
        open={open}
        title="Add staff member"
        onClose={() => setOpen(false)}
        footer={
          <>
            <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
            <PrimaryButton onClick={submit}>Save staff</PrimaryButton>
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
          <Field label="Employee ID">
            <input
              className={inputClass}
              value={form.employeeId}
              onChange={(e) => setForm({ ...form, employeeId: e.target.value })}
            />
          </Field>
          <Field label="Salary">
            <input
              type="number"
              className={inputClass}
              value={form.salary}
              onChange={(e) => setForm({ ...form, salary: e.target.value })}
            />
          </Field>
        </div>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Role">
            <input
              className={inputClass}
              value={form.role}
              onChange={(e) => setForm({ ...form, role: e.target.value })}
            />
          </Field>
          <Field label="Department">
            <input
              className={inputClass}
              value={form.department}
              onChange={(e) => setForm({ ...form, department: e.target.value })}
            />
          </Field>
        </div>
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
