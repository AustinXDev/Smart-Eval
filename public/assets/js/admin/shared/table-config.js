import { getRatingBadge } from "./utils.js";
import { periodId, state } from "../report_analytics/modules/state.js";
import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import { fetchAnalytics } from "../report_analytics/api/api.js";
import { renderHistoricalBanner } from "../report_analytics/modules/render/historical_banner.js";
import { nameToInitials } from "./utils.js";
import { getAdjectiveRating } from "../report_analytics/modules/helpers/formatters.js";

export function initRankingTable() {
  const tableEl = "#tbl-ranking";

  if ($.fn.DataTable.isDataTable(tableEl)) {
    return $(tableEl).DataTable();
  }

  return $(tableEl).DataTable({
    pageLength: 8,
    lengthChange: false,
    searching: true,
    dom: "tip",

    // ============================================================
    // HEADER CONTAINER STYLING
    // ============================================================
    headerCallback: function (thead) {
      $(thead).addClass(
        "border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500",
      );
    },

    // ============================================================
    // COLUMNS
    // ============================================================
    columns: [
      // RANK
      {
        title: "Rank",
        data: null,
        className: "whitespace-nowrap px-5 py-3.5 text-center font-semibold",
        render: (v, type, row, meta) => {
          const n = meta.row + 1;
          const cls =
            n === 1
              ? "border-violet-200 bg-violet-50 text-violet-700"
              : n === 2
                ? "border-slate-300 bg-slate-50 text-slate-600"
                : n === 3
                  ? "border-orange-200 bg-orange-50 text-orange-700"
                  : "border-slate-200 bg-white text-slate-500";

          return `
            <div class="flex items-center justify-center">
              <span
                class="
                  rank-pill ${cls}
                  inline-flex
                  h-8 w-8
                  items-center justify-center
                  rounded-lg
                  border
                  text-[11px]
                  font-bold
                  tracking-tight
                  shadow-sm
                  transition-all
                  duration-200
                  group-hover:-translate-y-0.5
                "
              >
                ${n}
              </span>
            </div>
          `;
        },
      },

      // TEACHER
      {
        title: "Teacher",
        data: "employee_id",
        className: "whitespace-nowrap px-5 py-3.5 font-semibold",
        render: (v, type, row) => {
          const initials = row.full_name
            .split(" ")
            .map((w) => w[0])
            .join("")
            .slice(0, 2)
            .toUpperCase();

          return `
            <div class="flex min-w-[220px] items-center gap-3">
              <div
                class="
                  flex h-9 w-9 shrink-0
                  items-center justify-center
                  rounded-xl
                  border border-violet-100
                  bg-violet-50
                  text-[10px]
                  font-bold
                  text-violet-600
                  transition-all duration-200
                  group-hover:border-violet-200
                  group-hover:bg-violet-100
                "
              >
                ${initials}
              </div>

              <div class="min-w-0">
                <p
                  class="
                    truncate
                    text-sm
                    font-[Roboto]
                    font-semibold
                    leading-5
                    text-slate-800
                    transition-colors
                    duration-150
                    group-hover:text-slate-900
                  "
                >
                  ${row.full_name}
                </p>

                <p
                  class="
                    mt-0.5
                    text-[11px]
                    font-medium
                    text-slate-400
                  "
                >
                  ${v}
                </p>
              </div>
            </div>
          `;
        },
      },

      // MEAN SCORE
      {
        title: "Mean Score",
        data: "mean_score",
        className: "whitespace-nowrap px-5 py-3.5 text-center font-semibold",
        render: (v) => `
          <div class="flex items-center justify-center">
            <span
              class="
                inline-flex
                items-baseline
                rounded-lg
                border border-violet-100
                bg-violet-50
                px-2.5 py-1.5
                text-xs
                font-[Roboto]
                font-bold
                tracking-tight
                text-violet-700
                transition-all
                duration-200
                group-hover:border-violet-200
                group-hover:bg-violet-100
              "
            >
              ${Number(v).toFixed(2)}
              <span
                class="
                  ml-0.5
                  text-[9px]
                  font-medium
                  text-violet-400
                "
              >
                / 5
              </span>
            </span>
          </div>
        `,
      },

      // RATING
      {
        title: "Rating",
        data: "mean_score",
        className: "whitespace-nowrap px-5 py-3.5 text-center font-semibold",
        render: (v) => {
          const rating = (v) => {
            const adjective = getAdjectiveRating(v);

            const style = {
              Outstanding: "text-green-700",
              "Very Satisfactory": "text-blue-700",
              Satisfactory: "text-yellow-700",
              Fair: "text-orange-700",
              Poor: "text-red-700",
              "No Data": "text-gray-700",
            };

            const className = style[adjective] ?? "text-gray-700";

            return `
                <span class="text-xs font-medium ${className}">
                    ${adjective}
                </span>
            `;
          };

          return `
          <div class="flex items-center justify-center">
            ${rating(v)}
          </div>
        `;
        },
      },

      // REVIEWS
      {
        title: "Total Evaluators",
        data: "total_evaluated",
        className: "whitespace-nowrap px-5 py-3.5 text-center font-semibold",
        render: (v) => `
          <div class="flex items-center justify-center">
            <div
              class="
                inline-flex
                items-center
                gap-2
                text-sm
                font-medium
                text-slate-600
              "
            >
              <span
                class="
                  flex h-7 w-7 shrink-0
                  items-center justify-center
                  rounded-lg
                  border border-slate-100
                  bg-slate-50
                  text-slate-400
                  transition-colors
                  duration-150
                  group-hover:bg-white
                "
              >
                <i class="fa-solid fa-users text-[9px]"></i>
              </span>

              <span class="tabular-nums">
                ${v}
              </span>
            </div>
          </div>
        `,
      },

      // ACTIONS
      {
        title: "Actions",
        data: null,
        orderable: false,
        searchable: false,
        className: "whitespace-nowrap px-5 py-3.5 text-center font-semibold",
        render: (data, type, row) => {
          const isClosed = state.isClosed;

          return `
          <div class="flex items-center justify-center gap-1.5">
            <button
              class="
                comment-btn
                btn-act
                btn-act--blue
                btn-view
                inline-flex
                h-8 w-8
                items-center justify-center
                rounded-lg
                border border-slate-200
                bg-white
                text-slate-500
                shadow-sm
                transition-all
                duration-200
                hover:-translate-y-0.5
                hover:border-sky-200
                hover:bg-sky-50
                hover:text-sky-600
                focus:outline-none
                focus:ring-2
                focus:ring-violet-500/20
                active:scale-95
              "
              data-id="${row.teacher_id}"
              data-mean="${row.mean_score}"
              data-name="${row.full_name}"
              title="View Comments"
              aria-label="View Comments"
            >
              <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path
                  d="M21 15a4 4 0 0 1-4 4H8l-5 3V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"
                />
              </svg>
            </button>
            
            ${
              isClosed
                ? `
                    <!-- Download -->
                    <button
                        id="downloadTeacherReportBtn"
                        class="
                            btn-act
                            btn-act--green
                            btn-download
                            inline-flex
                            h-8 w-8
                            items-center justify-center
                            rounded-lg
                            border border-slate-200
                            bg-white
                            text-slate-500
                            shadow-sm
                            transition-all
                            duration-200
                            hover:-translate-y-0.5
                            hover:border-emerald-200
                            hover:bg-emerald-50
                            hover:text-emerald-600
                            focus:outline-none
                            focus:ring-2
                            focus:ring-violet-500/20
                            active:scale-95
                        "
                        data-id="${row.teacher_id}"
                        data-name="${row.full_name}"
                        title="Download"
                        aria-label="Download"
                    >
                        <svg
                            width="14"
                            height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </button>
                    `
                : ""
            }

        </div>
        `;
        },
      },
    ],

    // DEFAULT ORDER
    order: [[0, "desc"]],

    // ROW STYLING
    createdRow: function (row) {
      row.classList.add(
        "group",
        "border-b",
        "border-slate-100",
        "transition-colors",
        "duration-150",
        "hover:bg-slate-50/70",
      );

      row.querySelectorAll("td").forEach((td, index) => {
        td.classList.add("px-5", "py-4", "align-middle", "whitespace-nowrap");

        if (index !== 1) {
          td.classList.add("text-center");
        }
      });

      row.querySelectorAll("button").forEach((btn) => {
        btn.classList.add("cursor-pointer");
      });
    },
  });
}

