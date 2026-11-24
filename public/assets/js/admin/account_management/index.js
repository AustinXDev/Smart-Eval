import { escapeHTML } from "../../services/escape.js";
import { openModal, closeModal } from "../../modal/modal.js";
import { createDataTable } from "../shared/datatable_config.js";
import { countUp } from "../shared/count_up.js";
import { setLoading } from "../shared/Loader.js";
import { logout } from "../shared/logout.js";
import { listAccounts, deleteAccount, pinAction, isPinRequired } from "./api.js";
import { initAddForm, initEditForm, initPasswordToggles, openAddForm, openEditForm } from "./form.js";

// StatusModal is loaded globally by assets/js/common/modal.js.
window.logout = logout;

let table;
let accounts = [];

const el = {
  search: () => document.getElementById("searchBox"),
  clearSearch: () => document.getElementById("clear-search"),
  role: () => document.getElementById("roleFilter"),
  status: () => document.getElementById("statusFilter"),
  count: () => document.getElementById("table-count"),
};

const STATUS = {
  active: { label: "Active", badge: "bg-emerald-50 text-emerald-700 ring-emerald-600/15", dot: "bg-emerald-500" },
  inactive: { label: "Deactivated", badge: "bg-rose-50 text-rose-700 ring-rose-600/15", dot: "bg-rose-500" },
};

const ROLE = {
  "super admin": "bg-[#6010FF]/10 text-[#6010FF] ring-[#6010FF]/15",
  "department head": "bg-blue-50 text-blue-700 ring-blue-600/15",
};

const dateFmt = new Intl.DateTimeFormat(undefined, { month: "short", day: "numeric", year: "numeric" });

function parseDate(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime()) ? null : date;
}

const initials = (name) =>
  String(name || "?").trim().split(/\s+/).slice(0, 2).map((p) => p[0]?.toUpperCase() ?? "").join("") || "?";

const statusBadge = (status) => {
  const s = STATUS[status] ?? STATUS.inactive;
  return `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${s.badge}">
    <span class="h-1.5 w-1.5 rounded-full ${s.dot}"></span>${s.label}</span>`;
};

/* ---------- Table ---------- */

function renderRow(account) {
  const id = escapeHTML(String(account.admin_id));
  const name = escapeHTML(account.full_name || account.username);
  const created = parseDate(account.created_at);

  const iconButton = (cls, icon, label, hover, disabled = false, title = label) => `
    <button type="button" data-admin-id="${id}" title="${escapeHTML(title)}" aria-label="${label} ${name}" ${disabled ? "disabled" : ""}
      class="${cls} flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors duration-150 ${hover} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-slate-200 disabled:hover:bg-white disabled:hover:text-slate-500">
      <i class="fas ${icon} text-xs" aria-hidden="true"></i>
    </button>`;

  return [
    `<span class="flex items-center gap-3">
      <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#6010FF]/10 text-xs font-bold text-[#6010FF] ring-1 ring-[#6010FF]/15" aria-hidden="true">${escapeHTML(initials(account.full_name || account.username))}</span>
      <span class="min-w-0">
        <span class="ms-name block truncate font-semibold text-slate-900">${name}</span>
        ${account.is_self ? '<span class="mt-0.5 inline-flex rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">You</span>' : ""}
      </span>
    </span>`,
    `<span class="ms-id font-medium text-slate-600">${escapeHTML(account.username)}</span>`,
    `<span class="text-slate-600">${escapeHTML(account.email)}</span>`,
    `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${ROLE[account.role] ?? "bg-slate-50 text-slate-600 ring-slate-500/10"}">${escapeHTML(account.role_label)}</span>`,
    statusBadge(account.status),
    created
      ? `<time datetime="${escapeHTML(account.created_at)}" title="${escapeHTML(created.toLocaleString())}" class="tabular-nums text-slate-600">${dateFmt.format(created)}</time>`
      : '<span class="text-slate-400">—</span>',
    `<div class="flex items-center justify-end gap-1.5">
      ${iconButton("viewBtn", "fa-eye", "View", "hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600")}
      ${iconButton("editBtn", "fa-pen", "Edit", "hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]")}
      ${iconButton("deleteBtn", "fa-trash-alt", "Delete", "hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600", account.is_self, account.is_self ? "You can't delete your own account" : "Delete")}
    </div>`,
  ];
}

