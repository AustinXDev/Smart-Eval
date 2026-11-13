import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { fetchAllPrograms } from "../shared/program_api.js";
import { formatYearLevel } from "../report_analytics/modules/helpers/formatters.js";
import { getStudentsByDepartment } from "./api/api.js";

let department;
let table;
let latestDraw = 0;

const el = {
  table: () => document.getElementById("studentsTable"),
  search: () => document.getElementById("searchBox"),
  clearSearch: () => document.getElementById("clear-search"),
  course: () => document.getElementById("courseFilter"),
  year: () => document.getElementById("yearFilter"),
  reset: () => document.getElementById("reset-filters"),
  count: () => document.getElementById("table-count"),
  yearCards: () => document.querySelectorAll("[data-year-card]"),
};

// students.account_status: 'active' once the student activated their account.
const ACCOUNT_STATUS = {
  active: {
    label: "Activated",
    badge: "bg-emerald-50 text-emerald-700 ring-emerald-600/15",
    dot: "bg-emerald-500",
    hint: "The student activated their account",
  },
  pending: {
    label: "Pending",
    badge: "bg-amber-50 text-amber-700 ring-amber-600/15",
    dot: "bg-amber-500",
    hint: "Waiting for the student to activate their account",
  },
};

const dateFmt = new Intl.DateTimeFormat(undefined, { month: "short", day: "numeric", year: "numeric" });

