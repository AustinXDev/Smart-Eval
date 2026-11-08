import { escapeHTML } from "../../../services/escape.js";

export const text = (value) =>
  escapeHTML(value == null || value === "" ? "Not recorded" : value);

export const count = (value) =>
  Number.isFinite(Number(value)) ? Number(value).toLocaleString("en-US") : "—";

export function date(value) {
  if (!value) return "Not recorded";

  // SQL timestamps use the archive connection's +08:00 timezone.
  const source = String(value).includes("T")
    ? String(value)
    : String(value).replace(" ", "T") + "+08:00";

  const parsed = new Date(source);

  return Number.isNaN(parsed.getTime())
    ? text(value)
    : text(
        new Intl.DateTimeFormat("en-PH", {
          dateStyle: "medium",
          timeStyle: "short",
          timeZone: "Asia/Manila",
        }).format(parsed),
      );
}

export function department(value) {
  return value === "college"
    ? "College"
    : value === "shs"
      ? "SHS"
      : "Not recorded";
}

export function period(row) {
  return `<div class="min-w-40"><p class="font-semibold text-slate-800">${text(row.academic_year)}</p>
    <p class="mt-1 text-xs text-slate-500">${text(row.semester)} · Period #${text(row.period_id)}</p></div>`;
}

export function badge(status) {
  const statuses = {
    verified: ["Verified", "bg-emerald-50 text-emerald-800"],
    pending: ["Pending", "bg-amber-50 text-amber-800"],
    processing: ["Processing", "bg-blue-50 text-blue-800"],
    failed: ["Failed", "bg-red-50 text-red-800"],
  };
  const [label, classes] = statuses[status] ?? [
    "Missing job",
    "bg-slate-100 text-slate-600",
  ];
  return `<span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium ${classes}">${label}</span>`;
}

const actionClass =
  "min-h-11 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-violet-700 hover:bg-violet-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500";

export function archiveActions(row) {
  return `
    <div class="flex items-start justify-end gap-2">
    
      <button type="button" data-view-period="${text(row.period_id)}" class="${actionClass}" aria-label="View details for period ${text(row.period_id)}">View Details</button>

      <details class="group relative shrink-0">

        <summary class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500" aria-label="More actions for period ${text(row.period_id)}">
          <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i></summary>

          <div class="absolute right-0 top-full z-50 mt-2 min-w-40 space-y-1 rounded-lg border border-slate-200 bg-white p-2 shadow-lg">

          <button 
            type="button" 
            data-verify-period="${text(row.period_id)}" 
            ${row.backup_status === "verified" ? "" : "disabled"}
            class="min-h-11 w-full rounded px-3 text-left text-xs font-medium text-violet-700 hover:bg-violet-50 disabled:cursor-not-allowed disabled:text-slate-400"
            title="${row.backup_status === "verified" ? "Check the stored encrypted backup checksum" : "A previously verified backup is required"}">Verify Backup</button>

          <button 
            type="button" 
            data-delete-period="${text(row.period_id)}"
            class="min-h-11 w-full rounded px-3 text-left text-xs hover:bg-red-50 text-red-500" 
            title="Deletion requires an approved retention policy and audited recovery safeguards.">Delete Archive</button>

        </div>
      </details>
    </div>`;
}

export function logAction(row) {
  return `<button type="button" data-view-log="${text(row.operation_id)}" class="${actionClass}" aria-label="View recovery entry ${text(row.operation_id)}">View Details</button>`;
}

export function fields(items) {
  return `<dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">${items
    .map(
      ([label, value]) =>
        `<div class="min-w-0"><dt class="text-xs font-medium text-slate-500">${text(label)}</dt><dd class="mt-1 break-words text-sm text-slate-800">${value}</dd></div>`,
    )
    .join("")}</dl>`;
}

function section(title, items) {
  return `<section><h3 class="mb-3 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">${text(title)}</h3>${fields(items)}</section>`;
}

export function archiveDetails(row) {
  return (
    section("Evaluation Information", [
      ["Period ID", text(row.period_id)],
      ["Academic Year", text(row.academic_year)],
      ["Semester", text(row.semester)],
      ["Department", department(row.target_dept)],
      ["Start Date", date(row.start_date)],
      ["End Date", date(row.end_date)],
    ]) +
    section("Archive Information", [
      ["Date Archived (Philippine Time)", date(row.archived_at)],
      ["Archive Status", "Archived"],
      ["Cleanup Status", text(row.cleanup_status)],
      ["Date Cleaned", date(row.cleaned_at)],
    ]) +
    section("Record Summary", [
      ["Student snapshots", count(row.counts.student_snapshots)],
      ["Teacher snapshots", count(row.counts.teacher_snapshots)],
      ["Evaluation submissions", count(row.counts.submissions)],
      ["Evaluation answers", count(row.counts.evaluation_answers)],
      [
        "Evaluation records (all statuses)",
        count(row.counts.evaluation_status),
      ],
      ["Participation records", count(row.counts.participation_history)],
    ]) +
    section("Backup Information", [
      ["Backup Status", badge(row.backup_status)],
      ["Backup Filename", "Not recorded"],
      ["Backup Size", "Not recorded"],
      ["Initial Backup Verification Date", date(row.backup_verified_at)],
    ]) +
    '<p class="text-xs leading-5 text-slate-500">The verification date is the archive job timestamp recorded when the worker first verified its upload. Manual checks do not update that timestamp or test restoration.</p>'
  );
}

export function restoreStatus(status) {
  const styles = {
    completed: "bg-emerald-50 text-emerald-800",
    failed: "bg-red-50 text-red-800",
    processing: "bg-amber-50 text-amber-800",
  };
  return `<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${styles[status] || "bg-slate-100 text-slate-600"}">${text(status)}</span>`;
}

export function logDetails(row) {
  return section("Backup Restoration Operation", [
    ["Operation ID", text(row.operation_id)],
    ["Evaluation Period", period(row)],
    ["Department", department(row.target_dept)],
    ["Administrator", text(row.administrator ?? `Admin #${row.admin_id} (name unavailable)`)],
    ["Source", text(row.source)],
    ["Result", restoreStatus(row.status)],
    ["Completed (Philippine Time)", date(row.completed_at)],
    ["Records Restored", row.records_restored == null ? "Not recorded" : count(row.records_restored)],
    ["Failure Reason", text(row.error_message)],
  ]);
}

export function previewDetails(row) {
  return (
    fields([
      ["Period ID", text(row.period_id)],
      ["Academic Year", text(row.academic_year)],
      ["Semester", text(row.semester)],
      ["Department", department(row.department)],
      ["Backup Date (Philippine Time)", date(row.exported_at)],
      ["Backup Filename", text(row.filename)],
      ["Students", count(row.students)],
      ["Teachers", count(row.teachers)],
      ["Evaluation Submissions", count(row.submissions)],
      ["Evaluation Answers", count(row.answers)],
      ["Total Records", count(row.total_records)],
      [
        "Existing Archive Period",
        row.existing_period ? "Yes — overwrite blocked" : "No",
      ],
    ]) +
    `<p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm leading-6 text-amber-900">${text(row.notice)}</p>`
  );
}
