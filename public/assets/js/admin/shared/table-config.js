import { getRatingBadge } from "./utils.js";

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
            <button class="btn-act btn-act--green btn-download" data-id="${row.teacher_id}" title="Download">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
            </button>
            <button class="btn-act btn-act--amber btn-mail" data-id="${row.teacher_id}" data-name="${row.full_name}" title="Send Email">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
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
      {
        data: null,
        title: "Action",
        orderable: false,
        className: "text-center",
        render: (v, type, row) => `
          <div class="flex items-center justify-center gap-1.5">
            <button 
              class="btn-act btn-act--amber btn-notify" 
              data-id="${row.student_id}"
              data-email="${row.email}"
              data-name="${row.full_name}"
              title="Send Reminder"
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
            </button>
          </div>`,
      },
    ],
    order: [[1, "asc"]],
  });
}

// ✅ fixed — was using dummy avatarCell/statusPill/notifyBtn that don't exist
// update the data: keys below to match whatever your backend actually returns
export function initAbandonedTable() {
  return $("#tbl-abandoned").DataTable({
    pageLength: 8,
    lengthChange: false,
    searching: true,
    dom: "tip",
    language: {
      emptyTable: "No abandoned evaluations at this time.", // ✅ friendly empty message
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
      {
        data: null,
        title: "Action",
        orderable: false,
        className: "text-center",
        render: (v, type, row) => `
          <div class="flex items-center justify-center gap-1.5">
            <button 
              class="btn-act btn-act--amber btn-notify"
              data-id="${row.student_id}"
              data-email="${row.email}"
              data-name="${row.full_name}"
              title="Send Reminder"
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
            </button>
          </div>`,
      },
    ],
    order: [[1, "asc"]],
  });
}
