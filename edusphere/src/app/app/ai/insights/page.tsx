"use client";

import { useState } from "react";
import {
  Field,
  PageHeader,
  PrimaryButton,
  inputClass,
} from "@/components/ui";
import { generateLearningInsights } from "@/lib/ai";
import { STUDENTS } from "@/lib/data";
import { Brain } from "lucide-react";

export default function AIInsightsPage() {
  const [studentId, setStudentId] = useState(STUDENTS[0].id);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState<Awaited<
    ReturnType<typeof generateLearningInsights>
  > | null>(null);

  async function run() {
    const student = STUDENTS.find((s) => s.id === studentId)!;
    setLoading(true);
    setData(null);
    const result = await generateLearningInsights(student.name);
    setData(result);
    setLoading(false);
  }

  return (
    <div>
      <PageHeader
        eyebrow="AI Studio"
        title="Learning Insights"
        description="Predict risk, forecast performance, and suggest interventions — the edge most school ERPs miss."
      />

      <div className="mb-6 flex flex-wrap items-end gap-4 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-5">
        <div className="min-w-[220px] flex-1">
          <Field label="Student">
            <select
              className={inputClass}
              value={studentId}
              onChange={(e) => setStudentId(e.target.value)}
            >
              {STUDENTS.map((s) => (
                <option key={s.id} value={s.id}>
                  {s.name}
                </option>
              ))}
            </select>
          </Field>
        </div>
        <PrimaryButton onClick={run} disabled={loading}>
          <Brain className="h-4 w-4" />
          {loading ? "Analysing…" : "Run AI insights"}
        </PrimaryButton>
      </div>

      {loading && (
        <div className="ai-loading rounded-3xl border border-[var(--line)] p-10 text-center text-[var(--muted)]">
          Scanning attendance, fees, and exam signals…
        </div>
      )}

      {data && (
        <div className="animate-rise grid gap-6 md:grid-cols-3">
          <div className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6 md:col-span-1">
            <p className="text-xs uppercase tracking-wider text-[var(--muted)]">
              Risk score
            </p>
            <p className="mt-2 font-display text-5xl">{data.riskScore}</p>
            <p className="mt-1 text-sm text-emerald-700">{data.riskLabel}</p>
          </div>
          <div className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6 md:col-span-2">
            <p className="font-display text-xl">Predictions</p>
            <ul className="mt-3 list-disc space-y-2 pl-5 text-sm text-[var(--ink-soft)]">
              {data.predictions.map((p) => (
                <li key={p}>{p}</li>
              ))}
            </ul>
          </div>
          <div className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6 md:col-span-3">
            <p className="font-display text-xl">Recommended interventions</p>
            <ul className="mt-3 grid gap-3 sm:grid-cols-3">
              {data.interventions.map((i) => (
                <li
                  key={i}
                  className="rounded-2xl bg-[var(--surface)] px-4 py-3 text-sm"
                >
                  {i}
                </li>
              ))}
            </ul>
          </div>
        </div>
      )}
    </div>
  );
}
