import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { formatYearLevel } from "../report_analytics/modules/helpers/formatters.js";
import { getTeachersByDepartment, getTeacherHandles } from "./api/api.js";

let department;
let table;
let latestDraw = 0;
let listError = "";

const el = {
  table: () => document.getElementById("teachersTable"),
  search: () => document.getElementById("searchBox"),
  clearSearch: () => document.getElementById("clear-search"),
  status: () => document.getElementById("statusFilter"),
  reset: () => document.getElementById("reset-filters"),
  count: () => document.getElementById("table-count"),
};

const STATUS = {
  1: {
    label: "Active",
    badge: "bg-emerald-50 text-emerald-700 ring-emerald-600/15",
    dot: "bg-emerald-500",
  },
  0: {
    label: "Inactive",
    badge: "bg-rose-50 text-rose-700 ring-rose-600/15",
    dot: "bg-rose-500",
  },
};

const dateFmt = new Intl.DateTimeFormat(undefined, {
  month: "short",
  day: "numeric",
  year: "numeric",
});

$(document).ready(function () {
  const wrapper = document.getElementById("tableWrapper");
  department = wrapper ? wrapper.dataset.department : "";

  // KPI cards load here, first and on their own (as on the student page), so
  // they never depend on another script calling loadCard().
  loadCard();

  table = createDataTable(
    "#teachersTable",
    {
      serverSide: true,
      pageLength: 10,
      ordering: false,
      createdRow: styleTeacherRow,
      columnDefs: [
        { className: "whitespace-nowrap", targets: [0, 2, 3, 4, 5] },
      ],
      language: {
        emptyTable: emptyState(
          "fa-chalkboard-user",
          "No teachers yet",
          "Add a teacher to start assigning programs.",
        ),
        zeroRecords: emptyState(
          "fa-magnifying-glass",
          "No teachers match your search or filters",
          "Try a different name, Teacher ID, or status.",
          true,
        ),
        loadingRecords:
          '<div class="py-12 text-center text-sm text-slate-400">Loading teachers…</div>',
        info: "Showing _START_ to _END_ of _TOTAL_ teachers",
        infoEmpty: "Showing 0 to 0 of 0 teachers",
        infoFiltered: "",
        paginate: {
          previous:
            '<span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-chevron-left text-[9px]"></i><span>Previous</span></span>',
          next: '<span class="inline-flex items-center gap-1.5"><span>Next</span><i class="fa-solid fa-chevron-right text-[9px]"></i></span>',
        },
      },
      ajax: async function (request, callback) {
        const draw = Number(request.draw);
        latestDraw = draw;

        const tableElement = el.table();
        tableElement?.classList.add("opacity-60");
        tableElement?.setAttribute("aria-busy", "true");

        let result;
        try {
          const response = await getTeachersByDepartment(department, {
            draw,
            start: request.start,
            length: request.length,
            search: el.search()?.value.trim() ?? "",
            status: el.status()?.value || "All",
          });
          if (response.status !== "success")
            throw new Error(response.message || "Unable to load teachers.");
          if (
            !Array.isArray(response.data) ||
            Number(response.draw) !== draw ||
            !Number.isInteger(Number(response.recordsTotal)) ||
            response.recordsTotal == null ||
            !Number.isInteger(Number(response.recordsFiltered)) ||
            response.recordsFiltered == null ||
            Number(response.recordsTotal) < 0 ||
            Number(response.recordsFiltered) < 0
          ) {
            throw new Error("Invalid teacher-list response.");
          }
          result = { ...response, data: response.data.map(renderTeacherRow) };
          if (draw === latestDraw) {
            listError = "";
            el.count().textContent = Number(response.recordsFiltered) || 0;
            if (response.counts) renderCounts(response.counts);
          }
        } catch (error) {
          console.error(error);
          if (draw === latestDraw)
            listError = error.message || "Unable to load teachers.";
          result = { draw, recordsTotal: 0, recordsFiltered: 0, data: [] };
        } finally {
          if (draw === latestDraw) {
            tableElement?.classList.remove("opacity-60");
            tableElement?.setAttribute("aria-busy", "false");
          }
        }
        callback(result);
      },
      drawCallback: function () {
        if (!listError) return;
        const cell = this.api()
          .table()
          .container()
          .querySelector("td.dataTables_empty, td.dt-empty");
        if (cell) cell.innerHTML = errorState(listError);
      },
    },
    "Search teachers",
  );

  bindFilters();
});

// ================================================================
// FILTERS
// ================================================================

