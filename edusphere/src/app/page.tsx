import Link from "next/link";
import {
  Brain,
  CheckCircle2,
  Globe2,
  Palette,
  Sparkles,
  ArrowRight,
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
  "AI Suite",
];

const AI_FEATURES = [
  {
    title: "AI Report Cards",
    text: "Auto-written narratives, strengths, and next-step plans from marks + attendance.",
    icon: Sparkles,
  },
  {
    title: "Syllabus Question Papers",
    text: "Teachers pick topics — Campora drafts board-style papers with mark schemes.",
    icon: Brain,
  },
  {
    title: "MCQ & Adaptive Tests",
    text: "Generate topic-wise quizzes with difficulty mix and instant explanations.",
    icon: CheckCircle2,
  },
];

export default function HomePage() {
  return (
    <div className="min-h-screen text-[var(--ink)]">
      <div className="hero-mesh relative overflow-hidden">
        <div className="grain pointer-events-none absolute inset-0 opacity-40" />
        <header className="relative z-10 mx-auto flex max-w-6xl items-center justify-between px-5 py-6 md:px-8">
          <div className="flex items-center gap-3">
            <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--brand-primary)] font-display text-lg text-white">
              C
            </div>
            <div>
              <p className="font-display text-2xl leading-none tracking-tight">
                Campora
              </p>
              <p className="text-xs text-[var(--muted)]">School & Coaching OS</p>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <Link
              href="/login"
              className="hidden text-sm font-medium text-[var(--ink-soft)] sm:inline"
            >
              Sign in
            </Link>
            <Link
              href="/login"
              className="inline-flex items-center gap-2 rounded-full bg-[var(--brand-primary)] px-5 py-2.5 text-sm font-medium text-white"
            >
              Open demo <ArrowRight className="h-4 w-4" />
            </Link>
          </div>
        </header>

        <section className="relative z-10 mx-auto grid max-w-6xl gap-10 px-5 pb-20 pt-10 md:grid-cols-[1.15fr_0.85fr] md:px-8 md:pb-28 md:pt-16">
          <div>
            <p className="animate-rise mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--brand-primary)]">
              White-label · Multi-tenant · AI-native
            </p>
            <h1 className="animate-rise-delay font-display text-5xl leading-[1.05] tracking-tight md:text-7xl">
              Campora
            </h1>
            <p className="animate-rise-delay mt-5 max-w-xl text-lg text-[var(--ink-soft)] md:text-xl">
              One campus OS your school can brand as its own — logo, colors,
              subdomain, or custom domain — with AI that writes report cards and
              builds syllabus-perfect exams.
            </p>
            <div className="animate-rise-delay-2 mt-8 flex flex-wrap gap-3">
              <Link
                href="/login"
                className="rounded-full bg-[var(--ink)] px-6 py-3 text-sm font-medium text-[var(--surface)]"
              >
                Try live demo
              </Link>
              <Link
                href="#branding"
                className="rounded-full border border-[var(--ink)]/15 bg-white/50 px-6 py-3 text-sm font-medium backdrop-blur"
              >
                See white-label
              </Link>
            </div>
          </div>

          <div className="animate-float relative hidden md:block">
            <div className="absolute inset-0 rounded-[2rem] bg-[var(--brand-primary)]/10 blur-2xl" />
            <div className="relative overflow-hidden rounded-[2rem] border border-white/60 bg-[var(--panel)]/90 p-6 shadow-[0_30px_80px_-40px_rgba(28,25,23,0.45)] backdrop-blur">
              <div className="mb-5 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--brand-primary)] text-sm font-bold text-white">
                    HI
                  </div>
                  <div>
                    <p className="font-display text-lg">Horizon School</p>
                    <p className="text-xs text-[var(--muted)]">
                      horizon.campora.app
                    </p>
                  </div>
                </div>
                <span className="rounded-full bg-[var(--brand-accent)]/30 px-2.5 py-1 text-xs font-medium">
                  Branded portal
                </span>
              </div>
              <div className="grid grid-cols-2 gap-3 text-sm">
                {[
                  ["Students", "1,248"],
                  ["Fee collected", "₹1.84 Cr"],
                  ["Attendance", "92.4%"],
                  ["AI papers", "146"],
                ].map(([k, v]) => (
                  <div
                    key={k}
                    className="rounded-2xl bg-[var(--surface)] px-4 py-3"
                  >
                    <p className="text-xs text-[var(--muted)]">{k}</p>
                    <p className="mt-1 font-display text-2xl">{v}</p>
                  </div>
                ))}
              </div>
              <div className="mt-4 rounded-2xl border border-[var(--line)] bg-gradient-to-br from-[var(--brand-primary)]/8 to-transparent p-4">
                <p className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[var(--brand-primary)]">
                  <Sparkles className="h-3.5 w-3.5" /> AI Report Card
                </p>
                <p className="mt-2 text-sm leading-relaxed text-[var(--ink-soft)]">
                  “Priya shows A-grade consistency with standout CS & English.
                  Targeted Chemistry drills will lift the next term into the top
                  band…”
                </p>
              </div>
            </div>
          </div>
        </section>
      </div>

      <section className="mx-auto max-w-6xl px-5 py-16 md:px-8">
        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">
          Everything in one OS
        </p>
        <h2 className="mt-2 font-display text-3xl md:text-4xl">
          Modules schools actually run on
        </h2>
        <div className="mt-8 flex flex-wrap gap-2">
          {MODULES.map((m) => (
            <span
              key={m}
              className="rounded-full border border-[var(--line)] bg-[var(--panel)] px-4 py-2 text-sm"
            >
              {m}
            </span>
          ))}
        </div>
      </section>

      <section className="border-y border-[var(--line)] bg-[var(--panel)] py-16">
        <div className="mx-auto max-w-6xl px-5 md:px-8">
          <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">
            What makes Campora different
          </p>
          <h2 className="mt-2 font-display text-3xl md:text-4xl">
            AI that teachers will actually use
          </h2>
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {AI_FEATURES.map((f) => (
              <div key={f.title} className="rounded-3xl bg-[var(--surface)] p-6">
                <f.icon className="h-8 w-8 text-[var(--brand-primary)]" />
                <h3 className="mt-4 font-display text-xl">{f.title}</h3>
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
            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--brand-primary)]">
              Sell to any school
            </p>
            <h2 className="mt-2 font-display text-3xl md:text-4xl">
              Their logo. Their colors. Their URL.
            </h2>
            <p className="mt-4 text-[var(--ink-soft)]">
              Yes — when you sell Campora to a school, they get a fully branded
              portal. Upload logo, set primary/accent colors, choose a subdomain
              like <code className="rounded bg-[var(--surface)] px-1.5 py-0.5 text-sm">dps.campora.app</code>,
              or map a custom domain such as{" "}
              <code className="rounded bg-[var(--surface)] px-1.5 py-0.5 text-sm">portal.school.edu</code>.
            </p>
            <ul className="mt-6 space-y-3 text-sm">
              {[
                "Per-school tenant isolation",
                "Logo + color theme applied live",
                "Subdomain & custom domain ready",
                "Parents see the school brand, not yours",
              ].map((item) => (
                <li key={item} className="flex items-start gap-2">
                  <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-[var(--brand-primary)]" />
                  {item}
                </li>
              ))}
            </ul>
          </div>
          <div className="space-y-4">
            <div className="flex items-start gap-4 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-5">
              <Palette className="h-6 w-6 text-[var(--brand-accent)]" />
              <div>
                <p className="font-medium">Brand kit</p>
                <p className="mt-1 text-sm text-[var(--muted)]">
                  Logo mark, school name, tagline, primary & accent colors —
                  editable by school admin.
                </p>
              </div>
            </div>
            <div className="flex items-start gap-4 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-5">
              <Globe2 className="h-6 w-6 text-[var(--brand-primary)]" />
              <div>
                <p className="font-medium">URL control</p>
                <p className="mt-1 text-sm text-[var(--muted)]">
                  Free subdomain on campora.app, or connect their own domain via
                  DNS CNAME.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-5 pb-20 md:px-8">
        <div className="overflow-hidden rounded-[2rem] bg-[var(--brand-primary)] px-8 py-12 text-white md:px-12">
          <h2 className="font-display text-3xl md:text-4xl">
            Built with Next.js + React
          </h2>
          <p className="mt-3 max-w-2xl text-white/85">
            App Router, TypeScript, role-based dashboards, and AI generators
            ready to wire to OpenAI / Gemini. Perfect stack for a multi-tenant
            SaaS you can sell school-by-school.
          </p>
          <Link
            href="/login"
            className="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-medium text-[var(--brand-primary)]"
          >
            Launch demo campus <ArrowRight className="h-4 w-4" />
          </Link>
        </div>
      </section>

      <footer className="border-t border-[var(--line)] px-5 py-8 text-center text-sm text-[var(--muted)] md:px-8">
        Campora · Advanced School & Coaching Management · Demo build
      </footer>
    </div>
  );
}