export function initNotEvaluatedTable() {
  const tableEl = "#tbl-not-evaluated";

  if ($.fn.DataTable.isDataTable(tableEl)) {
    return $(tableEl).DataTable();
  }

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
  const tableEl = "#tbl-abandoned";

  if ($.fn.DataTable.isDataTable(tableEl)) {
    return $(tableEl).DataTable();
  }

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
        data: "full_name",
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

export function initTableButtonEvents(dept) {
  document.addEventListener("click", function (e) {
    const commentBtn = e.target.closest(".comment-btn");
    if (commentBtn) {
      const teacherId = commentBtn.dataset.id;
      const name = commentBtn.dataset.name;
      const mean = commentBtn.dataset.mean;
      const commentContainer = document.getElementById("comment-container");
      const initialEl = document.getElementById("initial");
      const nameEl = document.getElementById("tName");
      const meanEl = document.getElementById("mean");
      const exportEl = document.querySelector(".btn-export-comment");

      if (initialEl) {
        initialEl.textContent = nameToInitials(name);
      }

      if (nameEl) {
        nameEl.textContent = name;
      }

      if (meanEl) {
        meanEl.textContent = mean;
      }

      exportEl.dataset.id = teacherId;
      exportEl.dataset.name = name;

      if (commentContainer) {
        const currentPeriodId = new URLSearchParams(window.location.search).get(
          "period_id",
        );

        fetch(
          `/Smart-Eval/app/Controllers/reportAnalytics/AnalyticsController.php?action=teacherComments&teacher_id=${teacherId}&dept=${dept}&period_id=${currentPeriodId || ""}`,
        )
          .then((res) => res.json())
          .then((res) => {
            if (res.status === "success") {
              console.log(res.data);
              commentContainer.innerHTML = res.data
                .map(
                  (c) => `
                  <div class="bg-white hover:bg-gray-100 transition border border-gray-200 rounded-lg p-4" id="comment-card">
                    <p style="font-size:11px;color:#94A3B8;margin:0 0 8px;">Evaluator's comment</p>
                    <p id="comment" style="font-size:13px;color:#334155;line-height:1.65;margin:0;">${c}</p>
                    </div>
                  </div>`,
                )
                .join("");
            }
          });
      }

      openModal("commentModal");

      document.getElementById("closeComment").addEventListener("click", () => {
        closeModal("commentModal");
      });
    }

    const exportCommentBtn = e.target.closest(".btn-export-comment");
    if (exportCommentBtn) {
      const teacherId = exportCommentBtn.dataset.id;
      const name = exportCommentBtn.dataset.name;
      const currentPeriodId = new URLSearchParams(window.location.search).get(
        "period_id",
      );

      const url =
        `/Smart-Eval/app/Controllers/reportAnalytics/AnalyticsController.php?` +
        `action=exportComments` +
        `&teacher_id=${teacherId}` +
        `&period_id=${currentPeriodId || ""}` +
        `&dept=${dept}` +
        `&name=${encodeURIComponent(name)}`;

      window.location.href = url;
    }

    const viewBtn = e.target.closest(".btn-view");
    if (viewBtn) {
      const teacherId = viewBtn.dataset.id;
      return;
    }

    const downloadBtn = e.target.closest(".btn-download");
    if (downloadBtn) {
      const teacherId = downloadBtn.dataset.id;
      const name = downloadBtn.dataset.name;

      console.log(teacherId);

      const currentPeriodId = state.lastData?.period?.period_id;

      if (!currentPeriodId) {
        console.error("No evaluation period available for teacher report.");

        showConfirmation({
          title: "Report Unavailable",
          message: "No evaluation period is available for this report.",
          onConfirm: () => {},
        });

        return;
      }

      const url =
        `report/teacher-report` +
        `?teacher_id=${encodeURIComponent(teacherId)}` +
        `&period_id=${encodeURIComponent(currentPeriodId)}` +
        `&teacher_name=${encodeURIComponent(name)}`;

      showConfirmation({
        title: "Download Report ",
        message: `Are you sure you want to download the report for ${name}`,
        onConfirm: () => {
          window.open(url, "_blank");
        },
      });
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
