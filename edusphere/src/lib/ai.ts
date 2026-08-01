export interface MCQ {
  id: number;
  question: string;
  options: string[];
  answer: string;
  explanation: string;
  difficulty: "easy" | "medium" | "hard";
}

export interface QuestionPaperSection {
  title: string;
  marks: number;
  questions: { text: string; marks: number }[];
}

export interface QuestionPaper {
  title: string;
  subject: string;
  className: string;
  duration: string;
  totalMarks: number;
  instructions: string[];
  sections: QuestionPaperSection[];
}

export interface StudentReportCard {
  studentName: string;
  className: string;
  term: string;
  overallGrade: string;
  percentage: number;
  subjects: {
    name: string;
    marks: number;
    maxMarks: number;
    grade: string;
    remark: string;
  }[];
  strengths: string[];
  improvements: string[];
  aiSummary: string;
  teacherNote: string;
  attendancePct: number;
  rankEstimate: string;
}

function delay(ms = 900) {
  return new Promise((r) => setTimeout(r, ms));
}

const GRADE = (pct: number) => {
  if (pct >= 90) return "A+";
  if (pct >= 80) return "A";
  if (pct >= 70) return "B+";
  if (pct >= 60) return "B";
  if (pct >= 50) return "C";
  return "D";
};

export async function generateReportCard(input: {
  studentName: string;
  className: string;
  term: string;
}): Promise<StudentReportCard> {
  await delay();
  const subjects = [
    { name: "Mathematics", marks: 88, maxMarks: 100 },
    { name: "Physics", marks: 82, maxMarks: 100 },
    { name: "Chemistry", marks: 79, maxMarks: 100 },
    { name: "English", marks: 91, maxMarks: 100 },
    { name: "Computer Science", marks: 94, maxMarks: 100 },
    { name: "Physical Education", marks: 86, maxMarks: 100 },
  ].map((s) => ({
    ...s,
    grade: GRADE((s.marks / s.maxMarks) * 100),
    remark:
      s.marks >= 90
        ? "Outstanding grasp and consistency."
        : s.marks >= 80
          ? "Strong performance; aim for deeper application."
          : "Solid foundation; practice application problems.",
  }));

  const total = subjects.reduce((a, s) => a + s.marks, 0);
  const max = subjects.reduce((a, s) => a + s.maxMarks, 0);
  const percentage = Math.round((total / max) * 100);

  return {
    studentName: input.studentName,
    className: input.className,
    term: input.term,
    overallGrade: GRADE(percentage),
    percentage,
    subjects,
    strengths: [
      "Excellent analytical skills in Computer Science and English",
      "Consistent homework completion and classroom participation",
      "Strong written communication and structured answers",
    ],
    improvements: [
      "Spend extra time on Chemistry numerical practice",
      "Attempt timed mock papers for Physics to improve speed",
      "Join peer study groups for collaborative problem-solving",
    ],
    aiSummary: `${input.studentName} has delivered a ${GRADE(percentage)} performance in ${input.term} with an overall ${percentage}%. The student shows clear strength in language and computing, with reliable science fundamentals. Focused practice on Chemistry application and timed Physics drills should push the next-term trajectory into the top band. Attendance and engagement remain healthy indicators of academic discipline.`,
    teacherNote:
      "A motivated learner who responds well to feedback. Encourage olympiad-style challenges to stretch potential.",
    attendancePct: 94,
    rankEstimate: "Top 15% of class",
  };
}

export async function generateQuestionPaper(input: {
  subject: string;
  className: string;
  topics: string[];
  totalMarks: number;
  duration: string;
}): Promise<QuestionPaper> {
  await delay(1100);
  const topics = input.topics.length
    ? input.topics
    : ["Core syllabus topics"];

  return {
    title: `${input.subject} — Syllabus-Based Question Paper`,
    subject: input.subject,
    className: input.className,
    duration: input.duration,
    totalMarks: input.totalMarks,
    instructions: [
      "Read all questions carefully before answering.",
      "All questions are compulsory unless otherwise stated.",
      "Use neat diagrams wherever required.",
      "Mobile phones and smart devices are prohibited.",
      "Write answers in the provided answer booklet only.",
    ],
    sections: [
      {
        title: "Section A — Short Answer",
        marks: Math.round(input.totalMarks * 0.3),
        questions: topics.slice(0, 4).map((t, i) => ({
          text: `Q${i + 1}. Briefly explain the key concepts of ${t} with one real-world example.`,
          marks: Math.round((input.totalMarks * 0.3) / Math.min(4, topics.length || 4)),
        })),
      },
      {
        title: "Section B — Application",
        marks: Math.round(input.totalMarks * 0.4),
        questions: topics.slice(0, 3).map((t, i) => ({
          text: `Q${i + 5}. Solve / analyse a problem based on ${t}. Show complete working and reasoning.`,
          marks: Math.round((input.totalMarks * 0.4) / 3),
        })),
      },
      {
        title: "Section C — Long Answer / Critical Thinking",
        marks: Math.round(input.totalMarks * 0.3),
        questions: [
          {
            text: `Q8. Discuss interconnections between ${topics[0] || "core concepts"} and ${topics[1] || "advanced applications"}. Include diagrams and evaluation criteria.`,
            marks: Math.round(input.totalMarks * 0.3),
          },
        ],
      },
    ],
  };
}

export async function generateMCQTest(input: {
  subject: string;
  className: string;
  topics: string[];
  count: number;
  difficulty: "mixed" | "easy" | "medium" | "hard";
}): Promise<MCQ[]> {
  await delay(1000);
  const topics = input.topics.length ? input.topics : ["General"];
  const difficulties: MCQ["difficulty"][] =
    input.difficulty === "mixed"
      ? ["easy", "medium", "hard", "medium", "easy"]
      : [input.difficulty];

  return Array.from({ length: input.count }, (_, i) => {
    const topic = topics[i % topics.length];
    const difficulty = difficulties[i % difficulties.length];
    const options = [
      `Fundamental principle of ${topic}`,
      `Common misconception about ${topic}`,
      `Advanced corollary of ${topic}`,
      `Unrelated concept outside ${topic}`,
    ];
    return {
      id: i + 1,
      question: `Which of the following best describes a core idea in ${topic}? (Class ${input.className} · ${input.subject})`,
      options,
      answer: options[0],
      explanation: `The correct choice aligns with the standard syllabus definition of ${topic}. Distractors include misconceptions and out-of-scope ideas.`,
      difficulty,
    };
  });
}

export async function generateLearningInsights(studentName: string) {
  await delay(700);
  return {
    riskScore: 28,
    riskLabel: "Low risk",
    predictions: [
      `${studentName} is projected to score 85–90% in the next term if current pace continues.`,
      "Chemistry practice volume is below peer average — 3 extra sessions/week recommended.",
      "Strong CS performance suggests readiness for olympiad or coding club leadership.",
    ],
    interventions: [
      "Assign adaptive Chemistry worksheets (medium difficulty).",
      "Schedule a 20-min mentor check-in before Unit Test 2.",
      "Share parent digest highlighting strengths and one focus area.",
    ],
  };
}
