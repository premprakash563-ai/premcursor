"use client";

import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useSyncExternalStore,
  type ReactNode,
} from "react";
import {
  ATTENDANCE,
  BOOKS,
  EXAMS,
  FEES,
  LEAVES,
  LIBRARY_ISSUES,
  PAYROLL,
  STAFF,
  STUDENTS,
  TEACHERS,
} from "@/lib/data";
import type {
  AttendanceRow,
  Book,
  Exam,
  FeeRecord,
  LeaveRequest,
  LibraryIssue,
  PayrollRecord,
  StaffMember,
  Student,
  Teacher,
} from "@/lib/types";

const CAMPUS_KEY = "campora_campus_data_v1";

export type CampusData = {
  students: Student[];
  teachers: Teacher[];
  staff: StaffMember[];
  fees: FeeRecord[];
  exams: Exam[];
  attendance: AttendanceRow[];
  leaves: LeaveRequest[];
  payroll: PayrollRecord[];
  books: Book[];
  libraryIssues: LibraryIssue[];
};

const DEFAULT_DATA: CampusData = {
  students: STUDENTS,
  teachers: TEACHERS,
  staff: STAFF,
  fees: FEES,
  exams: EXAMS,
  attendance: ATTENDANCE,
  leaves: LEAVES,
  payroll: PAYROLL,
  books: BOOKS,
  libraryIssues: LIBRARY_ISSUES,
};

type CampusActions = {
  addStudent: (s: Omit<Student, "id">) => void;
  updateStudent: (id: string, patch: Partial<Student>) => void;
  removeStudent: (id: string) => void;
  addTeacher: (t: Omit<Teacher, "id">) => void;
  updateTeacher: (id: string, patch: Partial<Teacher>) => void;
  removeTeacher: (id: string) => void;
  addStaff: (s: Omit<StaffMember, "id">) => void;
  removeStaff: (id: string) => void;
  addFee: (f: Omit<FeeRecord, "id">) => void;
  markFeePaid: (id: string) => void;
  addExam: (e: Omit<Exam, "id">) => void;
  updateExamStatus: (id: string, status: Exam["status"]) => void;
  setAttendanceMark: (
    id: string,
    type: "present" | "absent" | "late",
  ) => void;
  setLeaveStatus: (id: string, status: LeaveRequest["status"]) => void;
  addLeave: (l: Omit<LeaveRequest, "id">) => void;
  markPayrollPaid: (id: string) => void;
  addBook: (b: Omit<Book, "id">) => void;
  issueBook: (bookId: string, borrower: string) => void;
  returnBook: (issueId: string) => void;
  resetCampus: () => void;
};

type CampusContextValue = CampusData & CampusActions;

const CampusContext = createContext<CampusContextValue | null>(null);

let rawCache: string | null | undefined;
let dataCache: CampusData = DEFAULT_DATA;

function subscribe(cb: () => void) {
  const handler = () => cb();
  window.addEventListener("storage", handler);
  window.addEventListener("campora-campus", handler);
  return () => {
    window.removeEventListener("storage", handler);
    window.removeEventListener("campora-campus", handler);
  };
}

function emit() {
  window.dispatchEvent(new Event("campora-campus"));
}

function readData(): CampusData {
  try {
    const raw = localStorage.getItem(CAMPUS_KEY);
    if (raw === rawCache) return dataCache;
    rawCache = raw;
    if (!raw) {
      dataCache = DEFAULT_DATA;
      return dataCache;
    }
    dataCache = { ...DEFAULT_DATA, ...(JSON.parse(raw) as CampusData) };
    return dataCache;
  } catch {
    rawCache = null;
    dataCache = DEFAULT_DATA;
    return DEFAULT_DATA;
  }
}

function writeData(next: CampusData) {
  localStorage.setItem(CAMPUS_KEY, JSON.stringify(next));
  rawCache = undefined;
  emit();
}

