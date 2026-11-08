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
      emptyTable:
        '<div class="py-10 text-center text-sm text-slate-500">No records to display. Check the filters or status message above.</div>',
      zeroRecords: "No matching records. Try adjusting the filters.",
      info: "Showing _START_ to _END_ of _TOTAL_ records",
      infoEmpty: "No records",
      infoFiltered: "(from _MAX_ total)",
      paginate: { previous: "Previous", next: "Next" },
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
      row.classList.add("border-b", "border-slate-100", "hover:bg-slate-50");
      row
        .querySelectorAll("td")
        .forEach((cell) => cell.classList.add("px-4", "py-3", "align-top"));
    },
  };
}

const ordinal = {
  data: null,
  render: (_value, _type, _row, meta) =>
    meta.settings._iDisplayStart + meta.row + 1,
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
        { data: "target_dept", render: render.department },
        {
          data: "archived_at",
          render: render.date,
          className: "whitespace-nowrap",
        },
        { data: "backup_status", render: render.badge },
        {
          data: null,
          render: (_v, _t, row) =>
            `<div class="min-w-36 space-y-1 text-xs leading-5 text-slate-600"><p>${render.count(row.students)} students · ${render.count(row.teachers)} teachers</p><p>${render.count(row.submissions)} submissions</p></div>`,
        },
        { data: null, render: (_v, _t, row) => render.archiveActions(row) },
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
      { data: "target_dept", render: render.department },
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
      { data: null, render: (_v, _t, row) => render.logAction(row) },
    ]),
  );
  return tableInstances.logs;
}
