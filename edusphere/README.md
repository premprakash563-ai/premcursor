# Campora — School & Coaching OS

White-label, multi-tenant school and coaching management platform built with **Next.js + React + TypeScript**.

## Why Campora stands out

- **White-label branding** — each school manages logo mark, colors, subdomain (`school.campora.app`), and custom domain
- **Full campus modules** — students, teachers, staff, attendance, fees, exams, library, leave, payroll
- **AI Studio** — report cards, syllabus-based question papers, MCQ tests, learning insights
- Role-based demo login (Admin / Teacher / Student / Staff)

## Stack

- Next.js App Router
- React 19
- TypeScript
- Tailwind CSS 4
- Lucide icons
- Local demo AI generators (ready to wire to OpenAI / Gemini)

## Run locally

```bash
cd edusphere
npm install
npm run dev
```

Open [http://localhost:3000](http://localhost:3000)

## Demo flow

1. Landing page → **Try live demo**
2. Pick a role (no password)
3. Explore modules in the sidebar
4. Admin → **Branding & URL** to customize school identity
5. Teacher/Admin → **AI Report Card / Question Paper / MCQ / Insights**

## Product note (selling to schools)

Yes — when you sell Campora to a school, that school can manage:

| Setting | Example |
|--------|---------|
| Logo / mark | Initials or uploaded logo |
| Brand colors | Primary + accent applied live |
| Subdomain | `dps.campora.app` |
| Custom domain | `portal.yourschool.edu` (DNS CNAME) |

Parents and teachers see the **school brand**, not the platform brand.
