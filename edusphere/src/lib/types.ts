export type Role = "superadmin" | "admin" | "teacher" | "student" | "staff" | "parent";

export interface SchoolBranding {
  name: string;
  shortName: string;
  tagline: string;
  logoText: string;
  logoUrl?: string;
  primaryColor: string;
  accentColor: string;
  subdomain: string;
  customDomain?: string;
  address: string;
  phone: string;
  email: string;
}

export interface User {
  id: string;
  name: string;
  email: string;
  role: Role;
  avatar?: string;
  department?: string;
  className?: string;
  phone?: string;
}

export interface Student {
  id: string;
  name: string;
  rollNo: string;
  className: string;
  section: string;
  gender: string;
  parentName: string;
  phone: string;
  email: string;
  feesStatus: "paid" | "partial" | "due";
  attendancePct: number;
  status: "active" | "inactive";
}

export interface Teacher {
  id: string;
  name: string;
  employeeId: string;
  subjects: string[];
  classes: string[];
  email: string;
  phone: string;
  status: "active" | "on-leave";
}

export interface StaffMember {
  id: string;
  name: string;
  employeeId: string;
  role: string;
  department: string;
  email: string;
  phone: string;
  salary: number;
}

export interface FeeRecord {
  id: string;
  studentName: string;
  className: string;
  amount: number;
  paid: number;
  dueDate: string;
  status: "paid" | "partial" | "overdue" | "pending";
  category: string;
}

export interface Exam {
  id: string;
  name: string;
  className: string;
  subject: string;
  date: string;
  maxMarks: number;
  status: "upcoming" | "ongoing" | "completed";
}

export interface AttendanceRow {
  id: string;
  name: string;
  className: string;
  present: number;
  absent: number;
  late: number;
  percentage: number;
}

export interface LeaveRequest {
  id: string;
  name: string;
  role: string;
  type: string;
  from: string;
  to: string;
  reason: string;
  status: "pending" | "approved" | "rejected";
}

export interface PayrollRecord {
  id: string;
  name: string;
  role: string;
  basic: number;
  allowances: number;
  deductions: number;
  net: number;
  month: string;
  status: "paid" | "processing" | "pending";
}

export interface Book {
  id: string;
  title: string;
  author: string;
  isbn: string;
  category: string;
  copies: number;
  available: number;
}

export interface LibraryIssue {
  id: string;
  bookTitle: string;
  borrower: string;
  issueDate: string;
  dueDate: string;
  status: "issued" | "returned" | "overdue";
}

export interface NavItem {
  href: string;
  label: string;
  icon: string;
  roles: Role[];
}
