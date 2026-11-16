import { createDataTable } from "../shared/datatable_config.js";
import { fetchArchivedPeriods, fetchRecoveryLogs } from "./api/api.js";
import { byId, filterValues } from "./components/ui.js";
import * as render from "./components/archiveRender.js";

// Recovery logs are initialized only when their tab is first opened.
export const ARCHIVE_FILTERS = [
  "department",
  "year",
  "semester",
  "backup_status",
  "search",
];
export const LOG_FILTERS = ["period_id", "department", "search"];

export const tableInstances = { archive: null, logs: null };

function emptyState(icon, title, text) {
  return `
    <div class="flex flex-col items-center justify-center py-12 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-400">
        <i class="fa-solid ${icon} text-lg" aria-hidden="true"></i>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-700">${title}</p>
      <p class="mt-1 text-xs text-slate-500">${text}</p>
    </div>`;
}

const departmentTag = (value) =>
  `<span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">${render.department(value)}</span>`;

function tableOptions(id, loadRecords, prefix, names, columns) {
  let latestDraw = 0;
  const stateId = prefix === "archive" ? "records-state" : "logs-state";
  return {
    serverSide: true,
    processing: false,
    searching: false,
    ordering: false,
    pageLength: 10,
    columns,
    language: {
      emptyTable: emptyState(
        "fa-box-open",
        "No records to display",
        "Check the filters or the status message above.",
      ),
      zeroRecords: emptyState(
        "fa-magnifying-glass",
        "No matching records",
        "Try adjusting the filters.",
      ),
      loadingRecords:
        '<div class="py-12 text-center text-sm text-slate-400">Loading records…</div>',
      info: "Showing _START_ to _END_ of _TOTAL_ records",
      infoEmpty: "Showing 0 to 0 of 0 records",
      infoFiltered: "",
      paginate: {
        previous:
          '<span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-chevron-left text-[9px]"></i><span>Previous</span></span>',
        next: '<span class="inline-flex items-center gap-1.5"><span>Next</span><i class="fa-solid fa-chevron-right text-[9px]"></i></span>',
      },
    },
    ajax: async (req, callback) => {
      latestDraw = req.draw;
      byId(stateId).textContent = "Loading records…";
      byId(id).setAttribute("aria-busy", "true");
      try {
        const result = await loadRecords({
          draw: req.draw,
          start: req.start,
          length: req.length,
          ...filterValues(prefix, names),
        });
        if (
          !Array.isArray(result.data) ||
          Number(result.draw) !== Number(req.draw)
        ) {
          throw new Error("The server returned an invalid record list.");
        }
        callback(result);
        if (latestDraw === req.draw)
          byId(stateId).textContent = result.recordsFiltered
            ? ""
            : "No matching records.";
      } catch (error) {
        callback({
          draw: req.draw,
          recordsTotal: 0,
          recordsFiltered: 0,
          data: [],
        });
        if (latestDraw === req.draw)
          byId(stateId).textContent = error.message + " Use Refresh to retry.";
      } finally {
        if (latestDraw === req.draw)
          byId(id).setAttribute("aria-busy", "false");
      }
    },
    createdRow: (row) => {
      row.classList.add(
        "border-b",
        "border-slate-100",
        "transition-colors",
        "duration-150",
        "hover:bg-slate-50/70",
      );
      row
        .querySelectorAll("td")
        .forEach((cell) => cell.classList.add("px-5", "py-3.5", "align-middle"));
    },
  };
}

const ordinal = {
  data: null,
  render: (_value, _type, _row, meta) =>
    `<span class="tabular-nums text-xs font-semibold text-slate-400">${meta.settings._iDisplayStart + meta.row + 1}</span>`,
};

export function initArchiveTable() {
  if (tableInstances.archive) return tableInstances.archive;
  tableInstances.archive = createDataTable(
    "#archive-table",
    tableOptions(
      "archive-table",
      fetchArchivedPeriods,
      "archive",
      ARCHIVE_FILTERS,
      [
        ordinal,
        { data: null, render: (_v, _t, row) => render.period(row) },
        { data: "target_dept", render: departmentTag },
        {
          data: "archived_at",
          render: render.date,
          className: "whitespace-nowrap",
        },
        { data: "backup_status", render: render.badge },
        {
          data: null,
          render: (_v, _t, row) =>
            `<div class="min-w-36 text-xs leading-5 text-slate-500"><p><span class="font-semibold tabular-nums text-slate-800">${render.count(row.students)}</span> students · <span class="font-semibold tabular-nums text-slate-800">${render.count(row.teachers)}</span> teachers</p><p><span class="font-semibold tabular-nums text-slate-800">${render.count(row.submissions)}</span> submissions</p></div>`,
        },
        { data: null, className: "text-right", render: (_v, _t, row) => render.archiveActions(row) },
      ],
    ),
  );
  return tableInstances.archive;
}

export function initLogTable() {
  if (tableInstances.logs) return tableInstances.logs;
  tableInstances.logs = createDataTable(
    "#recovery-log-table",
    tableOptions("recovery-log-table", fetchRecoveryLogs, "logs", LOG_FILTERS, [
      ordinal,
      {
        data: "completed_at",
        render: render.date,
        className: "whitespace-nowrap",
      },
      { data: null, render: (_v, _t, row) => render.period(row) },
      { data: "target_dept", render: departmentTag },
      {
        data: null,
        render: (_v, _t, row) =>
          render.text(
            row.administrator ?? `Admin #${row.admin_id} (name unavailable)`,
          ),
      },
      {
        data: null,
        render: (_v, _t, row) => render.restoreStatus(row.status),
      },
      {
        data: null,
        render: (_v, _t, row) =>
          `<div class="min-w-36 max-w-xs break-words text-xs leading-5">${render.text(row.source)}<br>${row.records_restored == null ? "Count not recorded" : render.count(row.records_restored) + " records restored"}</div>`,
      },
      { data: null, className: "text-right", render: (_v, _t, row) => render.logAction(row) },
    ]),
  );
  return tableInstances.logs;
}
