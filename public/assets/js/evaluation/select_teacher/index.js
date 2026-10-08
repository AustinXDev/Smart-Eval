import { fetchAvailableTeachers, submitTeacherSelection } from "./api/api.js";

import { createSelectionState } from "./components/state.js";

import { createTeacherCard, setCardSelected } from "./components/card.js";

import {
  updateSummaryUI,
  setProceedButtonState,
} from "./components/summary.js";

import { confirm } from "./components/Confimation.js";

document.addEventListener("DOMContentLoaded", async () => {
  // ==========================================================
  // ELEMENTS
  // ==========================================================

  const grid = document.getElementById("teachersGrid");
  const proceedBtn = document.getElementById("proceedBtn");
  const count = document.getElementById("selectedCount");
  const list = document.getElementById("selectedList");
  const summary = document.getElementById("summarySection");

  const searchInput = document.getElementById("teacherSearch");
  const emptyState = document.getElementById("emptyTeachersState");
  const clearSearchBtn = document.getElementById("emptyClearSearchBtn");
  const teacherCount = document.getElementById("teacherCount");

  // ==========================================================
  // STATE
  // ==========================================================

  const selectionState = createSelectionState();

  const department = window.studentContext?.department ?? "";

  let teachers = [];

  // ==========================================================
  // INITIALIZE
  // ==========================================================

  const refreshSummary = () =>
    updateSummaryUI({
      grid,
      count,
      list,
      summary,
      proceedBtn,
      selectionState,
    });

  await loadTeachers();

  bindSearch();

  bindProceedButton();

  // ==========================================================
  // LOAD AVAILABLE TEACHERS
  // ==========================================================

  async function loadTeachers() {
    try {
      const response = await fetchAvailableTeachers(department);

      // Correct error check
      if (response?.status === "error") {
        alert(response.message);
        return;
      }

      const teachersData = response?.data?.teachers ?? [];

      teachers = teachersData.filter((teacher) => teacher.is_active === 1);

      if (teachers.length === 0) {
        showEmptyState();
        return;
      }

      console.log("Teachers:", teachers);

      // Initial render
      renderTeachers(teachers);

      refreshSummary();
    } catch (err) {
      console.error("Failed to load teachers:", err);

      alert("Failed to load available teachers. Please try again.");
    }
  }

  // ==========================================================
  // RENDER TEACHERS
  // ==========================================================

  function renderTeachers(teacherList) {
    // Remove existing cards
    grid.innerHTML = "";

    // No results
    if (teacherList.length === 0) {
      showEmptyState();
      return;
    }

    hideEmptyState();

    // Update count
    updateTeacherCount(teacherList.length);

    teacherList.forEach((teacher) => {
      const card = createTeacherCard(teacher, {
        isSelected: selectionState.isSelected(teacher.teacher_id),

        onToggle: (id, cardEl) => {
          const nowSelected = selectionState.toggle(id);

          setCardSelected(cardEl, nowSelected);

          refreshSummary();
        },
      });

      grid.appendChild(card);
    });
  }

  // ==========================================================
  // SEARCH
  // ==========================================================

  function bindSearch() {
    if (!searchInput) return;

    const debouncedSearch = debounce((value) => {
      searchTeachers(value);
    }, 300);

    searchInput.addEventListener("input", (event) => {
      debouncedSearch(event.target.value);
    });

    // Clear search button
    if (clearSearchBtn) {
      clearSearchBtn.addEventListener("click", () => {
        searchInput.value = "";

        searchInput.focus();

        renderTeachers(teachers);
      });
    }
  }

  // ==========================================================
  // SEARCH TEACHERS
  // ==========================================================

  function searchTeachers(searchTerm) {
    const query = searchTerm.trim().toLowerCase();

    // Empty search = show everything
    if (!query) {
      renderTeachers(teachers);

      return;
    }

    const filteredTeachers = teachers.filter((teacher) => {
      const fullName = String(teacher.full_name ?? "").toLowerCase();

      return fullName.includes(query);
    });

    renderTeachers(filteredTeachers);
  }

  // ==========================================================
  // DEBOUNCE
  // ==========================================================

  function debounce(callback, delay = 300) {
    let timeoutId;

    return (...args) => {
      clearTimeout(timeoutId);

      timeoutId = setTimeout(() => {
        callback(...args);
      }, delay);
    };
  }

  // ==========================================================
  // EMPTY STATE
  // ==========================================================

  function showEmptyState() {
    if (emptyState) {
      emptyState.classList.remove("hidden");
    }

    if (grid) {
      grid.classList.add("hidden");
    }

    if (teacherCount) {
      teacherCount.textContent = "No teachers";
    }
  }

  function hideEmptyState() {
    if (emptyState) {
      emptyState.classList.add("hidden");
    }

    if (grid) {
      grid.classList.remove("hidden");
    }
  }

  // ==========================================================
  // TEACHER COUNT
  // ==========================================================

  function updateTeacherCount(total) {
    if (!teacherCount) return;

    teacherCount.textContent = `${total} ${total === 1 ? "Teacher" : "Teachers"}`;
  }

  // ==========================================================
  // SUBMIT SELECTION
  // ==========================================================

  function bindProceedButton() {
    proceedBtn.addEventListener("click", async () => {
      const selectedIds = selectionState.getAll();

      if (selectedIds.length === 0) {
        alert("Please select at least one teacher.");
        return;
      }

      const selectedTeachers = teachers.filter((teacher) =>
        selectedIds.includes(Number(teacher.teacher_id)),
      );

      const confirmed = await confirm(selectedTeachers);

      if (!confirmed) {
        return;
      }

      setProceedButtonState(proceedBtn, {
        disabled: true,
        text: "Saving...",
      });

      try {
        const data = await submitTeacherSelection(selectedIds);

        if (data.success) {
          window.location.href =
            "/Smart-Eval/views/student/evaluation.view.php";

          return;
        }

        alert(data.error || "Failed to save teacher selection.");

        setProceedButtonState(proceedBtn, {
          disabled: false,
          text: "Continue to Evaluation",
        });
      } catch (err) {
        console.error("Failed to save selection:", err);

        alert("Failed to save teacher selection. Please try again.");

        setProceedButtonState(proceedBtn, {
          disabled: false,
          text: "Continue to Evaluation",
        });
      }
    });
  }
});
