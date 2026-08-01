"use client";

import { useMemo, useState } from "react";
import {
  Field,
  PageHeader,
  PrimaryButton,
  SecondaryButton,
  inputClass,
} from "@/components/ui";
import { generateMCQTest, type MCQ } from "@/lib/ai";
import { SYLLABUS_TOPICS } from "@/lib/data";
import { ListChecks } from "lucide-react";
import clsx from "clsx";

export default function AIMCQPage() {
  const subjects = Object.keys(SYLLABUS_TOPICS);
  const [subject, setSubject] = useState(subjects[0]);
  const [className, setClassName] = useState("10");
  const [count, setCount] = useState(5);
  const [difficulty, setDifficulty] = useState<
    "mixed" | "easy" | "medium" | "hard"
  >("mixed");
  const [selected, setSelected] = useState<string[]>(
    SYLLABUS_TOPICS[subjects[0]].slice(0, 3),
  );
  const [loading, setLoading] = useState(false);
  const [mcqs, setMcqs] = useState<MCQ[]>([]);
  const [answers, setAnswers] = useState<Record<number, string>>({});
  const [submitted, setSubmitted] = useState(false);

  const topics = useMemo(() => SYLLABUS_TOPICS[subject] ?? [], [subject]);

  function toggleTopic(t: string) {
    setSelected((prev) =>
      prev.includes(t) ? prev.filter((x) => x !== t) : [...prev, t],
    );
  }

  async function run() {
    setLoading(true);
    setMcqs([]);
    setAnswers({});
    setSubmitted(false);
    const result = await generateMCQTest({
      subject,
      className,
      topics: selected,
      count,
      difficulty,
    });
    setMcqs(result);
    setLoading(false);
  }

  const score = mcqs.filter((m) => answers[m.id] === m.answer).length;

  return (
    <div>
      <PageHeader
        eyebrow="AI Studio"
        title="AI MCQ Test Generator"
        description="Create topic-wise quizzes with difficulty mix — students can attempt instantly."
      />

      <div className="grid gap-6 lg:grid-cols-[340px_1fr]">
        <div className="h-fit space-y-4 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-5">
          <Field label="Subject">
            <select
              className={inputClass}
              value={subject}
              onChange={(e) => {
                setSubject(e.target.value);
                setSelected(SYLLABUS_TOPICS[e.target.value].slice(0, 3));
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
            <Field label="Questions">
              <input
                type="number"
                min={3}
                max={15}
                className={inputClass}
                value={count}
                onChange={(e) => setCount(Number(e.target.value))}
              />
            </Field>
          </div>
          <Field label="Difficulty">
            <select
              className={inputClass}
              value={difficulty}
              onChange={(e) =>
                setDifficulty(e.target.value as typeof difficulty)
              }
            >
              <option value="mixed">Mixed</option>
              <option value="easy">Easy</option>
              <option value="medium">Medium</option>
              <option value="hard">Hard</option>
            </select>
          </Field>
          <div>
            <p className="mb-2 text-xs font-medium uppercase tracking-wider text-[var(--muted)]">
              Topics
            </p>
            <div className="flex flex-wrap gap-2">
              {topics.map((t) => (
                <button
                  key={t}
                  type="button"
                  onClick={() => toggleTopic(t)}
                  className={clsx(
                    "rounded-full px-3 py-1.5 text-xs",
                    selected.includes(t)
                      ? "bg-[var(--brand-primary)] text-white"
                      : "bg-[var(--surface)]",
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
            <ListChecks className="h-4 w-4" />
            {loading ? "Generating…" : "Generate MCQs"}
          </PrimaryButton>
        </div>

        <div>
          {loading && (
            <div className="ai-loading rounded-3xl border border-[var(--line)] p-10 text-center text-[var(--muted)]">
              Crafting questions and distractors…
            </div>
          )}
          {!loading && mcqs.length === 0 && (
            <div className="rounded-3xl border border-dashed border-[var(--line)] bg-[var(--panel)] px-6 py-16 text-center text-[var(--muted)]">
              Generate an MCQ set to start a practice test.
            </div>
          )}
          {mcqs.length > 0 && (
            <div className="animate-rise space-y-4">
              {submitted && (
                <div className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] px-5 py-4">
                  <p className="font-display text-2xl">
                    Score: {score}/{mcqs.length}
                  </p>
                  <p className="text-sm text-[var(--muted)]">
                    Review explanations below.
                  </p>
                </div>
              )}
              {mcqs.map((m) => (
                <div
                  key={m.id}
                  className="rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-5"
                >
                  <div className="flex items-start justify-between gap-3">
                    <p className="font-medium">
                      {m.id}. {m.question}
                    </p>
                    <span className="shrink-0 rounded-full bg-[var(--surface)] px-2 py-0.5 text-xs capitalize">
                      {m.difficulty}
                    </span>
                  </div>
                  <div className="mt-3 space-y-2">
                    {m.options.map((opt) => {
                      const chosen = answers[m.id] === opt;
                      const correct = submitted && opt === m.answer;
                      const wrong = submitted && chosen && opt !== m.answer;
                      return (
                        <button
                          key={opt}
                          type="button"
                          disabled={submitted}
                          onClick={() =>
                            setAnswers((a) => ({ ...a, [m.id]: opt }))
                          }
                          className={clsx(
                            "block w-full rounded-2xl border px-4 py-2.5 text-left text-sm transition",
                            correct && "border-emerald-400 bg-emerald-50",
                            wrong && "border-rose-300 bg-rose-50",
                            !submitted &&
                              chosen &&
                              "border-[var(--brand-primary)] bg-[var(--brand-primary)]/5",
                            !submitted &&
                              !chosen &&
                              "border-[var(--line)] hover:bg-[var(--surface)]",
                          )}
                        >
                          {opt}
                        </button>
                      );
                    })}
                  </div>
                  {submitted && (
                    <p className="mt-3 text-sm text-[var(--muted)]">
                      {m.explanation}
                    </p>
                  )}
                </div>
              ))}
              <div className="flex gap-3">
                {!submitted ? (
                  <PrimaryButton
                    onClick={() => setSubmitted(true)}
                    disabled={Object.keys(answers).length < mcqs.length}
                  >
                    Submit test
                  </PrimaryButton>
                ) : (
                  <SecondaryButton
                    onClick={() => {
                      setAnswers({});
                      setSubmitted(false);
                    }}
                  >
                    Retry
                  </SecondaryButton>
                )}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
