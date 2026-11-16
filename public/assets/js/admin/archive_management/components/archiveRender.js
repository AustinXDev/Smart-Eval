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
  return `<div class="min-w-40"><p class="ms-name font-semibold text-slate-900">${text(row.academic_year)}</p>
    <p class="mt-0.5 text-xs text-slate-500">${text(row.semester)} · Period #${text(row.period_id)}</p></div>`;
}

export function badge(status) {
  const statuses = {
    verified: ["Verified", "bg-emerald-50 text-emerald-700 ring-emerald-600/15", "bg-emerald-500"],
    pending: ["Pending", "bg-amber-50 text-amber-700 ring-amber-600/15", "bg-amber-500"],
    processing: ["Processing", "bg-blue-50 text-blue-700 ring-blue-600/15", "bg-blue-500"],
    failed: ["Failed", "bg-rose-50 text-rose-700 ring-rose-600/15", "bg-rose-500"],
  };
  const [label, classes, dot] = statuses[status] ?? [
    "Missing job",
    "bg-slate-50 text-slate-600 ring-slate-500/10",
    "bg-slate-400",
  ];
  return `<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${classes}"><span class="h-1.5 w-1.5 rounded-full ${dot}"></span>${label}</span>`;
}

const actionClass =
  "inline-flex h-9 items-center gap-1.5 whitespace-nowrap rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition-colors duration-150 hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF]";

export function archiveActions(row) {
  return `
    <div class="flex items-start justify-end gap-2">
    
      <button type="button" data-view-period="${text(row.period_id)}" class="${actionClass}" aria-label="View details for period ${text(row.period_id)}"><i class="fa-solid fa-eye text-[11px]" aria-hidden="true"></i>View Details</button>

      <details class="group relative shrink-0">

        <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors duration-150 hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF]" aria-label="More actions for period ${text(row.period_id)}">
          <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i></summary>

          <div class="absolute right-0 top-full z-50 mt-2 min-w-44 space-y-0.5 rounded-xl border border-slate-200 bg-white p-1.5 text-left shadow-lg">

          <button 
            type="button" 
            data-verify-period="${text(row.period_id)}" 
            ${row.backup_status === "verified" ? "" : "disabled"}
            class="flex min-h-10 w-full items-center rounded-lg px-3 text-left text-xs font-medium text-slate-700 hover:bg-[#6010FF]/5 hover:text-[#6010FF] disabled:cursor-not-allowed disabled:text-slate-400 disabled:hover:bg-transparent"
            title="${row.backup_status === "verified" ? "Check the stored encrypted backup checksum" : "A previously verified backup is required"}">Verify Backup</button>

          <button 
            type="button" 
            data-delete-period="${text(row.period_id)}"
            class="flex min-h-10 w-full items-center rounded-lg px-3 text-left text-xs font-medium text-rose-600 hover:bg-rose-50 disabled:cursor-not-allowed disabled:text-slate-400" 
            title="Deletion requires an approved retention policy and audited recovery safeguards.">Delete Archive</button>

        </div>
      </details>
    </div>`;
}

export function logAction(row) {
  return `<button type="button" data-view-log="${text(row.operation_id)}" class="${actionClass}" aria-label="View recovery entry ${text(row.operation_id)}"><i class="fa-solid fa-eye text-[11px]" aria-hidden="true"></i>View Details</button>`;
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
    completed: ["bg-emerald-50 text-emerald-700 ring-emerald-600/15", "bg-emerald-500"],
    failed: ["bg-rose-50 text-rose-700 ring-rose-600/15", "bg-rose-500"],
    processing: ["bg-amber-50 text-amber-700 ring-amber-600/15", "bg-amber-500"],
  };
  const [classes, dot] = styles[status] || ["bg-slate-50 text-slate-600 ring-slate-500/10", "bg-slate-400"];
  return `<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold capitalize ring-1 ring-inset ${classes}"><span class="h-1.5 w-1.5 rounded-full ${dot}"></span>${text(status)}</span>`;
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