$(document).ready(async function () {
  const wrapper = document.getElementById("tableWrapper");

  department = wrapper ? wrapper.dataset.department : "";

  // ============================================================
  // DATATABLE (server-side, same pattern as Evaluation Monitoring)
  // ============================================================

  table = createDataTable(
    "#studentsTable",
    {
      serverSide: true,
      pageLength: 10,
      ordering: false,
      ajax: async function (request, callback) {
        const draw = Number(request.draw);
        latestDraw = draw;

        const tableElement = el.table();
        tableElement?.classList.add("opacity-60");
        tableElement?.setAttribute("aria-busy", "true");

        try {
          const response = await getStudentsByDepartment(department, {
            draw: request.draw,
            start: request.start,
            length: request.length,
            search: el.search()?.value.trim() ?? "",
            status: "All",
            program: el.course()?.value || "All",
            year_level: el.year()?.value || "",
          });

          if (response.status !== "success") {
            throw new Error(response.message || "Unable to load students.");
          }

          if (draw === latestDraw) {
            el.count().textContent = Number(response.recordsFiltered) || 0;
          }

          callback({ ...response, data: response.data.map(renderStudentRow) });
        } catch (error) {
          callback({
            draw: request.draw,
            recordsTotal: 0,
            recordsFiltered: 0,
            data: [],
          });
          StatusModal.show(
            "Error",
            error.message || "Unable to load students.",
            "error",
          );
        } finally {
          if (draw === latestDraw) {
            tableElement?.classList.remove("opacity-60");
            tableElement?.setAttribute("aria-busy", "false");
          }
        }
      },
      createdRow: styleStudentRow,
      columnDefs: [{ className: "whitespace-nowrap", targets: [0, 3, 4, 5, 6] }],
      language: {
        emptyTable: emptyState(
          "fa-user-graduate",
          "No students yet",
          "Add a student or import a CSV file to build the directory.",
        ),
        zeroRecords: emptyState(
          "fa-magnifying-glass",
          "No students match your search or filters",
          "Try a different name, Student ID, course, or year level.",
          true,
        ),
        loadingRecords:
          '<div class="py-12 text-center text-sm text-slate-400">Loading students…</div>',
        info: "Showing _START_ to _END_ of _TOTAL_ students",
        infoEmpty: "Showing 0 to 0 of 0 students",
        infoFiltered: "",
        paginate: {
          previous:
            '<span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-chevron-left text-[9px]"></i><span>Previous</span></span>',
          next: '<span class="inline-flex items-center gap-1.5"><span>Next</span><i class="fa-solid fa-chevron-right text-[9px]"></i></span>',
        },
      },
    },
    "Search students",
  );

  bindFilters();

  // ============================================================
  // LOAD PROGRAMS (course filter)
  // ============================================================

  const select = el.course();

  if (select) {
    const programs = await fetchAllPrograms(department);

    programs.forEach((program) => {
      const programName = program.program_name.trim();
      const option = document.createElement("option");

      option.value = program.program_name;
      option.textContent = programName.toUpperCase();

      select.appendChild(option);
    });
  }

  // ============================================================
  // INITIAL LOAD
  // ============================================================

  await loadStudentCard();
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

  el.course().addEventListener("change", reload);

  el.year().addEventListener("change", () => {
    syncYearCards();
    reload();
  });

  // Year level cards toggle the year filter.
  el.yearCards().forEach((card) => {
    card.addEventListener("click", () => {
      const level = card.dataset.yearCard;
      el.year().value = el.year().value === level ? "" : level;
      syncYearCards();
      reload();
    });
  });

  const resetAll = () => {
    el.search().value = "";
    el.course().value = "All";
    el.year().value = "";
    syncClearButton();
    syncYearCards();
    reload();
  };

  el.reset().addEventListener("click", resetAll);

  // "Clear search & filters" inside the no-results state.
  el.table().addEventListener("click", (event) => {
    if (event.target.closest("[data-clear-filters]")) resetAll();
  });
}

function syncYearCards() {
  const selected = el.year().value;
  el.yearCards().forEach((card) => {
    const active = card.dataset.yearCard === selected;
    card.setAttribute("aria-pressed", String(active));
    card.querySelector("[data-year-filtered]")?.classList.toggle("hidden", !active);
  });
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

// ================================================================
// LOAD STUDENTS
// ================================================================

export function loadStudents() {
  return new Promise((resolve) => table.ajax.reload(resolve, true));
}

function parseDate(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime()) ? null : date;
}

function renderStudentRow(student) {
  const id = escapeHTML(student.student_id);
  const status = ACCOUNT_STATUS[student.account_status] ?? {
    label: student.account_status ? String(student.account_status) : "Unknown",
    badge: "bg-slate-50 text-slate-600 ring-slate-500/10",
    dot: "bg-slate-400",
    hint: "",
  };
  const created = parseDate(student.created_at);

  const iconButton = (cls, icon, label, hover) => `
    <button type="button" data-student-id="${id}" title="${label}" aria-label="${label} ${escapeHTML(student.full_name)}"
      class="${cls} flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors duration-150 ${hover} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] cursor-pointer">
      <i class="fas ${icon} text-xs" aria-hidden="true"></i>
    </button>`;

  return [
    `<span class="ms-id font-medium text-slate-600">${id}</span>`,

    `<span class="flex items-center gap-3">
      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#6010FF]/10 text-xs font-semibold text-[#6010FF]" aria-hidden="true">${escapeHTML(getInitials(student.full_name))}</span>
      <span class="ms-name min-w-0 truncate font-semibold text-slate-900">${escapeHTML(student.full_name)}</span>
    </span>`,

    `<span class="ms-course inline-block max-w-[220px] truncate align-middle text-slate-600" title="${escapeHTML(student.program_name)}">${escapeHTML(student.program_name.toUpperCase())}</span>`,

    `<span class="text-slate-600">${escapeHTML(student.year_level ? formatYearLevel(student.year_level) : "—")}</span>`,

    `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${status.badge}" title="${escapeHTML(status.hint)}">
      <span class="h-1.5 w-1.5 rounded-full ${status.dot}"></span>${escapeHTML(status.label)}
    </span>`,

    created
      ? `<time datetime="${escapeHTML(student.created_at)}" title="${escapeHTML(created.toLocaleString())}" class="tabular-nums text-slate-600">${dateFmt.format(created)}</time>`
      : '<span class="text-slate-400">—</span>',

    `<div class="flex items-center justify-end gap-1.5">
      ${iconButton("viewBtn", "fa-eye", "View", "hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600")}
      ${iconButton("editBtn", "fa-pen", "Edit", "hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]")}
      ${iconButton("deleteBtn", "fa-trash-alt", "Delete", "hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600")}
    </div>`,
  ];
}

function styleStudentRow(rowNode) {
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

// ================================================================
// GET INITIALS
// ================================================================

function getInitials(name) {
  if (!name) return "?";

  const words = name.trim().split(/\s+/);

  if (words.length === 1) {
    return words[0].substring(0, 2).toUpperCase();
  }

  return (words[0][0] + words[words.length - 1][0]).toUpperCase();
}

// ================================================================
// LOAD KPI CARDS (status + per year level)
// ================================================================

const STATUS_IDS = ["total-students", "total-active", "total-inactive"];

function setText(id, value) {
  const node = document.getElementById(id);
  if (node) node.textContent = value;
}

const percent = (part, whole) => (whole > 0 ? `${((part / whole) * 100).toFixed(1)}%` : "");

export async function loadStudentCard() {
  try {
    const data = await getStudentsByDepartment(department, { counts_only: 1 });

    if (data.status !== "success") {
      throw new Error(data.message || "Unable to load student counts.");
    }

    const counts = data.counts ?? {};
    const total = Number(counts.total) || 0;
    const active = Number(counts.active) || 0;
    const inactive = Number(counts.inactive) || 0;

    setText("total-students", total.toLocaleString());
    setText("total-active", active.toLocaleString());
    setText("total-inactive", inactive.toLocaleString());
    setText("total-active-share", percent(active, total));
    setText("total-inactive-share", percent(inactive, total));

    // Year level cards: share of active students (the directory's scope).
    const years = Array.isArray(data.year_levels) ? data.year_levels : [];
    const yearTotal = years.reduce((sum, y) => sum + (Number(y.total) || 0), 0);

    years.forEach(({ year_level: level, total: n }) => {
      const value = Number(n) || 0;
      setText(`year-${level}`, value.toLocaleString());
      setText(`year-${level}-share`, percent(value, yearTotal));
      const bar = document.getElementById(`year-${level}-bar`);
      if (bar) bar.style.width = yearTotal > 0 ? `${(value / yearTotal) * 100}%` : "0%";
    });
  } catch (error) {
    console.error("Error loading student counts:", error);

    STATUS_IDS.forEach((id) => setText(id, "—"));
    el.yearCards().forEach((card) => setText(`year-${card.dataset.yearCard}`, "—"));
  }
}
