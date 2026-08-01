"use client";

import { useState } from "react";
import {
  Field,
  PageHeader,
  PrimaryButton,
  SecondaryButton,
  inputClass,
} from "@/components/ui";
import { useTenant } from "@/components/providers";
import { DEMO_SCHOOL } from "@/lib/data";
import { Check, Globe2, Palette, RotateCcw } from "lucide-react";

export default function BrandingPage() {
  const { school, updateSchool, resetSchool } = useTenant();
  const [saved, setSaved] = useState(false);
  const [form, setForm] = useState(() => school);

  function save() {
    updateSchool(form);
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  }

  function handleReset() {
    resetSchool();
    setForm(DEMO_SCHOOL);
  }

  return (
    <div>
      <PageHeader
        eyebrow="White-label"
        title="Branding & URL"
        description="When you sell Campora to a school, they manage their own logo, colors, subdomain, and custom domain from here."
        action={
          <div className="flex gap-2">
            <SecondaryButton onClick={handleReset}>
              <RotateCcw className="h-4 w-4" /> Reset demo
            </SecondaryButton>
            <PrimaryButton onClick={save}>
              {saved ? (
                <>
                  <Check className="h-4 w-4" /> Saved
                </>
              ) : (
                "Save branding"
              )}
            </PrimaryButton>
          </div>
        }
      />

      <div className="grid gap-6 lg:grid-cols-2">
        <div className="space-y-4 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6">
          <div className="flex items-center gap-2 text-[var(--brand-primary)]">
            <Palette className="h-5 w-5" />
            <h3 className="font-display text-xl text-[var(--ink)]">
              Brand identity
            </h3>
          </div>
          <Field label="School name">
            <input
              className={inputClass}
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
            />
          </Field>
          <Field label="Short name">
            <input
              className={inputClass}
              value={form.shortName}
              onChange={(e) => setForm({ ...form, shortName: e.target.value })}
            />
          </Field>
          <Field label="Logo initials / mark">
            <input
              className={inputClass}
              value={form.logoText}
              maxLength={3}
              onChange={(e) =>
                setForm({ ...form, logoText: e.target.value.toUpperCase() })
              }
            />
          </Field>
          <Field label="Tagline">
            <input
              className={inputClass}
              value={form.tagline}
              onChange={(e) => setForm({ ...form, tagline: e.target.value })}
            />
          </Field>
          <div className="grid grid-cols-2 gap-4">
            <Field label="Primary color">
              <input
                type="color"
                className="h-11 w-full cursor-pointer rounded-2xl border border-[var(--line)] bg-[var(--panel)] p-1"
                value={form.primaryColor}
                onChange={(e) =>
                  setForm({ ...form, primaryColor: e.target.value })
                }
              />
            </Field>
            <Field label="Accent color">
              <input
                type="color"
                className="h-11 w-full cursor-pointer rounded-2xl border border-[var(--line)] bg-[var(--panel)] p-1"
                value={form.accentColor}
                onChange={(e) =>
                  setForm({ ...form, accentColor: e.target.value })
                }
              />
            </Field>
          </div>
        </div>

        <div className="space-y-4 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6">
          <div className="flex items-center gap-2 text-[var(--brand-primary)]">
            <Globe2 className="h-5 w-5" />
            <h3 className="font-display text-xl text-[var(--ink)]">
              Portal URL
            </h3>
          </div>
          <Field label="Subdomain">
            <div className="flex items-center gap-2">
              <input
                className={inputClass}
                value={form.subdomain}
                onChange={(e) =>
                  setForm({
                    ...form,
                    subdomain: e.target.value
                      .toLowerCase()
                      .replace(/[^a-z0-9-]/g, ""),
                  })
                }
              />
              <span className="shrink-0 text-sm text-[var(--muted)]">
                .campora.app
              </span>
            </div>
          </Field>
          <Field label="Custom domain (optional)">
            <input
              className={inputClass}
              placeholder="portal.yourschool.edu"
              value={form.customDomain ?? ""}
              onChange={(e) =>
                setForm({ ...form, customDomain: e.target.value })
              }
            />
          </Field>
          <Field label="Contact email">
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
          <Field label="Address">
            <textarea
              className={inputClass}
              rows={2}
              value={form.address}
              onChange={(e) => setForm({ ...form, address: e.target.value })}
            />
          </Field>
        </div>
      </div>

      <div className="mt-6 rounded-3xl border border-[var(--line)] bg-[var(--panel)] p-6">
        <p className="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">
          Live preview
        </p>
        <div className="mt-4 flex flex-wrap items-center gap-4">
          <div
            className="flex h-14 w-14 items-center justify-center rounded-2xl text-lg font-bold text-white"
            style={{ background: form.primaryColor }}
          >
            {form.logoText}
          </div>
          <div>
            <p className="font-display text-2xl">{form.name}</p>
            <p className="text-sm text-[var(--muted)]">{form.tagline}</p>
            <p className="mt-1 text-sm">
              <span
                className="rounded-full px-2 py-0.5 text-xs font-medium"
                style={{ background: form.accentColor, color: "#1a1a1a" }}
              >
                {form.subdomain}.campora.app
              </span>
              {form.customDomain && (
                <span className="ml-2 text-[var(--muted)]">
                  → {form.customDomain}
                </span>
              )}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