function styleRow(row) {
  row.classList.add("border-b", "border-slate-100", "transition-colors", "duration-150", "hover:bg-slate-50/70");
  row.querySelectorAll("td").forEach((td) => td.classList.add("px-5", "py-3.5", "align-middle"));
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

function filtered() {
  const q = el.search().value.trim().toLowerCase();
  const role = el.role().value;
  const status = el.status().value;

  return accounts.filter(
    (a) =>
      (!role || a.role === role) &&
      (!status || a.status === status) &&
      (!q || [a.full_name, a.username, a.email].some((v) => String(v ?? "").toLowerCase().includes(q))),
  );
}

function draw() {
  const rows = filtered();
  const filtering = el.search().value.trim() || el.role().value || el.status().value;

  table.settings()[0].oLanguage.sEmptyTable = filtering
    ? emptyState("fa-magnifying-glass", "No accounts match your search or filters", "Try a different name, username, role or status.", true)
    : emptyState("fa-users-gear", "No administrator accounts", "Add an account to get started.");

  table.clear().rows.add(rows.map(renderRow)).draw();
  el.count().textContent = rows.length;
}

function renderCounts(counts) {
  const total = Number(counts.total) || 0;
  countUp("total-accounts", total);
  countUp("total-active", Number(counts.active) || 0);
  countUp("total-inactive", Number(counts.inactive) || 0);

  const share = (n) => (total ? `${Math.round((n / total) * 100)}%` : "");
  document.getElementById("total-accounts-share").textContent = `${Number(counts.super_admins) || 0} super admin${counts.super_admins === 1 ? "" : "s"}`;
  document.getElementById("total-active-share").textContent = share(Number(counts.active) || 0);
  document.getElementById("total-inactive-share").textContent = share(Number(counts.inactive) || 0);
}

async function load() {
  try {
    const result = await listAccounts();
    accounts = result.data?.accounts ?? [];
    renderCounts(result.data?.counts ?? {});
    draw();
  } catch (error) {
    if (isPinRequired(error)) return window.location.reload();
    table.settings()[0].oLanguage.sEmptyTable = emptyState("fa-circle-exclamation", "Accounts could not be loaded", escapeHTML(error.message || "Please refresh the page."));
    table.clear().draw();
  }
}

/* ---------- View / delete ---------- */

const byId = (id) => accounts.find((a) => String(a.admin_id) === String(id));

function showDetails(account) {
  const set = (key, value) => {
    const node = document.querySelector(`#viewAccountModal [data-view="${key}"]`);
    if (node) node.textContent = value;
  };
  const created = parseDate(account.created_at);
  const updated = parseDate(account.updated_at);

  set("initials", initials(account.full_name || account.username));
  set("full_name", account.full_name || account.username);
  set("username", account.username);
  set("email", account.email);
  set("role_label", account.role_label);
  set("access", account.role === "super admin" ? "Full access, including this page" : "Dashboards, reports and monitoring");
  set("created_at", created ? created.toLocaleString() : "—");
  set("updated_at", updated ? updated.toLocaleString() : "—");
  document.querySelector('#viewAccountModal [data-view="status_badge"]').innerHTML = statusBadge(account.status);
  document.getElementById("viewEditBtn").dataset.adminId = account.admin_id;

  openModal("viewAccountModal");
}

function requestDelete(account) {
  StatusModal.confirm(
    "Delete Account",
    `Permanently delete ${account.full_name || account.username} (${account.username})? They will be signed out and can't sign in again. This can't be undone.`,
    async () => {
      setLoading("Deleting…");
      try {
        const result = await deleteAccount(account.admin_id);
        StatusModal.show("Account deleted", result.message || "Account deleted.", "success");
        load();
      } catch (error) {
        if (isPinRequired(error)) return window.location.reload();
        StatusModal.show("Not deleted", error.message || "Something went wrong. Please try again.", "error");
      } finally {
        setLoading("", false);
      }
    },
  );
}

async function lockPage() {
  try {
    await pinAction({ action: "lock" });
  } finally {
    window.location.reload();
  }
}

/* ---------- Start ---------- */

$(function () {
  table = createDataTable("#accountsTable", {
    data: [],
    pageLength: 10,
    ordering: false,
    searching: false,
    createdRow: styleRow,
    columnDefs: [{ className: "whitespace-nowrap", targets: [1, 3, 4, 5, 6] }],
    language: {
      emptyTable: '<div class="py-12 text-center text-sm text-slate-400">Loading accounts…</div>',
      info: "Showing _START_ to _END_ of _TOTAL_ accounts",
      infoEmpty: "Showing 0 to 0 of 0 accounts",
      infoFiltered: "",
    },
  });

  initAddForm({ onSaved: load });
  initEditForm({ onSaved: load });
  initPasswordToggles();
  load();

  // Filters
  let timer;
  el.search().addEventListener("input", () => {
    el.clearSearch().classList.toggle("hidden", !el.search().value);
    el.clearSearch().classList.toggle("flex", !!el.search().value);
    clearTimeout(timer);
    timer = setTimeout(draw, 200);
  });
  el.clearSearch().addEventListener("click", () => {
    el.search().value = "";
    el.clearSearch().classList.add("hidden");
    el.clearSearch().classList.remove("flex");
    draw();
    el.search().focus();
  });
  el.role().addEventListener("change", draw);
  el.status().addEventListener("change", draw);

  const resetFilters = () => {
    el.search().value = "";
    el.role().value = "";
    el.status().value = "";
    el.clearSearch().classList.add("hidden");
    draw();
  };
  document.getElementById("reset-filters").addEventListener("click", resetFilters);

  document.getElementById("lockPageBtn").addEventListener("click", lockPage);

  document.addEventListener("click", (event) => {
    const target = event.target;
    const button = target.closest(".viewBtn, .editBtn, .deleteBtn, #viewEditBtn");
    if (target.closest("[data-clear-filters]")) return resetFilters();
    if (target.closest(".add-btn")) return openAddForm();
    if (target.closest("[data-close-modal]")) return closeModal(target.closest("[data-close-modal]").dataset.closeModal);
    if (!button || button.disabled) return;

    const account = byId(button.dataset.adminId);
    if (!account) return;

    if (button.classList.contains("viewBtn")) return showDetails(account);
    if (button.classList.contains("editBtn")) return openEditForm(account);
    if (button.id === "viewEditBtn") {
      closeModal("viewAccountModal");
      return openEditForm(account);
    }
    if (button.classList.contains("deleteBtn")) return requestDelete(account);
  });
});
