import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import {
  fetchAllQuestionSets,
  fetchCreatedPeriod,
} from "../evaluation_periods/question_set_api.js";
import { loadEvaluationPeriods, loadPeriodCard } from "./table.js";

// Populate question set option in the create evaluation periods modal
async function populateQuestionsSet(selectId = "questionSetSelect") {
  const questionSetSelect = document.getElementById(selectId);
  if (!questionSetSelect) return;

  questionSetSelect.innerHTML =
    "<option disabled selected>Select Question Bank</option>";

  const questionSets = await fetchAllQuestionSets();
  //console.log(questionSets);
  questionSets.forEach((qs) => {
    const option = document.createElement("option");
    option.value = String(qs.set_id);
    option.textContent = qs.set_name;
    questionSetSelect.appendChild(option);
  });
}

// Event trigger
document.addEventListener("click", async (e) => {
  const createBtn = e.target.closest(".createPeriodBtn");
  const activeBtn = e.target.closest(".ActiveBtn");
  const deleteBtn = e.target.closest(".deleteBtn");
  const closeBtn = e.target.closest(".closeBtn");
  const viewBtn = e.target.closest(".viewBtn");
  const downloadBtn = e.target.closest(".downloadBtn");
  const editBtn = e.target.closest(".editBtn");
  if (createBtn) {
    openModal("createPeriodModal");
    populateQuestionsSet();
    return;
  }

  if (activeBtn) {
    const period_id = activeBtn.dataset.id;

    showConfirmation({
      title: "Force Active Evaluation",
      message: "Are you sure you want to active this evaluation?",
      onConfirm: () => {
        fetch("/Smart-Eval/app/Controllers/periods/force_active_period.php", {
          method: "POST",
          body: new URLSearchParams({ period_id }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              alert(data.message);
              loadEvaluationPeriods();
              loadPeriodCard();
            } else {
              alert(data.message);
            }
          })
          .catch((err) => console.log(err));
      },
    });

    return;
  }

  if (deleteBtn) {
    const period_id = deleteBtn.dataset.id;

    showConfirmation({
      title: "Delete Period",
      message: "Are you sure you want to delete this period?",
      onConfirm: () => {
        fetch("/Smart-Eval/app/Controllers/periods/delete_period.php", {
          method: "POST",
          body: new URLSearchParams({ period_id }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              alert(data.message);
              loadEvaluationPeriods();
            } else {
              alert(data.message);
            }
          })
          .catch((err) => console.log(err));
      },
    });

    return;
  }

  if (closeBtn) {
    const period_id = closeBtn.dataset.id;

    showConfirmation({
      title: "Force Close Period",
      message: "Are you sure you want to close this period?",
      onConfirm: () => {
        fetch("/Smart-Eval/app/Controllers/periods/force_close_period.php", {
          method: "POST",
          body: new URLSearchParams({ period_id }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              alert(data.message);
              loadEvaluationPeriods();
              loadPeriodCard();
            } else {
              alert(data.message);
            }
          })
          .catch((err) => console.log(err));
      },
    });
  }

  if (downloadBtn) {
    const period_id = downloadBtn.dataset.id;

    let url = `/Smart-Eval/public/download_report.php?type=period&period_id=${period_id}`;

    // trigger download
    window.location.href = url;
  }

  if (editBtn) {
    const periodId = editBtn.dataset.id;
    const data = await fetchCreatedPeriod(periodId);

    openModal("updatePeriodModal");

    await populateQuestionsSet("updateQuestionSetSelect");

    document.getElementById("update_period_id").value = periodId;

    document.getElementById("update_academic_year").value =
      data.period.academic_year;

    document
      .querySelectorAll('input[name="update_semester"]')
      .forEach((radio) => {
        radio.checked = radio.value === data.period.semester;
      });

    document
      .querySelectorAll('input[name="update_department"]')
      .forEach((radio) => {
        radio.checked =
          radio.value.toLowerCase() === data.period.target_dept.toLowerCase();
      });

    document.querySelector('input[name="update_start_date"]').value =
      data.period.start_date;
    document.querySelector('input[name="update_end_date"]').value =
      data.period.end_date;

    const select = document.getElementById("updateQuestionSetSelect");
    if (select) select.value = String(data.period.set_id);
  }
});

document.addEventListener("click", (e) => {
  const closeBtn = e.target.closest("[data-close-modal]");
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});

// Additional event listeners for form submission and other interactions
const createPeriodForm = document.getElementById("createPeriodForm");
createPeriodForm.addEventListener("submit", (e) => {
  e.preventDefault();

  //gather form data
  const formData = new FormData(createPeriodForm);

  showConfirmation({
    title: "Confirm Creation",
    message: "Are you sure you want to create this evaluation period?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/Controllers/periods/create_period.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          console.log(data);
          if (data.status === "success") {
            alert(data.message);
            loadEvaluationPeriods();
            closeModal("createPeriodModal");
            createPeriodForm.reset();
          } else {
            alert(data.message);
          }
        })
        .catch((err) => {
          console.error("Error while creating evaluation period", err);
          alert("An error occured. Please try again later.");
        });
    },
  });
});

const updatePeriodForm = document.getElementById("updatePeriodForm");

updatePeriodForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(updatePeriodForm);

  showConfirmation({
    title: "Update Evaluation Period",
    message: "Are you sure you want to update?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/Controllers/periods/update_period.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          console.log(data);
          if (data.status === "success") {
            alert(data.message);
            loadEvaluationPeriods();
            closeModal("updatePeriodModal");
            updatePeriodForm.reset();
          } else {
            alert(data.message);
          }
        })
        .catch((err) => {
          console.error("Error while updating evaluation period", err);
          alert("An error occured. Please try again later.");
        });
    },
  });
});

// Auto load active evaluation
let isUpdating = false;

async function updatePeriodAndReload() {
  if (isUpdating) return;
  isUpdating = true;

  try {
    // 1. Wait for the PHP to finish its work
    const response = await fetch("/Smart-Eval/scripts/auto_update.php");
    const result = await response.text(); // Or .json() if your PHP outputs json

    console.log("PHP Script result:", result);

    // 2. ONLY THEN reload the UI
    await loadEvaluationPeriods();
    await loadPeriodCard();
  } catch (err) {
    console.log("Error updating periods:", err);
  } finally {
    isUpdating = false;
  }
}

// Load page
$(document).ready(() => {
  // Run immediately
  updatePeriodAndReload();

  // Run every 30 seconds
  setInterval(() => {
    updatePeriodAndReload();
    console.log("refreshed");
  }, 30000);
});
