const studentEvaluation = {
  currentIndex: 0,
  totalTeachers: 0,
  teachers: [],
  questions: [],
  answers: {}, // store answers per teacher load_id
  currentTeacher: null,
  periodId: null,

  /**
   * Fetch current teacher and previous answers from backend
   */
  async fetchTeacher() {
    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=get_teacher",
      );
      const data = await res.json();

      if (!data.success) {
        console.error("Error fetching teacher:", data.error);
        alert(data.error);
        return;
      }

      this.currentIndex = data.current_index;
      this.totalTeachers = data.total_teachers;
      this.currentTeacher = data.teacher;
      this.periodId = data.period_id;

      console.log("Teacher Data:", {
        currentIndex: this.currentIndex,
        totalTeachers: this.totalTeachers,
        progressPercentage: data.progress_percentage,
      });

      // **CRITICAL FIX: Store previous answers from backend**
      // This ensures answers persist even after page reload
      if (!this.answers[data.teacher.load_id]) {
        this.answers[data.teacher.load_id] = {};
      }

      // Merge backend answers with local ones
      this.answers[data.teacher.load_id] = {
        ...this.answers[data.teacher.load_id],
        ...data.previous_answers,
      };

      // Render teacher info
      this.renderTeacher();

      // Render questions with previous answers highlighted
      this.renderQuestions(data.previous_answers);

      // Update navigation buttons
      this.updateNavigationButtons();
    } catch (error) {
      console.error("Fetch teacher error:", error);
      alert("Failed to load teacher information");
    }
  },

  /**
   * Fetch all questions from backend
   */
  async fetchQuestions() {
    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=get_questions",
      );
      const data = await res.json();

      if (!data.success) {
        console.error("Error fetching questions:", data.error);
        alert(data.error);
        return;
      }

      this.questions = data.questions;
    } catch (error) {
      console.error("Fetch questions error:", error);
      alert("Failed to load questions");
    }
  },

  /**
   * Render teacher information in UI
   */
  renderTeacher() {
    // Update teacher name
    const teacherNameEl = document.querySelector("[data-teacher-name]");
    if (teacherNameEl) {
      teacherNameEl.textContent = this.currentTeacher.full_name;
    }

    // Update teacher department if exists
    const teacherDeptEl = document.querySelector("[data-teacher-dept]");
    if (teacherDeptEl) {
      teacherDeptEl.textContent = this.currentTeacher.department;
    }

    // Update teacher email if exists
    const teacherEmailEl = document.querySelector("[data-teacher-email]");
    if (teacherEmailEl) {
      teacherEmailEl.href = `mailto:${this.currentTeacher.email}`;
      teacherEmailEl.textContent = this.currentTeacher.email;
    }

    // ✅ FIX: Update progress bar correctly
    this.updateProgressBar();

    // Update progress text if exists
    const progressText = document.querySelector("[data-progress-text]");
    if (progressText) {
      progressText.textContent = `${this.currentIndex + 1} of ${this.totalTeachers}`;
    }
  },

  /**
   * ✅ NEW: Proper progress bar calculation
   * The formula: (currentIndex + 1) / totalTeachers * 100
   * This is correct because:
   * - currentIndex is 0-based (0, 1, 2, ...)
   * - We add 1 to show which teacher we're on (1st, 2nd, 3rd, ...)
   * Example: Teacher 2 of 5 = (1 + 1) / 5 * 100 = 40%
   */
  updateProgressBar() {
    const progressBar = document.getElementById("progressBar");

    if (!progressBar) {
      console.warn("Progress bar element not found");
      return;
    }

    // Calculate percentage
    const percentage = Math.round(
      ((this.currentIndex + 1) / this.totalTeachers) * 100,
    );

    console.log("Progress Update:", {
      currentIndex: this.currentIndex,
      totalTeachers: this.totalTeachers,
      percentage: percentage,
      calculation: `(${this.currentIndex} + 1) / ${this.totalTeachers} * 100 = ${percentage}%`,
    });

    // Update progress bar width
    progressBar.style.width = percentage + "%";

    // Update progress text
    const progressLabel = document.querySelector("[data-progress-label]");
    if (progressLabel) {
      progressLabel.textContent = `Teacher ${this.currentIndex + 1} of ${this.totalTeachers} (${percentage}%)`;
    }

    // Update aria attributes for accessibility
    progressBar.setAttribute("aria-valuenow", percentage);
    progressBar.setAttribute("aria-label", `Progress: ${percentage}%`);
  },

  /**
   * Render questions with rating buttons and restore previous answers
   */
  renderQuestions(previousAnswers = {}) {
    const container = document.getElementById("questionsContainer");
    if (!container) {
      console.error("Questions container not found");
      return;
    }

    container.innerHTML = "";

    const labels = [
      { text: "Strongly Disagree", value: 1, color: "red" },
      { text: "Disagree", value: 2, color: "orange" },
      { text: "Neutral", value: 3, color: "gray" },
      { text: "Agree", value: 4, color: "blue" },
      { text: "Strongly Agree", value: 5, color: "green" },
    ];

    this.questions.forEach((q, index) => {
      // Get previous answer for this question
      const selected = previousAnswers[q.question_id]?.score;
      const comment = previousAnswers[q.question_id]?.comment || "";

      // Build rating options HTML
      const optionsHTML = labels
        .map(
          (label) => `
        <label class="cursor-pointer">
          <input 
            type="radio" 
            name="q${q.question_id}" 
            value="${label.value}" 
            class="hidden peer"
            data-question-id="${q.question_id}"
            ${selected == label.value ? "checked" : ""}
          >
          <div class="px-4 py-2 rounded-full border border-gray-300 
                      text-gray-600 bg-white transition-all duration-200
                      hover:border-${label.color}-400 hover:text-${label.color}-500 hover:scale-105
                      peer-checked:bg-${label.color}-500 
                      peer-checked:text-white 
                      peer-checked:border-${label.color}-500 
                      peer-checked:shadow-md
                      text-xs sm:text-sm">
            ${label.text}
          </div>
        </label>
      `,
        )
        .join("");

      // Build question HTML
      const questionHTML = `
        <div class="question" data-qid="${q.question_id}">
          <p class="font-medium text-gray-700 mb-3">
            ${index + 1}. ${this.escapeHtml(q.question_text)}
          </p>
          <div class="flex flex-wrap gap-3 text-sm mb-4">
            ${optionsHTML}
          </div>
          <div>
            <label class="text-xs text-gray-600">Additional Comment (Optional)</label>
            <textarea 
              class="comment-box w-full mt-1 border rounded-lg p-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none resize-none"
              data-question-id="${q.question_id}"
              maxlength="300"
              rows="2"
              placeholder="Share your thoughts...">${comment}</textarea>
            <p class="text-xs text-gray-400 text-right mt-1">
              <span class="char-count">${comment.length}</span> / 300 characters
            </p>
          </div>
        </div>
      `;

      container.innerHTML += questionHTML;
    });

    // Add event listeners to all radio buttons and comment boxes
    this.attachEventListeners();
  },

  /**
   * Attach event listeners to radio buttons and textareas
   */
  attachEventListeners() {
    // Radio button change listeners
    document.querySelectorAll("input[type='radio']").forEach((radio) => {
      radio.addEventListener("change", () => {
        this.collectAnswers();
        this.validateAllAnswered();
      });
    });

    // Comment textarea listeners
    document.querySelectorAll(".comment-box").forEach((textarea) => {
      textarea.addEventListener("input", (e) => {
        // Update character count
        const charCount = e.target.parentElement.querySelector(".char-count");
        if (charCount) {
          charCount.textContent = e.target.value.length;
        }
        this.collectAnswers();
      });
    });

    // Feedback textarea listener
    const feedbackEl = document.getElementById("feedback");
    if (feedbackEl) {
      feedbackEl.addEventListener("input", (e) => {
        const charCountEl = document.getElementById("charCount");
        if (charCountEl) {
          charCountEl.textContent = e.target.value.length;
        }
      });
    }
  },

  /**
   * Collect all current answers from the form
   */
  collectAnswers() {
    const answers = {};

    document.querySelectorAll(".question").forEach((q) => {
      const question_id = q.dataset.qid;
      const selected = q.querySelector("input[type='radio']:checked");
      const commentInput = q.querySelector(".comment-box");

      if (selected || commentInput) {
        answers[question_id] = {
          score: selected ? parseInt(selected.value) : null,
          comment: commentInput ? commentInput.value.trim() : "",
        };
      }
    });

    // Store in local variable
    this.answers[this.currentTeacher.load_id] = answers;
    return answers;
  },

  /**
   * Validate that all questions have been answered
   */
  validateAllAnswered() {
    const allAnswered = this.questions.every((q) => {
      const answers = this.answers[this.currentTeacher.load_id] || {};
      return answers[q.question_id] && answers[q.question_id].score;
    });

    // Update submit button state
    const nextBtn = document.getElementById("nextBtn");
    if (nextBtn) {
      nextBtn.disabled = !allAnswered;
      nextBtn.classList.toggle("opacity-50", !allAnswered);
      nextBtn.classList.toggle("cursor-not-allowed", !allAnswered);
    }

    return allAnswered;
  },

  /**
   * Submit evaluation for current teacher
   */
  async submitEvaluation() {
    // Collect current answers
    const answers = this.collectAnswers();

    // Validate all questions answered
    if (!this.validateAllAnswered()) {
      alert("Please answer all questions before proceeding");
      return;
    }

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=submit_evaluation",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            load_id: this.currentTeacher.load_id,
            answers: answers,
          }),
        },
      );

      const data = await res.json();

      if (!data.success) {
        console.error("Submit error:", data.error);
        alert(data.error);
        return;
      }

      console.log(
        "Evaluation submitted successfully for teacher:",
        this.currentTeacher.full_name,
      );
      await this.nextTeacher();
    } catch (error) {
      console.error("Submit evaluation error:", error);
      alert("Failed to submit evaluation");
    }
  },

  /**
   * Navigate to next teacher
   */
  async nextTeacher() {
    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=next_teacher",
        {
          method: "POST",
        },
      );
      const data = await res.json();

      if (!data.success) {
        console.error("Next teacher error:", data.error);
        alert(data.error);
        return;
      }

      if (data.is_last) {
        // All teachers done - show completion button
        console.log("All teachers evaluated");
        this.enableCompleteButton();
        alert(
          "You have completed all evaluations. Click 'Complete Evaluation' to finish.",
        );
        return;
      }

      // Load next teacher
      await this.fetchTeacher();
      this.validateAllAnswered();
    } catch (error) {
      console.error("Next teacher error:", error);
      alert("Failed to proceed to next teacher");
    }
  },

  /**
   * Navigate to previous teacher
   */
  async previousTeacher() {
    // Save current answers before going back
    this.collectAnswers();

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=previous_teacher",
        {
          method: "POST",
        },
      );
      const data = await res.json();

      if (!data.success) {
        console.error("Previous teacher error:", data.error);
        alert(data.error);
        return;
      }

      // Load previous teacher
      await this.fetchTeacher();
      this.validateAllAnswered();
    } catch (error) {
      console.error("Previous teacher error:", error);
      alert("Failed to go to previous teacher");
    }
  },

  /**
   * Check completion status
   */
  async checkCompletion() {
    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=check_completion",
      );
      const data = await res.json();

      if (data.success && data.all_completed) {
        this.enableCompleteButton();
      }

      return data;
    } catch (error) {
      console.error("Check completion error:", error);
    }
  },

  /**
   * Complete all evaluations
   */
  async completeEvaluation() {
    if (
      !confirm(
        "Are you sure you want to complete all evaluations? You will not be able to edit after this.",
      )
    ) {
      return;
    }

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=complete_evaluation",
        {
          method: "POST",
        },
      );
      const data = await res.json();

      if (!data.success) {
        console.error("Complete error:", data.error);
        alert(data.error);
        return;
      }

      // Redirect to completion page
      window.location.href = data.redirect;
    } catch (error) {
      console.error("Complete evaluation error:", error);
      alert("Failed to complete evaluation");
    }
  },

  /**
   * Enable complete button
   */
  enableCompleteButton() {
    const completeBtn = document.getElementById("completeBtn");
    if (completeBtn) {
      completeBtn.disabled = false;
      completeBtn.classList.remove("bg-gray-300", "cursor-not-allowed");
      completeBtn.classList.add("bg-purple-700", "hover:bg-purple-800");
      completeBtn.textContent = "✓ Complete Evaluation";
      completeBtn.addEventListener("click", () => {
        this.completeEvaluation();
      });
    }
  },

  /**
   * Update navigation button states
   */
  updateNavigationButtons() {
    const prevBtn = document.getElementById("previousBtn");
    const nextBtn = document.getElementById("nextBtn");

    // Disable previous if on first teacher
    if (prevBtn) {
      prevBtn.disabled = this.currentIndex === 0;
      prevBtn.classList.toggle("opacity-50", this.currentIndex === 0);
      prevBtn.classList.toggle("cursor-not-allowed", this.currentIndex === 0);
    }

    // Update next button text
    if (nextBtn) {
      const isLast = this.currentIndex === this.totalTeachers - 1;
      nextBtn.textContent = isLast ? "Complete Evaluation" : "Next Teacher";
    }
  },

  /**
   * Escape HTML special characters
   */
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  },
};

/**
 * Initialize on page load
 */
document.addEventListener("DOMContentLoaded", async () => {
  console.log("=== Initializing Student Evaluation ===");

  try {
    // Load questions first (they stay the same)
    await studentEvaluation.fetchQuestions();
    console.log("Questions loaded:", studentEvaluation.questions.length);

    // Load teacher and previous answers
    await studentEvaluation.fetchTeacher();
    console.log("Teacher loaded:", studentEvaluation.currentTeacher.full_name);

    // Setup event listeners
    const nextBtn = document.getElementById("nextBtn");
    const prevBtn = document.getElementById("previousBtn");
    const completeBtn = document.getElementById("completeBtn");

    if (nextBtn) {
      nextBtn.addEventListener("click", () => {
        studentEvaluation.submitEvaluation();
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener("click", () => {
        studentEvaluation.previousTeacher();
      });
    }

    if (completeBtn) {
      completeBtn.addEventListener("click", () => {
        studentEvaluation.completeEvaluation();
      });
    }

    // Validate answers on page load
    studentEvaluation.validateAllAnswered();

    console.log("=== Evaluation Initialized Successfully ===");
  } catch (error) {
    console.error("Initialization error:", error);
    alert("Failed to initialize evaluation");
  }
});