function uid(prefix: string) {
  return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 6)}`;
}

function patchList<T extends { id: string }>(
  list: T[],
  id: string,
  patch: Partial<T>,
) {
  return list.map((item) => (item.id === id ? { ...item, ...patch } : item));
}

export function CampusProvider({ children }: { children: ReactNode }) {
  const data = useSyncExternalStore(subscribe, readData, () => DEFAULT_DATA);

  const addStudent = useCallback((s: Omit<Student, "id">) => {
    const cur = readData();
    writeData({ ...cur, students: [{ ...s, id: uid("s") }, ...cur.students] });
  }, []);

  const updateStudent = useCallback((id: string, patch: Partial<Student>) => {
    const cur = readData();
    writeData({ ...cur, students: patchList(cur.students, id, patch) });
  }, []);

  const removeStudent = useCallback((id: string) => {
    const cur = readData();
    writeData({
      ...cur,
      students: cur.students.filter((s) => s.id !== id),
    });
  }, []);

  const addTeacher = useCallback((t: Omit<Teacher, "id">) => {
    const cur = readData();
    writeData({ ...cur, teachers: [{ ...t, id: uid("t") }, ...cur.teachers] });
  }, []);

  const updateTeacher = useCallback((id: string, patch: Partial<Teacher>) => {
    const cur = readData();
    writeData({ ...cur, teachers: patchList(cur.teachers, id, patch) });
  }, []);

  const removeTeacher = useCallback((id: string) => {
    const cur = readData();
    writeData({
      ...cur,
      teachers: cur.teachers.filter((t) => t.id !== id),
    });
  }, []);

  const addStaff = useCallback((s: Omit<StaffMember, "id">) => {
    const cur = readData();
    writeData({ ...cur, staff: [{ ...s, id: uid("st") }, ...cur.staff] });
  }, []);

  const removeStaff = useCallback((id: string) => {
    const cur = readData();
    writeData({ ...cur, staff: cur.staff.filter((s) => s.id !== id) });
  }, []);

  const addFee = useCallback((f: Omit<FeeRecord, "id">) => {
    const cur = readData();
    writeData({ ...cur, fees: [{ ...f, id: uid("f") }, ...cur.fees] });
  }, []);

  const markFeePaid = useCallback((id: string) => {
    const cur = readData();
    writeData({
      ...cur,
      fees: cur.fees.map((f) =>
        f.id === id ? { ...f, paid: f.amount, status: "paid" as const } : f,
      ),
    });
  }, []);

  const addExam = useCallback((e: Omit<Exam, "id">) => {
    const cur = readData();
    writeData({ ...cur, exams: [{ ...e, id: uid("e") }, ...cur.exams] });
  }, []);

  const updateExamStatus = useCallback((id: string, status: Exam["status"]) => {
    const cur = readData();
    writeData({ ...cur, exams: patchList(cur.exams, id, { status }) });
  }, []);

  const setAttendanceMark = useCallback(
    (id: string, type: "present" | "absent" | "late") => {
      const cur = readData();
      writeData({
        ...cur,
        attendance: cur.attendance.map((a) => {
          if (a.id !== id) return a;
          const present = type === "present" ? a.present + 1 : a.present;
          const absent = type === "absent" ? a.absent + 1 : a.absent;
          const late = type === "late" ? a.late + 1 : a.late;
          const total = present + absent;
          const percentage =
            total === 0 ? 100 : Math.round((present / total) * 100);
          return { ...a, present, absent, late, percentage };
        }),
      });
    },
    [],
  );

  const setLeaveStatus = useCallback(
    (id: string, status: LeaveRequest["status"]) => {
      const cur = readData();
      writeData({ ...cur, leaves: patchList(cur.leaves, id, { status }) });
    },
    [],
  );

  const addLeave = useCallback((l: Omit<LeaveRequest, "id">) => {
    const cur = readData();
    writeData({ ...cur, leaves: [{ ...l, id: uid("l") }, ...cur.leaves] });
  }, []);

  const markPayrollPaid = useCallback((id: string) => {
    const cur = readData();
    writeData({
      ...cur,
      payroll: patchList(cur.payroll, id, { status: "paid" }),
    });
  }, []);

  const addBook = useCallback((b: Omit<Book, "id">) => {
    const cur = readData();
    writeData({ ...cur, books: [{ ...b, id: uid("b") }, ...cur.books] });
  }, []);

  const issueBook = useCallback((bookId: string, borrower: string) => {
    const cur = readData();
    const book = cur.books.find((b) => b.id === bookId);
    if (!book || book.available < 1) return;
    const today = new Date();
    const due = new Date(today);
    due.setDate(due.getDate() + 14);
    const fmt = (d: Date) => d.toISOString().slice(0, 10);
    writeData({
      ...cur,
      books: cur.books.map((b) =>
        b.id === bookId ? { ...b, available: b.available - 1 } : b,
      ),
      libraryIssues: [
        {
          id: uid("li"),
          bookTitle: book.title,
          borrower,
          issueDate: fmt(today),
          dueDate: fmt(due),
          status: "issued",
        },
        ...cur.libraryIssues,
      ],
    });
  }, []);

  const returnBook = useCallback((issueId: string) => {
    const cur = readData();
    const issue = cur.libraryIssues.find((i) => i.id === issueId);
    if (!issue || issue.status === "returned") return;
    writeData({
      ...cur,
      libraryIssues: patchList(cur.libraryIssues, issueId, {
        status: "returned",
      }),
      books: cur.books.map((b) =>
        b.title === issue.bookTitle
          ? { ...b, available: Math.min(b.copies, b.available + 1) }
          : b,
      ),
    });
  }, []);

  const resetCampus = useCallback(() => {
    localStorage.removeItem(CAMPUS_KEY);
    rawCache = undefined;
    emit();
  }, []);

  const value = useMemo(
    () => ({
      ...data,
      addStudent,
      updateStudent,
      removeStudent,
      addTeacher,
      updateTeacher,
      removeTeacher,
      addStaff,
      removeStaff,
      addFee,
      markFeePaid,
      addExam,
      updateExamStatus,
      setAttendanceMark,
      setLeaveStatus,
      addLeave,
      markPayrollPaid,
      addBook,
      issueBook,
      returnBook,
      resetCampus,
    }),
    [
      data,
      addStudent,
      updateStudent,
      removeStudent,
      addTeacher,
      updateTeacher,
      removeTeacher,
      addStaff,
      removeStaff,
      addFee,
      markFeePaid,
      addExam,
      updateExamStatus,
      setAttendanceMark,
      setLeaveStatus,
      addLeave,
      markPayrollPaid,
      addBook,
      issueBook,
      returnBook,
      resetCampus,
    ],
  );

  return (
    <CampusContext.Provider value={value}>{children}</CampusContext.Provider>
  );
}

export function useCampus() {
  const ctx = useContext(CampusContext);
  if (!ctx) throw new Error("useCampus must be used within CampusProvider");
  return ctx;
}
