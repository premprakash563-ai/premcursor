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

export default function LeavePage() {
  const { leaves, setLeaveStatus, addLeave } = useCampus();
  const [query, setQuery] = useState("");
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({
    name: "",
    role: "Teacher",
    type: "Casual",
    from: new Date().toISOString().slice(0, 10),
    to: new Date().toISOString().slice(0, 10),
    reason: "",
  });

  const getText = useCallback(
    (l: (typeof leaves)[0]) =>
      `${l.name} ${l.role} ${l.type} ${l.reason} ${l.status}`,
    [],
  );
  const filtered = useQueryFilter(leaves, query, getText);

  function submit() {
    if (!form.name.trim()) return;
    addLeave({
      name: form.name.trim(),
      role: form.role,
      type: form.type,
      from: form.from,
      to: form.to,
      reason: form.reason || "—",
      status: "pending",
    });
    setOpen(false);
  }

  return (
    <div>
      <PageHeader
        eyebrow="HR & students"
        title="Leave management"
        description="Apply, approve or reject leave for teachers, staff and students."
      />
      <Toolbar
        query={query}
        onQuery={setQuery}
        placeholder="Search leave requests…"
        actionLabel="Apply leave"
        onAction={() => setOpen(true)}
      />
      <DataTable
        columns={["Name", "Role", "Type", "From", "To", "Reason", "Status", ""]}
        rows={filtered.map((l) => [
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
                onClick={() => setLeaveStatus(l.id, "approved")}
              >
                Approve
              </PrimaryButton>
              <button
                type="button"
                className="rounded-xl border border-[var(--line)] px-3 py-1.5 text-xs font-semibold"
                onClick={() => setLeaveStatus(l.id, "rejected")}
              >
                Reject
              </button>
            </div>
          ) : (
            "—"
          ),
        ])}
      />

      <Modal
        open={open}
        title="Apply for leave"
        onClose={() => setOpen(false)}
        footer={
          <>
            <SecondaryButton onClick={() => setOpen(false)}>Cancel</SecondaryButton>
            <PrimaryButton onClick={submit}>Submit</PrimaryButton>
          </>
        }
      >
        <Field label="Name">
          <input
            className={inputClass}
            value={form.name}
            onChange={(e) => setForm({ ...form, name: e.target.value })}
          />
        </Field>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Role">
            <select
              className={inputClass}
              value={form.role}
              onChange={(e) => setForm({ ...form, role: e.target.value })}
            >
              <option>Teacher</option>
              <option>Staff</option>
              <option>Student</option>
            </select>
          </Field>
          <Field label="Type">
            <select
              className={inputClass}
              value={form.type}
              onChange={(e) => setForm({ ...form, type: e.target.value })}
            >
              <option>Casual</option>
              <option>Sick</option>
              <option>Medical</option>
              <option>Earned</option>
            </select>
          </Field>
        </div>
        <div className="grid grid-cols-2 gap-3">
          <Field label="From">
            <input
              type="date"
              className={inputClass}
              value={form.from}
              onChange={(e) => setForm({ ...form, from: e.target.value })}
            />
          </Field>
          <Field label="To">
            <input
              type="date"
              className={inputClass}
              value={form.to}
              onChange={(e) => setForm({ ...form, to: e.target.value })}
            />
          </Field>
        </div>
        <Field label="Reason">
          <textarea
            className={inputClass}
            rows={3}
            value={form.reason}
            onChange={(e) => setForm({ ...form, reason: e.target.value })}
          />
        </Field>
      </Modal>
    </div>
  );
}