function bindFilters() {
  const reload = () => table.ajax.reload(null, true);
  let timer;

  const syncClearButton = () => {
    const hasValue = el.search().value.length > 0;
    el.clearSearch().classList.toggle("hidden", !hasValue);
    el.clearSearch().classList.toggle("inline-flex", hasValue);
  };

  el.search().addEventListener("input", () => {
    syncClearButton();
    clearTimeout(timer);
    timer = setTimeout(reload, 350);
  });

  el.search().addEventListener("keydown", (event) => {
    if (event.key === "Escape" && el.search().value) {
      el.search().value = "";
      syncClearButton();
      reload();
    }
  });

  el.clearSearch().addEventListener("click", () => {
    el.search().value = "";
    syncClearButton();
    el.search().focus();
    reload();
  });

  el.status().addEventListener("change", reload);

  const resetAll = () => {
    el.search().value = "";
    el.status().value = "All";
    syncClearButton();
    reload();
  };

  el.reset().addEventListener("click", resetAll);

  el.table().addEventListener("click", (event) => {
    if (event.target.closest("[data-clear-filters]")) resetAll();
    if (event.target.closest(".retryTeachersBtn")) loadTeachers();
  });
}

export function loadTeachers() {
  if (!table) return Promise.resolve();
  return new Promise((resolve) => table.ajax.reload(resolve, true));
}

// ================================================================
// ROWS
// ================================================================

function parseDate(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime()) ? null : date;
}

function renderHandles(teacher) {
  const count = Number(teacher.handle_count) || 0;
  if (count === 0) {
    return `<span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/15" title="No program assigned, so this teacher cannot be evaluated yet">
      <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>None assigned
    </span>`;
  }
  // handle_list is "BSIT 3, BSED 1" (program code + year level) -> "BSIT 3rd Year, BSED 1st Year"
  const list = escapeHTML(
    String(teacher.handle_list ?? "")
      .split(", ")
      .filter(Boolean)
      .map((item) => {
        const at = item.lastIndexOf(" ");
        return at > 0
          ? `${item.slice(0, at)} ${formatYearLevel(item.slice(at + 1))}`
          : item;
      })
      .join(", "),
  );
  return `<span class="inline-flex items-center gap-2" title="${list}">
    <span class="font-semibold tabular-nums text-slate-800">${count}</span>
    <span class="text-xs text-slate-500">program${count === 1 ? "" : "s"}</span>
  </span>`;
}

function renderTeacherRow(teacher) {
  const id = escapeHTML(String(teacher.teacher_id));
  const name = escapeHTML(teacher.full_name);
  const status = STATUS[Number(teacher.is_active) === 1 ? 1 : 0];
  const created = parseDate(teacher.created_at);

  const iconButton = (cls, icon, label, hover) => `
    <button type="button" data-teacher-id="${id}" title="${label}" aria-label="${label} ${name}"
      class="${cls} flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors duration-150 ${hover} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] cursor-pointer">
      <i class="fas ${icon} text-xs" aria-hidden="true"></i>
    </button>`;

  return [
    `<span class="ms-id font-medium text-slate-600">${escapeHTML(teacher.employee_id)}</span>`,

    `<span class="flex items-center gap-3">
      <img class="h-9 w-9 shrink-0 rounded-full border border-slate-200 bg-[#6010FF]/10 object-cover object-center"
        src="${window.BASE_URL}uploads/teachers/${encodeURIComponent(teacher.image_path)}"
        onerror="this.src='${window.BASE_URL}uploads/teachers/default_teacher.png';this.onerror=null;"
        alt="" loading="lazy">
      <span class="ms-name min-w-0 truncate font-semibold text-slate-900">${name}</span>
    </span>`,

    renderHandles(teacher),

    `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${status.badge}">
      <span class="h-1.5 w-1.5 rounded-full ${status.dot}"></span>${status.label}
    </span>`,

    created
      ? `<time datetime="${escapeHTML(teacher.created_at)}" title="${escapeHTML(created.toLocaleString())}" class="tabular-nums text-slate-600">${dateFmt.format(created)}</time>`
      : '<span class="text-slate-400" title="Added before creation dates were recorded">—</span>',

    `<div class="flex items-center justify-end gap-1.5">
      ${iconButton("viewBtn", "fa-eye", "View", "hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600")}
      ${iconButton("editBtn", "fa-pen", "Edit", "hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]")}
      ${iconButton("deleteBtn", "fa-trash-alt", "Delete", "hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600")}
    </div>`,
  ];
}

function styleTeacherRow(rowNode) {
  rowNode.classList.add(
    "border-b",
    "border-slate-100",
    "transition-colors",
    "duration-150",
    "hover:bg-slate-50/70",
  );

  rowNode
    .querySelectorAll("td")
    .forEach((td) => td.classList.add("px-5", "py-3.5", "align-middle"));
}

function emptyState(icon, title, text, withClear = false) {
  return `
    <div class="flex flex-col items-center justify-center py-12 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-400">
        <i class="fa-solid ${icon} text-lg" aria-hidden="true"></i>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-700">${title}</p>
      <p class="mt-1 text-xs text-slate-500">${text}</p>
      ${withClear ? '<button type="button" data-clear-filters class="mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]"><i class="fas fa-filter-circle-xmark text-[11px]" aria-hidden="true"></i>Clear search &amp; filters</button>' : ""}
    </div>`;
}

