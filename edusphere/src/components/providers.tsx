"use client";

import {
  createContext,
  useCallback,
  useContext,
  useLayoutEffect,
  useMemo,
  useSyncExternalStore,
  type ReactNode,
} from "react";
import { DEMO_SCHOOL, DEMO_USERS } from "@/lib/data";
import type { Role, SchoolBranding, User } from "@/lib/types";
import { AUTH_STORAGE_KEY, TENANT_STORAGE_KEY } from "@/lib/utils";

interface AuthState {
  user: User | null;
  login: (role: Role) => void;
  logout: () => void;
}

interface TenantState {
  school: SchoolBranding;
  updateSchool: (patch: Partial<SchoolBranding>) => void;
  resetSchool: () => void;
}

const AuthContext = createContext<AuthState | null>(null);
const TenantContext = createContext<TenantState | null>(null);

function applyTheme(school: SchoolBranding) {
  if (typeof document === "undefined") return;
  const root = document.documentElement;
  root.style.setProperty("--brand-primary", school.primaryColor);
  root.style.setProperty("--brand-accent", school.accentColor);
}

function subscribe(onStoreChange: () => void) {
  window.addEventListener("storage", onStoreChange);
  window.addEventListener("campora-store", onStoreChange);
  return () => {
    window.removeEventListener("storage", onStoreChange);
    window.removeEventListener("campora-store", onStoreChange);
  };
}

function emit() {
  window.dispatchEvent(new Event("campora-store"));
}

function readUser(): User | null {
  try {
    const raw = localStorage.getItem(AUTH_STORAGE_KEY);
    return raw ? (JSON.parse(raw) as User) : null;
  } catch {
    return null;
  }
}

function readSchool(): SchoolBranding {
  try {
    const tenant = localStorage.getItem(TENANT_STORAGE_KEY);
    if (tenant) return JSON.parse(tenant) as SchoolBranding;
  } catch {
    /* ignore */
  }
  return DEMO_SCHOOL;
}

export function Providers({ children }: { children: ReactNode }) {
  const user = useSyncExternalStore(subscribe, readUser, () => null);
  const school = useSyncExternalStore(subscribe, readSchool, () => DEMO_SCHOOL);

  useLayoutEffect(() => {
    applyTheme(school);
  }, [school]);

  const login = useCallback((role: Role) => {
    const found = DEMO_USERS.find((u) => u.role === role) ?? {
      id: "custom",
      name: role.charAt(0).toUpperCase() + role.slice(1),
      email: `${role}@campora.app`,
      role,
    };
    localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(found));
    emit();
  }, []);

  const logout = useCallback(() => {
    localStorage.removeItem(AUTH_STORAGE_KEY);
    emit();
  }, []);

  const updateSchool = useCallback((patch: Partial<SchoolBranding>) => {
    const next = { ...readSchool(), ...patch };
    localStorage.setItem(TENANT_STORAGE_KEY, JSON.stringify(next));
    applyTheme(next);
    emit();
  }, []);

  const resetSchool = useCallback(() => {
    localStorage.setItem(TENANT_STORAGE_KEY, JSON.stringify(DEMO_SCHOOL));
    applyTheme(DEMO_SCHOOL);
    emit();
  }, []);

  const authValue = useMemo(
    () => ({ user, login, logout }),
    [user, login, logout],
  );
  const tenantValue = useMemo(
    () => ({ school, updateSchool, resetSchool }),
    [school, updateSchool, resetSchool],
  );

  return (
    <AuthContext.Provider value={authValue}>
      <TenantContext.Provider value={tenantValue}>
        {children}
      </TenantContext.Provider>
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within Providers");
  return ctx;
}

export function useTenant() {
  const ctx = useContext(TenantContext);
  if (!ctx) throw new Error("useTenant must be used within Providers");
  return ctx;
}
