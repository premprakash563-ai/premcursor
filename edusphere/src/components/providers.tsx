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
  root.style.setProperty(
    "--brand-primary-soft",
    hexToSoft(school.primaryColor),
  );
}

function hexToSoft(hex: string) {
  const h = hex.replace("#", "");
  if (h.length !== 6) return "#e6f3f4";
  const r = parseInt(h.slice(0, 2), 16);
  const g = parseInt(h.slice(2, 4), 16);
  const b = parseInt(h.slice(4, 6), 16);
  return `rgba(${r}, ${g}, ${b}, 0.12)`;
}

function subscribe(onStoreChange: () => void) {
  const handler = () => onStoreChange();
  window.addEventListener("storage", handler);
  window.addEventListener("campora-store", handler);
  return () => {
    window.removeEventListener("storage", handler);
    window.removeEventListener("campora-store", handler);
  };
}

function emit() {
  window.dispatchEvent(new Event("campora-store"));
}

/* Cache snapshots so useSyncExternalStore gets stable references */
let userRawCache: string | null | undefined;
let userCache: User | null = null;
let schoolRawCache: string | null | undefined;
let schoolCache: SchoolBranding = DEMO_SCHOOL;

function readUser(): User | null {
  try {
    const raw = localStorage.getItem(AUTH_STORAGE_KEY);
    if (raw === userRawCache) return userCache;
    userRawCache = raw;
    userCache = raw ? (JSON.parse(raw) as User) : null;
    return userCache;
  } catch {
    userRawCache = null;
    userCache = null;
    return null;
  }
}

function readSchool(): SchoolBranding {
  try {
    const raw = localStorage.getItem(TENANT_STORAGE_KEY);
    if (raw === schoolRawCache) return schoolCache;
    schoolRawCache = raw;
    schoolCache = raw ? (JSON.parse(raw) as SchoolBranding) : DEMO_SCHOOL;
    return schoolCache;
  } catch {
    schoolRawCache = null;
    schoolCache = DEMO_SCHOOL;
    return DEMO_SCHOOL;
  }
}

const serverUser = () => null;
const serverSchool = () => DEMO_SCHOOL;

export function Providers({ children }: { children: ReactNode }) {
  const user = useSyncExternalStore(subscribe, readUser, serverUser);
  const school = useSyncExternalStore(subscribe, readSchool, serverSchool);

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
    userRawCache = undefined; // bust cache
    emit();
  }, []);

  const logout = useCallback(() => {
    localStorage.removeItem(AUTH_STORAGE_KEY);
    userRawCache = undefined;
    emit();
  }, []);

  const updateSchool = useCallback((patch: Partial<SchoolBranding>) => {
    const next = { ...readSchool(), ...patch };
    localStorage.setItem(TENANT_STORAGE_KEY, JSON.stringify(next));
    schoolRawCache = undefined;
    applyTheme(next);
    emit();
  }, []);

  const resetSchool = useCallback(() => {
    localStorage.setItem(TENANT_STORAGE_KEY, JSON.stringify(DEMO_SCHOOL));
    schoolRawCache = undefined;
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
