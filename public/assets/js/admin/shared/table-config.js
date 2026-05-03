import { getRatingBadge } from "./utils.js";
import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import {
  fetchAnalytics,
  renderHistoricalBanner,
} from "../report_analytics/report_analytics.js";

export function initRankingTable() {
  return $("#tbl-ranking").DataTable({
    pageLength: 8,
    lengthChange: false,
    searching: true,
    dom: "tip",
    columns: [
      {
        data: null,
        title: "Rank",
        render: (v, type, row, meta) => {
          const n = meta.row + 1;
          const cls =
            n === 1
              ? "rank-1"
              : n === 2
                ? "rank-2"
                : n === 3
                  ? "rank-3"
                  : "rank-n";
          return `<div class="flex justify-center">
            <span class="rank-pill ${cls}">${n}</span>
          </div>`;
        },
      },
      {
        data: "employee_id",
        title: "Teacher",
        render: (v, type, row) => {
          const initials = row.full_name
            .split(" ")
            .map((w) => w[0])
            .join("")
            .slice(0, 2)
            .toUpperCase();
          return `
            <div class="flex items-center gap-2.5">
              <div class="avatar-init">${initials}</div>
              <div class="flex flex-col">
                <span class="font-medium text-sm whitespace-nowrap">${row.full_name}</span>
                <span class="text-xs text-gray-400">${v}</span>
              </div>
            </div>`;
        },
      },
      {
        data: "mean_score",
        title: "Mean Score",
        render: (v) => `
          <div class="flex justify-center">
            <span class="font-semibold">${Number(v).toFixed(2)}</span>
          </div>`,
      },
      {
        data: "adjective_rating",
        title: "Rating",
        render: (v) => getRatingBadge(v),
      },
      {
        data: "total_evaluated",
        title: "Reviews",
        render: (v) => `
          <div class="flex justify-center">
            <span class="font-medium">${v}</span>
          </div>`,
      },
      {
        data: null,
        title: "Actions",
        orderable: false,
        searchable: false,
        className: "text-center",
        render: (data, type, row) => `
          <div class="flex items-center justify-center gap-1.5">
            <button class="btn-act btn-act--blue btn-view" data-id="${row.teacher_id}" title="View Comments">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
              </svg>
            </button>
            <button class="btn-act btn-act--green btn-download" data-id="${row.teacher_id}" data-name="${row.full_name}" title="Download" id="download-btn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
            </button>
          </div>`,
      },
    ],
    order: [[0, "asc"]],
  });
}

export function initNotEvaluatedTable() {
  return $("#tbl-not-evaluated").DataTable({
    pageLength: 8,
    lengthChange: false,
    searching: true,
    dom: "tip",
    columns: [
      {
        data: null,
        title: "#",
        render: (v, type, row, meta) => `
          <div class="flex justify-center">
            <span class="rank-pill rank-n">${meta.row + 1}</span>
          </div>`,
      },
      {
        data: "full_name",
        title: "Teacher", // ✅ fixed from "Student"
        render: (v, _, row) => {
          const initials = v
            .split(" ")
            .map((w) => w[0])
            .join("")
            .slice(0, 2)
            .toUpperCase();
          return `
            <div class="flex items-center gap-2.5">
              <div class="avatar-init" style="background:#E6F1FB;color:#0C447C;">${initials}</div>
              <div class="flex flex-col">
                <span class="font-medium text-sm whitespace-nowrap">${v}</span>
                <span class="text-xs text-gray-400">${row.student_id}</span>
              </div>
            </div>`;
        },
      },
      {
        data: "email",
        title: "Email",
        render: (v) => `
          <div class="flex justify-center">
            <a href="mailto:${v}" class="text-xs text-blue-500 hover:underline whitespace-nowrap">${v}</a>
          </div>`,
      },
      {
        data: "program_name",
        title: "Program",
        render: (v) => `
          <div class="flex justify-center items-center">
            <span class="text-xs text-gray-500 whitespace-nowrap">${v}</span>
          </div>`,
      },
      {
        data: null,
        title: "Status",
        orderable: false,
        render: () => `
          <div class="flex justify-center">
            <span class="badge-pill" style="background:#FCEBEB;border:0.5px solid #F09595;color:#791F1F;">
              <span style="width:5px;height:5px;border-radius:50%;background:#E24B4A;flex-shrink:0;display:inline-block;margin-right:4px;"></span>
              Not Evaluated
            </span>
          </div>`,
      },
    ],
    order: [[1, "asc"]],
  });
}

