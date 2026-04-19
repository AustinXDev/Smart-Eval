import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import { fetchAllQuestionSets } from "../evaluation_periods/question_set_api.js";
import { loadEvaluationPeriods, loadPeriodCard } from "./table.js";

// Populate question set option in the create evaluation periods modal
async function populateQuestionsSet() {
  const questionSetSelect = document.getElementById("questionSetSelect");
  if (!questionSetSelect) return;

  questionSetSelect.innerHTML =
    "<option disabled selected>Select Question Bank</option>";

  const questionSets = await fetchAllQuestionSets();
  //console.log(questionSets);
  questionSets.forEach((qs) => {
    const option = document.createElement("option");
    option.value = qs.set_id;
    option.textContent = qs.set_name;
    questionSetSelect.appendChild(option);
  });
}

// Event trigger
document.addEventListener("click", (e) => {
  const createBtn = e.target.closest(".createPeriodBtn");
  const activeBtn = e.target.closest(".ActiveBtn");
  const deleteBtn = e.target.closest(".deleteBtn");
  const closeBtn = e.target.closest(".closeBtn");
  const viewBtn = e.target.closest(".viewBtn");

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

  if (viewBtn) {
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

// Auto load active evaluation
let isUpdating = false;

async function updatePeriodAndReload() {
  if (isUpdating) return; // Prevent overlapping calls
  isUpdating = true;

  try {
    await fetch("/Smart-Eval/scripts/auto_active_period.php");

    // Reload UI components
    loadEvaluationPeriods();
    loadPeriodCard();
  } catch (err) {
    console.log("Error updating periods:", err);
  }

  isUpdating = false;
}

// Load page
$(document).ready(() => {
  // Run immediately
  updatePeriodAndReload();

  // Run every 30 seconds
  setInterval(() => {
    updatePeriodAndReload();
  }, 30000);
});
