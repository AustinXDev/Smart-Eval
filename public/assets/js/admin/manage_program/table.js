import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { getPrograms } from "./api/api.js";

let table;
let latestDraw = 0;
let listError = "";

const el = {
  table: () => document.getElementById("programTable"),
  search: () => document.getElementById("searchBox"),
  clearSearch: () => document.getElementById("clear-search"),
  department: () => document.getElementById("departmentFilter"),
  reset: () => document.getElementById("reset-filters"),
  count: () => document.getElementById("table-count"),
};

$(document).ready(async function () {
  // KPI cards load first and on their own, so nothing below can block them.
  loadProgramCard();

  table = createDataTable(
    "#programTable",
    {
      serverSide: true,
      pageLength: 10,
      ordering: false,
      columnDefs: [
        { orderable: false, searchable: false, targets: 4 },
        { className: "whitespace-nowrap", targets: [0, 2, 3, 4] },
      ],
      language: {
        emptyTable: emptyState(
          "fa-graduation-cap",
          "No programs yet",
          "Add a program so students and teachers can be assigned to it.",
        ),
        zeroRecords: emptyState(
          "fa-magnifying-glass",
          "No programs match your search or filters",
          "Try a different program code, name, or department.",
          true,
        ),
        loadingRecords:
          '<div class="py-12 text-center text-sm text-slate-400">Loading programs…</div>',
        info: "Showing _START_ to _END_ of _TOTAL_ programs",
        infoEmpty: "Showing 0 to 0 of 0 programs",
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
        const element = el.table();
        element?.classList.add("opacity-60");
        element?.setAttribute("aria-busy", "true");
        let result;
        try {
          const response = await getPrograms({
            draw,
            start: request.start,
            length: request.length,
            search: request.search.value,
            department: $("#departmentFilter").val() || "All",
          });
          if (response?.status !== "success") {
            throw new Error(response?.message || "Unable to load programs.");
          }
          if (
            !Array.isArray(response.data) ||
            Number(response.draw) !== draw ||
            !Number.isInteger(Number(response.recordsTotal)) ||
            !Number.isInteger(Number(response.recordsFiltered)) ||
            Number(response.recordsTotal) < 0 ||
            Number(response.recordsFiltered) < 0
          ) {
            throw new Error("Invalid program-list response.");
          }
          result = {
            draw,
            recordsTotal: Number(response.recordsTotal),
            recordsFiltered: Number(response.recordsFiltered),
            data: buildProgramRows(response.data),
          };
          if (draw === latestDraw) {
            listError = "";
            const count = el.count();
            if (count) count.textContent = Number(response.recordsFiltered) || 0;
          }
        } catch (error) {
          console.error("Error loading programs:", error);
          if (draw === latestDraw)
            listError = error.message || "Unable to load programs.";
          result = { draw, recordsTotal: 0, recordsFiltered: 0, data: [] };
        }
        try {
          callback(result);
        } finally {
          if (draw === latestDraw) {
            element?.classList.remove("opacity-60");
            element?.setAttribute("aria-busy", "false");
          }
        }
      },
      createdRow(row) {
        row.classList.add(
          "border-b",
          "border-slate-100",
          "transition-colors",
          "duration-150",
          "hover:bg-slate-50/70",
        );
        row
          .querySelectorAll("td")
          .forEach((td) => td.classList.add("px-5", "py-3.5", "align-middle"));
      },
      drawCallback() {
        if (!listError) return;
        const cell = this.api()
          .table()
          .container()
          .querySelector("td.dataTables_empty, td.dt-empty");
        if (cell) cell.innerHTML = errorState(listError);
      },
    },
    "Search programs",
  );

  try {
    bindFilters();
  } catch (error) {
    console.error("Program filters could not be initialised:", error);
  }
});

// ================================================================
// FILTERS (search goes through DataTables' own search, as before)
// ================================================================