export function initAbandonedTable() {
  return $("#tbl-abandoned").DataTable({
    pageLength: 8,
    lengthChange: false,
    searching: true,
    dom: "tip",
    language: {
      emptyTable: "No abandoned evaluations at this time.",
    },
    columns: [
      {
        data: null,
        title: "#",
        render: (v, type, row, meta) => `
          <div class="flex justify-center">
            <span class="rank-pill rank-n">${meta.row + 1}</span>
          </div>`,
      },
      {
        data: "full_name", // ✅ match your backend key
        title: "Teacher",
        render: (v, _, row) => {
          const initials = v
            .split(" ")
            .map((w) => w[0])
            .join("")
            .slice(0, 2)
            .toUpperCase();
          return `
            <div class="flex items-center gap-2.5">
              <div class="avatar-init" style="background:#FEF3C7;color:#92400E;">${initials}</div>
              <div class="flex flex-col">
                <span class="font-medium text-sm whitespace-nowrap">${v}</span>
                <span class="text-xs text-gray-400">${row.employee_id ?? ""}</span>
              </div>
            </div>`;
        },
      },
      {
        data: "email",
        title: "Email",
        render: (v) => `
          <a href="mailto:${v}" class="text-xs text-blue-500 hover:underline whitespace-nowrap">${v}</a>`,
      },
      {
        data: "program_name",
        title: "Program",
        render: (v) => `
          <div class="flex justify-center">
            <span class="text-xs text-gray-500 whitespace-nowrap">${v}</span>
          </div>`,
      },
      {
        data: null,
        title: "Status",
        orderable: false,
        render: () => `
          <div class="flex justify-center">
            <span class="badge-pill" style="background:#FEF3C7;border:0.5px solid #F59E0B;color:#92400E;">
              <span style="width:5px;height:5px;border-radius:50%;background:#F59E0B;flex-shrink:0;display:inline-block;margin-right:4px;"></span>
              Abandoned
            </span>
          </div>`,
      },
    ],
    order: [[1, "asc"]],
  });
}

export function initHistoryTable(periodId) {
  if ($.fn.DataTable.isDataTable("#tbl-history")) {
    $("#tbl-history").DataTable().destroy();
  }

  return $("#tbl-history").DataTable({
    pageLength: 5,
    lengthChange: false,
    searching: true,
    dom: "tip",
    language: {
      emptyTable: "No evaluation history found.",
      paginate: {
        previous: "Prev",
        next: "Next",
      },
    },
    columns: [
      {
        data: "academic_year",
        title: "Academic Year",
        render: (v) =>
          `<div class="flex items-center gap-2 justify-center">
            <span class="font-medium text-sm text-gray-700">${v}</span>
          </div>
          `,
      },
      {
        data: "semester",
        title: "Semester",
        render: (v) => `
        <div class="flex items-center gap-2 justify-center">
          <span class="text-sm text-gray-600">${v}</span>
        </div>
        `,
      },
      {
        data: "final_average",
        title: "Mean Score",
        render: (v) => `
          <div class="flex items-center gap-2 justify-center">
            <span class="font-bold text-gray-800">${Number(v || 0).toFixed(2)}</span>
          </div>`,
      },
      {
        data: "period_id",
        title: "Action",
        orderable: false,
        searchable: false,
        className: "text-center",
        render: function (v, type, row) {
          if (type !== "display") return v;

          const activePeriodId = new URLSearchParams(
            window.location.search,
          ).get("period_id");
          const isLoaded = String(v) === String(activePeriodId);

          return `
      <button 
        class="btn-load-period inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold
               ${
                 isLoaded
                   ? "bg-green-50 text-green-700 border border-green-200 cursor-default"
                   : "bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 cursor-pointer"
               }
               transition-all duration-150"
        data-id="${v}"
        data-state="${isLoaded ? "loaded" : "idle"}"
        ${isLoaded ? "disabled" : ""}
        title="Load Historical Data"
      >
        ${
          isLoaded
            ? `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
               <polyline points="20 6 9 17 4 12"/>
             </svg>
             Loaded`
            : `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
               <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
               <circle cx="12" cy="12" r="3"/>
             </svg>
             Load Data`
        }
      </button>`;
        },
      },
    ],
    order: [[0, "desc"]],
  });
}

