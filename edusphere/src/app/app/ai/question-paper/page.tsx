"use client";

import { useMemo, useState } from "react";
import {
  Field,
  PageHeader,
  PrimaryButton,
  inputClass,
} from "@/components/ui";
import { generateQuestionPaper, type QuestionPaper } from "@/lib/ai";
import { SYLLABUS_TOPICS } from "@/lib/data";
import { FileQuestion } from "lucide-react";
import clsx from "clsx";

export default function AIQuestionPaperPage() {
  const subjects = Object.keys(SYLLABUS_TOPICS);
  const [subject, setSubject] = useState(subjects[0]);
  const [className, setClassName] = useState("10");
  const [duration, setDuration] = useState("3 hours");
  const [totalMarks, setTotalMarks] = useState(80);
  const [selected, setSelected] = useState<string[]>(
    SYLLABUS_TOPICS[subjects[0]].slice(0, 4),
  );
  const [loading, setLoading] = useState(false);
  const [paper, setPaper] = useState<QuestionPaper | null>(null);

  const topics = useMemo(() => SYLLABUS_TOPICS[subject] ?? [], [subject]);

  function toggleTopic(t: string) {
    setSelected((prev) =>
      prev.includes(t) ? prev.filter((x) => x !== t) : [...prev, t],
    );
  }

  async function run() {
    setLoading(true);
    setPaper(null);
    const result = await generateQuestionPaper({
      subject,
      className,
      topics: selected,
      totalMarks,
      duration,
    });
    setPaper(result);
    setLoading(false);
  }

  return (
    <div>
      <PageHeader
        eyebrow="AI Studio"
        title="Syllabus-based Question Paper"
        description="Teachers select syllabus topics — Campora drafts a board-style paper with sections and marks."
      />

      <div className="grid gap-6 lg:grid-cols-[360px_1fr]">
        <div className="h-fit space-y-4 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-5">
          <Field label="Subject">
            <select
              className={inputClass}
              value={subject}
              onChange={(e) => {
                setSubject(e.target.value);
                setSelected(SYLLABUS_TOPICS[e.target.value].slice(0, 4));
              }}
            >
              {subjects.map((s) => (
                <option key={s}>{s}</option>
              ))}
            </select>
          </Field>
          <div className="grid grid-cols-2 gap-3">
            <Field label="Class">
              <input
                className={inputClass}
                value={className}
                onChange={(e) => setClassName(e.target.value)}
              />
            </Field>
            <Field label="Total marks">
              <input
                type="number"
                className={inputClass}
                value={totalMarks}
                onChange={(e) => setTotalMarks(Number(e.target.value))}
              />
            </Field>
          </div>
          <Field label="Duration">
            <input
              className={inputClass}
              value={duration}
              onChange={(e) => setDuration(e.target.value)}
            />
          </Field>
          <div>
            <p className="mb-2 text-xs font-medium uppercase tracking-wider text-[var(--muted)]">
              Syllabus topics
            </p>
            <div className="flex flex-wrap gap-2">
              {topics.map((t) => (
                <button
                  key={t}
                  type="button"
                  onClick={() => toggleTopic(t)}
                  className={clsx(
                    "rounded-full px-3 py-1.5 text-xs transition",
                    selected.includes(t)
                      ? "bg-[var(--brand-primary)] text-white"
                      : "bg-[var(--surface)] text-[var(--ink-soft)]",
                  )}
                >
                  {t}
                </button>
              ))}
            </div>
          </div>
          <PrimaryButton
            className="w-full"
            onClick={run}
            disabled={loading || selected.length === 0}
          >
            <FileQuestion className="h-4 w-4" />
            {loading ? "Drafting paper…" : "Generate paper"}
          </PrimaryButton>
        </div>

        <div>
          {loading && (
            <div className="ai-loading rounded-3xl border border-[var(--line)] p-10 text-center text-[var(--muted)]">
              Building sections from selected syllabus topics…
            </div>
          )}
          {!loading && !paper && (
            <div className="rounded-3xl border border-dashed border-[var(--line)] bg-[var(--panel)] px-6 py-16 text-center text-[var(--muted)]">
              Pick topics and generate a question paper.
            </div>
          )}
          {paper && (
            <article className="animate-rise rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6 md:p-8">
              <header className="border-b border-[var(--line)] pb-5 text-center">
                <p className="text-xs uppercase tracking-[0.2em] text-[var(--muted)]">
                  Confidential · Examination Copy
                </p>
                <h3 className="mt-2 font-display text-3xl">{paper.title}</h3>
                <p className="mt-2 text-sm text-[var(--muted)]">
                  Class {paper.className} · {paper.subject} · {paper.duration} ·{" "}
                  {paper.totalMarks} marks
                </p>
              </header>
              <div className="mt-5">
                <p className="text-sm font-semibold">Instructions</p>
                <ol className="mt-2 list-decimal space-y-1 pl-5 text-sm text-[var(--ink-soft)]">
                  {paper.instructions.map((i) => (
                    <li key={i}>{i}</li>
                  ))}
                </ol>
              </div>
              <div className="mt-8 space-y-8">
                {paper.sections.map((sec) => (
                  <section key={sec.title}>
                    <h4 className="font-display text-xl">
                      {sec.title}{" "}
                      <span className="text-base text-[var(--muted)]">
                        ({sec.marks} marks)
                      </span>
                    </h4>
                    <ul className="mt-3 space-y-4">
                      {sec.questions.map((q) => (
                        <li key={q.text} className="text-sm leading-relaxed">
                          <span>{q.text}</span>
                          <span className="ml-2 text-[var(--muted)]">
                            [{q.marks}]
                          </span>
                        </li>
                      ))}
                    </ul>
                  </section>
                ))}
              </div>
            </article>
          )}
        </div>
      </div>
    </div>
  );
}