function bindFilters() {
  let timer;

  const runSearch = (value) => {
    if (table.search() !== value) table.search(value).draw();
    else table.draw();
  };

  const syncClearButton = () => {
    const hasValue = el.search().value.length > 0;
    el.clearSearch().classList.toggle("hidden", !hasValue);
    el.clearSearch().classList.toggle("inline-flex", hasValue);
  };

  el.search().addEventListener("input", () => {
    syncClearButton();
    clearTimeout(timer);
    const value = el.search().value;
    timer = setTimeout(() => {
      if (table.search() !== value) table.search(value).draw();
    }, 350);
  });

  el.search().addEventListener("keydown", (event) => {
    if (event.key === "Escape" && el.search().value) {
      el.search().value = "";
      syncClearButton();
      runSearch("");
    }
  });

  el.clearSearch().addEventListener("click", () => {
    el.search().value = "";
    syncClearButton();
    el.search().focus();
    runSearch("");
  });

  el.department().addEventListener("change", () => {
    clearTimeout(timer);
    table.search(el.search().value || "").draw();
  });

  const resetAll = () => {
    clearTimeout(timer);
    el.search().value = "";
    el.department().value = "All";
    syncClearButton();
    table.search("").draw();
  };

  el.reset().addEventListener("click", resetAll);

  el.table().addEventListener("click", (event) => {
    if (event.target.closest("[data-clear-filters]")) resetAll();
    if (event.target.closest(".retryPrograms")) loadPrograms();
  });
}

export function loadPrograms() {
  if (!table) return Promise.resolve();
  return new Promise((resolve) => table.ajax.reload(resolve, true));
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
      <p class="mt-3 text-sm font-semibold text-slate-700">Programs could not be loaded</p>
      <p class="mt-1 text-xs text-slate-500">${escapeHTML(message)}</p>
      <button type="button" class="retryPrograms mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]">
        <i class="fas fa-rotate-right text-[11px]" aria-hidden="true"></i>Try again
      </button>
    </div>`;
}

// ================================================================
// ROWS
// ================================================================

const DEPARTMENT = { college: "College", shs: "SHS" };

function buildProgramRows(programs) {
  return programs.map((p) => {
    const isActive = Number(p.is_active) === 1;
    const name = escapeHTML(p.program_name);
    const dept = String(p.department ?? "").toLowerCase();

    const iconButton = (cls, icon, label, hover, data) => `
      <button type="button" ${data} title="${label}" aria-label="${label}: ${name}"
        class="${cls} flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors duration-150 ${hover} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF]">
        <i class="fa-solid ${icon} text-xs" aria-hidden="true"></i>
      </button>`;

    // Same data attributes as before: index.js fills the edit form from them.
    const editData = `data-program-id="${escapeHTML(String(p.program_id))}"
        data-program-code="${escapeHTML(p.program_code)}"
        data-program-name="${name}"
        data-program-department="${escapeHTML(p.department)}"`;
    const deleteData = `data-program-id="${escapeHTML(String(p.program_id))}"`;

    return [
      `<span class="ms-id inline-flex items-center rounded-md bg-[#6010FF]/10 px-2 py-0.5 text-xs font-semibold tracking-wide text-[#6010FF]">${escapeHTML(p.program_code)}</span>`,

      `<span class="ms-name block max-w-[420px] truncate font-semibold text-slate-900" title="${name}">${name}</span>`,

      `<span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">${escapeHTML(DEPARTMENT[dept] ?? String(p.department ?? "").toUpperCase())}</span>`,

      `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${
        isActive
          ? "bg-emerald-50 text-emerald-700 ring-emerald-600/15"
          : "bg-rose-50 text-rose-700 ring-rose-600/15"
      }">
        <span class="h-1.5 w-1.5 rounded-full ${isActive ? "bg-emerald-500" : "bg-rose-500"}"></span>${isActive ? "Active" : "Inactive"}
      </span>`,

      `<div class="flex items-center justify-end gap-1.5">
        ${iconButton("editProgram", "fa-pen", "Edit program", "hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]", editData)}
        ${iconButton("deleteProgram", "fa-trash-alt", "Delete program", "hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600", deleteData)}
      </div>`,
    ];
  });
}

// ================================================================
// KPI CARDS
// ================================================================

function setText(id, value) {
  const node = document.getElementById(id);
  if (node) node.textContent = value;
}

const percent = (part, whole) =>
  whole > 0 ? `${((part / whole) * 100).toFixed(1)}%` : "";

export async function loadProgramCard() {
  try {
    const response = await getPrograms({ counts_only: 1 });

    if (response.status !== "success") {
      throw new Error(response.message || "Unable to load program counts.");
    }

    const counts = response.counts ?? {};
    const total = Number(counts.total) || 0;
    const active = Number(counts.active) || 0;
    const inactive = Number(counts.inactive) || 0;

    setText("total-programs", total.toLocaleString());
    setText("total-active", active.toLocaleString());
    setText("total-inactive", inactive.toLocaleString());
    setText("total-active-share", percent(active, total));
    setText("total-inactive-share", percent(inactive, total));
  } catch (error) {
    console.error("Error loading program counts:", error);
    ["total-programs", "total-active", "total-inactive"].forEach((id) => setText(id, "—"));
  }
}