export function initTableButtonEvents(dept, periodId) {
  document.addEventListener("click", function (e) {
    const viewBtn = e.target.closest(".btn-view");
    if (viewBtn) {
      const teacherId = viewBtn.dataset.id;
      return;
    }

    const downloadBtn = e.target.closest(".btn-download");
    if (downloadBtn) {
      const teacherId = downloadBtn.dataset.id;
      const name = downloadBtn.dataset.name;

      const url = `/Smart-Eval/app/Controllers/reportAnalytics/AnalyticsController.php?action=teacherReport&teacher_id=${teacherId}&dept=${dept}&period_id=${periodId || ""}`;

      showConfirmation({
        title: "Download Report ",
        message: `Are you sure you want to download the report for ${name}`,
        onConfirm: () => {
          window.open(url, "_blank");
        },
      });
      return;
    }

    const mailBtn = e.target.closest(".btn-mail");
    if (mailBtn) {
      const teacherId = mailBtn.dataset.id;
      const teacherName = mailBtn.dataset.name;
      return;
    }

    const loadBtn = e.target.closest(".btn-load-period");
    if (loadBtn) {
      if (
        loadBtn.dataset.state === "loaded" ||
        loadBtn.dataset.state === "loading"
      )
        return;

      const selectedId = loadBtn.dataset.id;

      document.querySelectorAll(".btn-load-period").forEach((b) => {
        if (b !== loadBtn) setLoadBtnState(b, "idle");
      });

      setLoadBtnState(loadBtn, "loading");

      const url = new URL(window.location.href);
      url.searchParams.set("period_id", selectedId);
      window.history.pushState({}, "", url);

      const safetyTimer = setTimeout(() => {
        setLoadBtnState(loadBtn, "loaded");
      }, 8000);

      fetchAnalytics(dept, selectedId)
        .then(() => {
          clearTimeout(safetyTimer);
          setLoadBtnState(loadBtn, "loaded");
          renderHistoricalBanner();
          closeModal("viewHistoryModal");
        })
        .catch(() => {
          clearTimeout(safetyTimer);
          setLoadBtnState(loadBtn, "idle");
        });

      return;
    }
  });
}

//loading helpers
function setLoadBtnState(btn, state) {
  btn.classList.remove(
    "bg-indigo-50",
    "text-indigo-700",
    "border-indigo-200",
    "hover:bg-indigo-600",
    "hover:text-white",
    "hover:border-indigo-600",
    "bg-green-50",
    "text-green-700",
    "border-green-200",
    "bg-gray-50",
    "text-gray-400",
    "border-gray-200",
    "cursor-pointer",
    "cursor-default",
    "cursor-not-allowed",
  );

  if (state === "idle") {
    btn.disabled = false;
    btn.dataset.state = "idle";
    btn.classList.add(
      "bg-indigo-50",
      "text-indigo-700",
      "border-indigo-200",
      "hover:bg-indigo-600",
      "hover:text-white",
      "hover:border-indigo-600",
      "cursor-pointer",
    );
    btn.innerHTML = `
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
      Load Data`;
  } else if (state === "loading") {
    btn.disabled = true;
    btn.dataset.state = "loading";
    btn.classList.add(
      "bg-gray-50",
      "text-gray-400",
      "border-gray-200",
      "cursor-not-allowed",
    );
    btn.innerHTML = `
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="animate-spin">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
      </svg>
      Loading...`;
  } else if (state === "loaded") {
    btn.disabled = true;
    btn.dataset.state = "loaded";
    btn.classList.add(
      "bg-green-50",
      "text-green-700",
      "border-green-200",
      "cursor-default",
    );
    btn.innerHTML = `
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      Loaded`;
  }

  btn.dataset.state = state;
}
