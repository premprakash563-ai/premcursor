import Link from "next/link";
import {
  Brain,
  CheckCircle2,
  Globe2,
  Palette,
  Sparkles,
  ArrowRight,
  ShieldCheck,
  Building2,
} from "lucide-react";

const MODULES = [
  "Students",
  "Teachers",
  "Staff",
  "Attendance",
  "Fees",
  "Exams",
  "Library",
  "Leave",
  "Payroll",
  "AI Studio",
];

const AI_FEATURES = [
  {
    title: "AI Report Cards",
    text: "Parent-ready narratives with strengths, focus areas, and term trajectory — generated in seconds.",
    icon: Sparkles,
  },
  {
    title: "Syllabus Question Papers",
    text: "Select topics from the syllabus and draft board-style papers with clean mark distribution.",
    icon: Brain,
  },
  {
    title: "MCQ & Practice Tests",
    text: "Topic-wise quizzes with difficulty mix, instant scoring, and explanations for revision.",
    icon: CheckCircle2,
  },
];

export default function HomePage() {
  return (
    <div className="min-h-screen text-[var(--ink)]">
      <div className="hero-mesh relative min-h-[100svh] overflow-hidden text-white">
        <div className="grain pointer-events-none absolute inset-0 opacity-50" />
        <div className="pointer-events-none absolute -left-24 top-24 h-72 w-72 rounded-full bg-[var(--brand-accent)]/20 blur-3xl" />
        <div className="pointer-events-none absolute -right-16 bottom-10 h-80 w-80 rounded-full bg-teal-300/10 blur-3xl" />

        <header className="relative z-10 mx-auto flex max-w-6xl items-center justify-between px-5 py-6 md:px-8">
          <div className="flex items-center gap-3">
            <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white font-display text-lg text-[var(--ink)]">
              C
            </div>
            <div>
              <p className="font-display text-2xl leading-none tracking-tight">
                Campora
              </p>
              <p className="text-[11px] uppercase tracking-[0.18em] text-white/50">
                School & Coaching OS
              </p>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <Link
              href="/login"
              className="hidden text-sm font-medium text-white/75 transition hover:text-white sm:inline"
            >
              Sign in
            </Link>
            <Link
              href="/login"
              className="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-[var(--ink)] transition hover:bg-white/90"
            >
              Open demo <ArrowRight className="h-4 w-4" />
            </Link>
          </div>
        </header>

        <section className="relative z-10 mx-auto flex max-w-6xl flex-col justify-center px-5 pb-24 pt-16 md:px-8 md:pb-28 md:pt-24">
          <p className="animate-rise mb-5 inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/70 backdrop-blur">
            <span className="h-1.5 w-1.5 rounded-full bg-[var(--brand-accent)]" />
            White-label · Multi-tenant · AI-native
          </p>
          <h1 className="animate-rise-delay max-w-3xl font-display text-5xl leading-[1.02] tracking-tight md:text-7xl">
            Campora
          </h1>
          <p className="animate-rise-delay mt-6 max-w-xl text-lg leading-relaxed text-white/70 md:text-xl">
            The campus operating system schools can brand as their own — with AI
            that writes report cards and builds syllabus-perfect exams.
          </p>
          <div className="animate-rise-delay-2 mt-10 flex flex-wrap gap-3">
            <Link
              href="/login"
              className="rounded-xl bg-white px-6 py-3.5 text-sm font-semibold text-[var(--ink)] shadow-[var(--shadow-lg)] transition hover:bg-white/90"
            >
              Try live demo
            </Link>
            <a
              href="#branding"
              className="rounded-xl border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10"
            >
              See white-label
            </a>
          </div>
        </section>
      </div>

      <section className="hero-mesh-light border-b border-[var(--line)]">
        <div className="mx-auto max-w-6xl px-5 py-16 md:px-8">
          <div className="grid gap-6 md:grid-cols-3">
            {[
              {
                icon: Building2,
                title: "Full campus stack",
                text: "People, academics, fees, library, leave and payroll in one calm workspace.",
              },
              {
                icon: Sparkles,
                title: "AI teachers trust",
                text: "Report cards, papers and MCQs grounded in your syllabus — not generic fluff.",
              },
              {
                icon: ShieldCheck,
                title: "Their brand, not yours",
                text: "Every school runs a branded portal with logo, colors and custom URL.",
              },
            ].map((item) => (
              <div
                key={item.title}
                className="rounded-2xl border border-[var(--line)] bg-[var(--panel)] p-6 shadow-[var(--shadow-sm)]"
              >
                <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--brand-primary-soft)] text-[var(--brand-primary)]">
                  <item.icon className="h-5 w-5" />
                </div>
                <h3 className="mt-4 text-lg font-semibold tracking-tight">
                  {item.title}
                </h3>
                <p className="mt-2 text-sm leading-relaxed text-[var(--muted)]">
                  {item.text}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-5 py-16 md:px-8">
        <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">
          Modules
        </p>
        <h2 className="mt-2 font-display text-3xl tracking-tight md:text-4xl">
          Everything a campus runs on
        </h2>
        <div className="mt-8 flex flex-wrap gap-2">
          {MODULES.map((m) => (
            <span
              key={m}
              className="rounded-xl border border-[var(--line)] bg-[var(--panel)] px-4 py-2 text-sm font-medium text-[var(--ink-soft)] shadow-[var(--shadow-sm)]"
            >
              {m}
            </span>
          ))}
        </div>
      </section>

      <section className="border-y border-[var(--line)] bg-[var(--panel)] py-16">
        <div className="mx-auto max-w-6xl px-5 md:px-8">
          <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">
            Differentiator
          </p>
          <h2 className="mt-2 font-display text-3xl tracking-tight md:text-4xl">
            AI Studio built for real classrooms
          </h2>
          <div className="mt-10 grid gap-5 md:grid-cols-3">
            {AI_FEATURES.map((f) => (
              <div
                key={f.title}
                className="rounded-2xl border border-[var(--line)] bg-[var(--surface)] p-6 transition hover:-translate-y-0.5 hover:shadow-[var(--shadow-md)]"
              >
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--brand-primary)] text-white">
                  <f.icon className="h-5 w-5" />
                </div>
                <h3 className="mt-4 text-lg font-semibold">{f.title}</h3>
                <p className="mt-2 text-sm leading-relaxed text-[var(--muted)]">
                  {f.text}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id="branding" className="mx-auto max-w-6xl px-5 py-16 md:px-8">
        <div className="grid items-center gap-10 md:grid-cols-2">
          <div>
            <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">
              Sell to any school
            </p>
            <h2 className="mt-2 font-display text-3xl tracking-tight md:text-4xl">
              Their logo. Their colors. Their URL.
            </h2>
            <p className="mt-4 text-[15px] leading-relaxed text-[var(--ink-soft)]">
              Each school gets an isolated, branded portal — subdomain like{" "}
              <code className="rounded-md bg-[var(--surface)] px-1.5 py-0.5 text-[13px]">
                dps.campora.app
              </code>{" "}
              or a custom domain such as{" "}
              <code className="rounded-md bg-[var(--surface)] px-1.5 py-0.5 text-[13px]">
                portal.school.edu
              </code>
              .
            </p>
            <ul className="mt-6 space-y-3 text-sm">
              {[
                "Per-school tenant isolation",
                "Logo + color theme applied live",
                "Subdomain & custom domain ready",
                "Parents see the school brand, not yours",
              ].map((item) => (
                <li key={item} className="flex items-start gap-2.5">
                  <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-[var(--brand-primary)]" />
                  <span className="text-[var(--ink-soft)]">{item}</span>
                </li>
              ))}
            </ul>
          </div>
          <div className="space-y-4">
            <div className="flex items-start gap-4 rounded-2xl border border-[var(--line)] bg-[var(--panel)] p-5 shadow-[var(--shadow-sm)]">
              <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--brand-primary-soft)]">
                <Palette className="h-5 w-5 text-[var(--brand-primary)]" />
              </div>
              <div>
                <p className="font-semibold">Brand kit</p>
                <p className="mt-1 text-sm leading-relaxed text-[var(--muted)]">
                  Logo mark, school name, tagline, primary & accent colors —
                  editable by school admin.
                </p>
              </div>
            </div>
            <div className="flex items-start gap-4 rounded-2xl border border-[var(--line)] bg-[var(--panel)] p-5 shadow-[var(--shadow-sm)]">
              <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--brand-primary-soft)]">
                <Globe2 className="h-5 w-5 text-[var(--brand-primary)]" />
              </div>
              <div>
                <p className="font-semibold">URL control</p>
                <p className="mt-1 text-sm leading-relaxed text-[var(--muted)]">
                  Free subdomain on campora.app, or map DNS CNAME to a custom
                  domain.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-5 pb-20 md:px-8">
        <div className="overflow-hidden rounded-[1.5rem] bg-[var(--ink)] px-8 py-12 text-white md:px-12">
          <div className="flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
            <div>
              <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
                Next.js 16 · React 19
              </p>
              <h2 className="mt-2 font-display text-3xl tracking-tight md:text-4xl">
                Ready for a professional campus demo
              </h2>
              <p className="mt-3 max-w-xl text-sm leading-relaxed text-white/65 md:text-[15px]">
                Role-based dashboards, white-label branding, and an AI studio —
                built to sell school-by-school.
              </p>
            </div>
            <Link
              href="/login"
              className="inline-flex items-center gap-2 self-start rounded-xl bg-white px-6 py-3 text-sm font-semibold text-[var(--ink)]"
            >
              Launch demo <ArrowRight className="h-4 w-4" />
            </Link>
          </div>
        </div>
      </section>

      <footer className="border-t border-[var(--line)] px-5 py-8 text-center text-sm text-[var(--muted)] md:px-8">
        Campora · Advanced School & Coaching Management
      </footer>
    </div>
  );
}
