// Pure text/date helpers. No DOM access.

export const dateFmt = new Intl.DateTimeFormat(undefined, { month: "short", day: "numeric", year: "numeric" });
export const timeFmt = new Intl.DateTimeFormat(undefined, { hour: "numeric", minute: "2-digit", second: "2-digit" });

const MODULE_LABELS = {
  auth: "Authentication",
  students: "Students",
  teachers: "Teachers",
  programs: "Programs",
  questionnaires: "Questionnaires",
  periods: "Evaluation Periods",
  monitoring: "Monitoring",
  archive: "Archive",
  evaluation: "Evaluation",
};

/** "force_close" -> "Force Close" */
export const label = (value) =>
  String(value ?? "")
    .split("_")
    .filter(Boolean)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");

export const moduleLabel = (value) => MODULE_LABELS[value] ?? label(value);

/** MySQL "Y-m-d H:i:s" -> Date, or null when invalid. */
export function parseDate(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime()) ? null : date;
}

/** Badge + dot classes, picked by what the action means. */
export function actionStyle(action) {
  const a = String(action ?? "");
  if (/failed|denied/.test(a)) return ["bg-amber-50 text-amber-700 ring-amber-600/15", "bg-amber-500"];
  if (/delete|remove|unassign|close|logout/.test(a)) return ["bg-rose-50 text-rose-700 ring-rose-600/15", "bg-rose-500"];
  if (/create|add|login|activate|complete|restore|assign|register|import/.test(a)) return ["bg-emerald-50 text-emerald-700 ring-emerald-600/15", "bg-emerald-500"];
  if (/update|reset|set|select|submit|verify|notify|preview/.test(a)) return ["bg-blue-50 text-blue-700 ring-blue-600/15", "bg-blue-500"];
  return ["bg-slate-50 text-slate-600 ring-slate-500/10", "bg-slate-400"];
}