function errorState(message) {
  return `
    <div class="flex flex-col items-center justify-center py-12 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-500">
        <i class="fa-solid fa-circle-exclamation text-lg" aria-hidden="true"></i>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-700">Teachers could not be loaded</p>
      <p class="mt-1 text-xs text-slate-500">${escapeHTML(message)}</p>
      <button type="button" class="retryTeachersBtn mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]">
        <i class="fas fa-rotate-right text-[11px]" aria-hidden="true"></i>Try again
      </button>
    </div>`;
}

/**
 * Render the View modal's handles table.
 *
 * Pass the handles you already have (the teacher-details response includes
 * them) to render without another request; pass a teacher id to fetch them.
 * An array as the first argument is treated as the handles themselves.
 */
export async function loadTeacherHandles(teacherId, knownHandles = null) {
  const tbody = document.querySelector("#handleTable tbody");
  tbody.innerHTML = ""; // clear previous rows

  try {
    const handles = Array.isArray(knownHandles)
      ? knownHandles
      : Array.isArray(teacherId)
        ? teacherId
        : await getTeacherHandles(teacherId);

    if (handles.length === 0) {
      tbody.innerHTML = `<tr><td colspan="3" class="text-center p-3">No handles assigned</td></tr>`;
      return;
    }

    handles.forEach((h) => {
      const tr = document.createElement("tr");
      tr.classList.add(
        "border-b",
        "border-gray-200",
        "hover:bg-purple-50",
        "transition",
        "duration-200",
      );

      tr.innerHTML = `
            <!-- LEVEL -->
            <td class="py-3 px-5">
              <span class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-3 py-1 rounded-full text-xs font-medium shadow-sm">
                ${escapeHTML(h.year_level)} ${h.year_level <= 4 ? "Year" : "Grade"}
              </span>
            </td>

            <!-- PROGRAM -->
            <td>
              <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">
                ${escapeHTML(h.program_name)}
              </span>
            </td>

            <!-- ACTION -->
            <td class="pr-5">
              <div class="flex justify-end items-center">

                <button 
                  class="deleteHandleBtn group flex items-center gap-2 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-md transition duration-200"
                  data-loadId="${h.load_id}"
                >
                  <i class="fas fa-trash text-sm transform transition-transform duration-200 group-hover:scale-110"></i>
                  <span class="text-xs font-medium hidden sm:inline">Remove</span>
                </button>

              </div>
            </td>
          `;
      tbody.appendChild(tr);
    });
  } catch (error) {
    const errorMessage =
      error.response?.data?.message ||
      error.message ||
      "Failed to fetch teacher details.";
    StatusModal.show("Error", errorMessage, "error");
  }
}

// ================================================================
// KPI CARDS
// ================================================================

const CARD_IDS = ["total-teachers", "total-active", "total-inactive"];

function setText(id, value) {
  const node = document.getElementById(id);
  if (node) node.textContent = value;
}

const percent = (part, whole) =>
  whole > 0 ? `${((part / whole) * 100).toFixed(1)}%` : "";

export async function loadCard() {
  const container = document.getElementById("card-container");
  // index.js calls this on DOMContentLoaded, possibly before the jQuery ready
  // handler has set `department`, so read it from the page directly.
  const dept =
    container?.dataset.department ||
    document.getElementById("tableWrapper")?.dataset.department ||
    department ||
    "";

  try {
    const response = await getTeachersByDepartment(dept, {
      counts_only: 1,
    });

    if (response?.counts) {
      renderCounts(response.counts);
      return;
    }

    // Fallback for servers whose API does not return counts: derive them from
    // the list endpoint, which every version supports. recordsTotal is all
    // teachers in the department; filtering by Active gives the active count.
    const active = await getTeachersByDepartment(dept, {
      draw: 1,
      start: 0,
      length: 1,
      search: "",
      status: "Active",
    });

    if (active?.status !== "success")
      throw new Error(active?.message || "Unable to load teacher counts.");

    const total = Number(active.recordsTotal) || 0;
    const activeCount = Number(active.recordsFiltered) || 0;
    renderCounts({
      total,
      active: activeCount,
      inactive: Math.max(0, total - activeCount),
    });
  } catch (error) {
    console.error(error);
    CARD_IDS.forEach((id) => setText(id, "—"));
  }
}

// Fill the KPI cards. Called by loadCard() and on every list response that
// carries counts (TeacherService::getTeacherList returns them with the page).
function renderCounts(counts) {
  const total = Number(counts.total) || 0;
  const active = Number(counts.active) || 0;
  const inactive = Number(counts.inactive) || 0;

  setText("total-teachers", total.toLocaleString());
  setText("total-active", active.toLocaleString());
  setText("total-inactive", inactive.toLocaleString());
  setText("total-active-share", percent(active, total));
  setText("total-inactive-share", percent(inactive, total));
}
