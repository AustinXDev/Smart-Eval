import { openModal, closeModal } from "../../../modal/modal.js";
import { fetchArchiveDetails, fetchRecoveryLogDetails } from "../api/api.js";
import { byId } from "./ui.js";
import * as render from "./archiveRender.js";

let modalRequest = 0;
let returnFocus = null;

const modalId = "archiveDetailsModal";

async function showDetails(id, isLog, trigger) {
  const current = ++modalRequest;
  returnFocus = trigger;

  const title = byId("archive-details-title");
  const description = byId("archive-details-description");
  const content = byId("archive-details-content");

  title.textContent = isLog
    ? "Recovery Activity Details"
    : "Archived Evaluation Details";

  description.textContent = isLog
    ? "Review the recovery operation and its recorded outcome."
    : "Review archived evaluation records and backup information.";

  content.innerHTML = `
    <div class="flex min-h-40 flex-col items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white p-6">
      <i class="fa-solid fa-circle-notch fa-spin text-xl text-violet-600" aria-hidden="true"></i>
      <p class="text-sm text-slate-500">Loading details...</p>
    </div>
  `;

  content.setAttribute("aria-busy", "true");

  openModal(modalId);
  byId("close-archive-details").focus();

  try {
    const { data } = await (isLog
      ? fetchRecoveryLogDetails(id)
      : fetchArchiveDetails(id));

    if (current !== modalRequest) return;

    content.innerHTML = isLog
      ? render.logDetails(data)
      : render.archiveDetails(data);
  } catch (error) {
    if (current !== modalRequest) return;

    const errorBox = document.createElement("div");
    errorBox.className = "rounded-xl border border-rose-200 bg-white p-5";

    const message = document.createElement("p");
    message.className = "text-sm text-rose-700";
    message.textContent = error.message || "Unable to load details.";

    errorBox.appendChild(message);
    content.replaceChildren(errorBox);
  } finally {
    if (current === modalRequest) {
      content.setAttribute("aria-busy", "false");
    }
  }
}

function dismissDetails() {
  ++modalRequest;
  closeModal(modalId);

  if (returnFocus?.isConnected) {
    returnFocus.focus();
  }
}

export function initDetailsModal() {
  const modal = byId(modalId);

  byId("close-archive-details").addEventListener("click", dismissDetails);

  byId("close-archive-details-footer").addEventListener(
    "click",
    dismissDetails,
  );

  modal.addEventListener("click", (event) => {
    if (event.target === modal) {
      dismissDetails();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (modal.classList.contains("hidden")) return;

    if (event.key === "Escape") {
      event.preventDefault();
      dismissDetails();
      return;
    }

    if (event.key === "Tab") {
      const controls = [
        byId("close-archive-details"),
        ...modal.querySelectorAll(
          '#archive-details-content a[href], #archive-details-content button:not([disabled]), #archive-details-content [tabindex="0"]',
        ),
        byId("close-archive-details-footer"),
      ].filter((element) => element && element.getClientRects().length);

      const first = controls[0];
      const last = controls[controls.length - 1];

      if (!first || !last) return;

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      } else if (!modal.contains(document.activeElement)) {
        event.preventDefault();
        first.focus();
      }
    }
  });

  document.addEventListener("click", async (event) => {
    const view = event.target.closest("[data-view-period], [data-view-log]");

    if (!view) return;

    const isLog = view.hasAttribute("data-view-log");

    await showDetails(
      isLog ? view.dataset.viewLog : view.dataset.viewPeriod,
      isLog,
      view,
    );
  });
}
