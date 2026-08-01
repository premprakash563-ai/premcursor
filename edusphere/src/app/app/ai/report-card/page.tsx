"use client";

import { useState } from "react";
import {
  Field,
  PageHeader,
  PrimaryButton,
  inputClass,
} from "@/components/ui";
import { generateReportCard, type StudentReportCard } from "@/lib/ai";
import { Sparkles } from "lucide-react";
import { useTenant } from "@/components/providers";
import { useCampus } from "@/components/campus-store";

export default function AIReportCardPage() {
  const { school } = useTenant();
  const { students } = useCampus();
  const [studentId, setStudentId] = useState(students[0]?.id ?? "");
  const [term, setTerm] = useState("Term 2 · 2025-26");
  const [loading, setLoading] = useState(false);
  const [card, setCard] = useState<StudentReportCard | null>(null);

  async function run() {
    const student = students.find((s) => s.id === studentId);
    if (!student) return;
    setLoading(true);
    setCard(null);
    const result = await generateReportCard({
      studentName: student.name,
      className: `${student.className}-${student.section}`,
      term,
    });
    setCard(result);
    setLoading(false);
  }

  return (
    <div>
      <PageHeader
        eyebrow="AI Studio"
        title="AI Student Report Card"
        description="Turn marks and attendance into a parent-ready narrative with strengths, focus areas, and teacher notes."
      />

      <div className="grid gap-6 lg:grid-cols-[320px_1fr]">
        <div className="h-fit space-y-4 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-5">
          <Field label="Student">
            <select
              className={inputClass}
              value={studentId}
              onChange={(e) => setStudentId(e.target.value)}
            >
              {students.map((s) => (
                <option key={s.id} value={s.id}>
                  {s.name} ({s.className}-{s.section})
                </option>
              ))}
            </select>
          </Field>
          <Field label="Term">
            <input
              className={inputClass}
              value={term}
              onChange={(e) => setTerm(e.target.value)}
            />
          </Field>
          <PrimaryButton className="w-full" onClick={run} disabled={loading}>
            <Sparkles className="h-4 w-4" />
            {loading ? "Generating…" : "Generate report card"}
          </PrimaryButton>
        </div>

        <div>
          {loading && (
            <div className="ai-loading rounded-3xl border border-[var(--line)] p-10 text-center text-[var(--muted)]">
              Campora AI is analysing performance patterns…
            </div>
          )}
          {!loading && !card && (
            <div className="rounded-3xl border border-dashed border-[var(--line)] bg-[var(--panel)] px-6 py-16 text-center text-[var(--muted)]">
              Select a student and generate an AI report card.
            </div>
          )}
          {card && (
            <article className="animate-rise overflow-hidden rounded-3xl border border-[var(--line)] bg-[var(--panel)]">
              <div
                className="px-6 py-5 text-white md:px-8"
                style={{ background: school.primaryColor }}
              >
                <div className="flex items-center justify-between gap-4">
                  <div>
                    <p className="text-xs uppercase tracking-[0.2em] opacity-80">
                      {school.name}
                    </p>
                    <h3 className="mt-1 font-display text-3xl">
                      {card.studentName}
                    </h3>
                    <p className="mt-1 text-sm opacity-90">
                      Class {card.className} · {card.term}
                    </p>
                  </div>
                  <div className="rounded-2xl bg-white/15 px-4 py-3 text-center backdrop-blur">
                    <p className="font-display text-3xl">{card.overallGrade}</p>
                    <p className="text-xs opacity-80">{card.percentage}%</p>
                  </div>
                </div>
              </div>
              <div className="space-y-6 p-6 md:p-8">
                <p className="leading-relaxed text-[var(--ink-soft)]">
                  {card.aiSummary}
                </p>
                <div className="overflow-x-auto rounded-2xl border border-[var(--line)]">
                  <table className="min-w-full text-sm">
                    <thead className="bg-[var(--surface)] text-xs uppercase tracking-wider text-[var(--muted)]">
                      <tr>
                        <th className="px-4 py-3 text-left">Subject</th>
                        <th className="px-4 py-3 text-left">Marks</th>
                        <th className="px-4 py-3 text-left">Grade</th>
                        <th className="px-4 py-3 text-left">Remark</th>
                      </tr>
                    </thead>
                    <tbody>
                      {card.subjects.map((s) => (
                        <tr key={s.name} className="border-t border-[var(--line)]">
                          <td className="px-4 py-3 font-medium">{s.name}</td>
                          <td className="px-4 py-3">
                            {s.marks}/{s.maxMarks}
                          </td>
                          <td className="px-4 py-3">{s.grade}</td>
                          <td className="px-4 py-3 text-[var(--muted)]">
                            {s.remark}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="rounded-2xl bg-[var(--surface)] p-4">
                    <p className="text-xs font-semibold uppercase tracking-wider text-[var(--brand-primary)]">
                      Strengths
                    </p>
                    <ul className="mt-2 list-disc space-y-1 pl-4 text-sm">
                      {card.strengths.map((s) => (
                        <li key={s}>{s}</li>
                      ))}
                    </ul>
                  </div>
                  <div className="rounded-2xl bg-[var(--surface)] p-4">
                    <p className="text-xs font-semibold uppercase tracking-wider text-amber-800">
                      Focus areas
                    </p>
                    <ul className="mt-2 list-disc space-y-1 pl-4 text-sm">
                      {card.improvements.map((s) => (
                        <li key={s}>{s}</li>
                      ))}
                    </ul>
                  </div>
                </div>
                <div className="flex flex-wrap gap-4 text-sm text-[var(--muted)]">
                  <span>Attendance: {card.attendancePct}%</span>
                  <span>Standing: {card.rankEstimate}</span>
                </div>
                <p className="rounded-2xl border border-[var(--line)] p-4 text-sm italic text-[var(--ink-soft)]">
                  Teacher note: {card.teacherNote}
                </p>
              </div>
            </article>
          )}
        </div>
      </div>
    </div>
  );
}
