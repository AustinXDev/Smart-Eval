import { openModal, closeModal } from "../../modal/modal.js";
import { fetchCreatedPeriod } from "./api/api.js";
import { populateQuestionsSet } from "./components/periodQuestionSelect.js";
import { renderUpdateForm } from "./components/periodRender.js";
import {
  requestForceActive,
  requestDeletePeriod,
  requestForceClose,
  initCreatePeriodForm,
  initUpdatePeriodForm,
} from "./components/periodForm.js";
import { initEvaluationTable } from "./table.js";
import { initAutoRefresh, stopAutoRefresh } from "./components/autoRefresh.js";

initCreatePeriodForm();
initUpdatePeriodForm();

initEvaluationTable().then(() => {
  initAutoRefresh(60000);
});

document.addEventListener("visibilitychange", () => {
  if (document.hidden) {
    stopAutoRefresh();
  } else {
    initAutoRefresh(60000);
  }
});

document.addEventListener("click", (e) => {
  const createBtn = e.target.closest(".createPeriodBtn");
  const activeBtn = e.target.closest(".ActiveBtn");
  const deleteBtn = e.target.closest(".deleteBtn");
  const closeBtn = e.target.closest(".closeBtn");
  const downloadBtn = e.target.closest(".downloadBtn");
  const editBtn = e.target.closest(".editBtn");
  const modalCloseBtn = e.target.closest("[data-close-modal]");

  if (createBtn) return handleCreateClick();
  if (activeBtn) return requestForceActive(activeBtn.dataset.id);
  if (deleteBtn) return requestDeletePeriod(deleteBtn.dataset.id);
  if (closeBtn) return requestForceClose(closeBtn.dataset.id);
  if (downloadBtn) return handleDownloadClick(downloadBtn);
  if (editBtn) return handleEditClick(editBtn);
  if (modalCloseBtn) return closeModal(modalCloseBtn.dataset.closeModal);
});

function handleCreateClick() {
  openModal("createPeriodModal");
  populateQuestionsSet();
}

function handleDownloadClick(downloadBtn) {
  const periodId = downloadBtn.dataset.id;
  window.location.href = `/Smart-Eval/public/download_report.php?type=period&period_id=${periodId}`;
}

async function handleEditClick(editBtn) {
  const periodId = editBtn.dataset.id;
  const data = await fetchCreatedPeriod(periodId);

  openModal("updatePeriodModal");
  await populateQuestionsSet("updateQuestionSetSelect");

  renderUpdateForm(periodId, data.period);
}
