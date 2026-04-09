/**
 * evaluation.js
 * Handles the full student evaluation flow.
 * Single source of truth: evaluation.php handles all actions.
 */

const studentEvaluation = {
  currentIndex: 0,
  totalTeachers: 0,
  questions: [],
  answers: {},
  currentTeacher: null,
  periodId: null,
  isSubmitted: false,

  // ── Review modal state ─────────────────────────────────────────────────────
  _reviewIndex: 0,
  _reviewData: [],

  // ── Fetch current teacher + previous answers ───────────────────────────────
  async fetchTeacher() {
    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=get_teacher",
      );
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!data.success) {
        this._handleFatalError(data.error ?? "Failed to load teacher.");
        return;
      }

      this.currentIndex = data.current_index;
      this.totalTeachers = data.total_teachers;
      this.currentTeacher = data.teacher;
      this.periodId = data.period_id;
      this.isSubmitted = data.is_submitted === 1;

      const loadId = data.teacher.load_id;
      this.answers[loadId] = {
        ...(this.answers[loadId] ?? {}),
        ...data.previous_answers,
      };

      this.renderTeacher();
      this.renderQuestions(data.previous_answers);
      this.updateNavigationButtons();

      if (this.isSubmitted) {
        this._showSubmittedNotice();
      }

      this.validateAllAnswered();
      await this.checkCompletion();
    } catch (err) {
      console.error("fetchTeacher:", err);
      alert("Failed to load teacher information. Please refresh.");
    }
  },

  // ── Fetch questions (called once on init) ─────────────────────────────────
  async fetchQuestions() {
    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=get_questions",
      );
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!data.success) {
        this._handleFatalError(data.error ?? "Failed to load questions.");
        return;
      }

      this.questions = data.questions;
    } catch (err) {
      console.error("fetchQuestions:", err);
      alert("Failed to load questions. Please refresh.");
    }
  },

  // ── Render teacher card ────────────────────────────────────────────────────
  renderTeacher() {
    const t = this.currentTeacher;

    const nameEl = document.querySelector("[data-teacher-name]");
    if (nameEl) nameEl.textContent = t.full_name;

    const deptEl = document.querySelector("[data-teacher-dept]");
    if (deptEl) deptEl.textContent = t.department;

    const emailEl = document.querySelector("[data-teacher-email]");
    if (emailEl) {
      emailEl.href = `mailto:${t.email}`;
      emailEl.textContent = t.email;
    }

    const imgEl = document.querySelector("[data-teacher-image]");
    if (imgEl) {
      imgEl.src = `/Smart-Eval/uploads/teachers/${t.image_path || "default.png"}`;
      imgEl.alt = t.full_name;
    }

    this._updateProgressBar();

    const progressText = document.querySelector("[data-progress-text]");
    if (progressText) {
      progressText.textContent = `${this.currentIndex + 1} of ${this.totalTeachers}`;
    }
  },

  // ── Progress bar ──────────────────────────────────────────────────────────
  _updateProgressBar() {
    const bar = document.getElementById("progressBar");
    if (!bar) return;

    const pct = Math.round(
      ((this.currentIndex + 1) / this.totalTeachers) * 100,
    );
    bar.style.width = pct + "%";
    bar.setAttribute("aria-valuenow", pct);
    bar.setAttribute("aria-label", `Progress: ${pct}%`);

    const label = document.querySelector("[data-progress-label]");
    if (label) {
      label.textContent = `Teacher ${this.currentIndex + 1} of ${this.totalTeachers} (${pct}%)`;
    }
  },

  // ── Render questions grouped by category ──────────────────────────────────
  renderQuestions(previousAnswers = {}) {
    const container = document.getElementById("questionsContainer");
    if (!container) {
      console.error("questionsContainer not found");
      return;
    }

    container.innerHTML = "";

    const LABELS = [
      { text: "Strongly Disagree", value: 1, color: "red" },
      { text: "Disagree", value: 2, color: "orange" },
      { text: "Neutral", value: 3, color: "gray" },
      { text: "Agree", value: 4, color: "blue" },
      { text: "Strongly Agree", value: 5, color: "green" },
    ];

    const grouped = {};
    this.questions.forEach((q) => {
      const cat = q.category || "General";
      (grouped[cat] = grouped[cat] ?? []).push(q);
    });

    let globalIndex = 0;

    Object.entries(grouped).forEach(([category, questions]) => {
      const catHeader = document.createElement("div");
      catHeader.className = "mb-2 mt-6 first:mt-0";
      catHeader.innerHTML = `
        <h3 class="text-sm font-semibold text-purple-700 uppercase tracking-wide border-b border-purple-200 pb-1">
          ${this._escapeHtml(category)}
        </h3>`;
      container.appendChild(catHeader);

      questions.forEach((q) => {
        globalIndex++;
        const prevScore = previousAnswers[q.question_id]?.score ?? null;
        const prevComment = previousAnswers[q.question_id]?.comment ?? "";

        const optionsHTML = LABELS.map(
          (label) => `
          <label class="cursor-pointer">
            <input
              type="radio"
              name="q${q.question_id}"
              value="${label.value}"
              class="hidden peer"
              data-question-id="${q.question_id}"
              ${prevScore == label.value ? "checked" : ""}
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
          </label>`,
        ).join("");

        const div = document.createElement("div");
        div.className = "question mb-6";
        div.dataset.qid = q.question_id;
        div.innerHTML = `
          <p class="font-medium text-gray-700 mb-3">
            ${globalIndex}. ${this._escapeHtml(q.question_text)}
          </p>
          <div class="flex flex-wrap gap-3 text-sm mb-4">${optionsHTML}</div>
          <div>
            <label class="text-xs text-gray-600">Additional Comment (Optional)</label>
            <textarea
              class="comment-box w-full mt-1 border rounded-lg p-2 text-sm
                     focus:ring-2 focus:ring-purple-500 focus:outline-none resize-none"
              data-question-id="${q.question_id}"
              maxlength="300"
              rows="2"
              placeholder="Share your thoughts..."
            >${this._escapeHtml(prevComment)}</textarea>
            <p class="text-xs text-gray-400 text-right mt-1">
              <span class="char-count">${prevComment.length}</span> / 300 characters
            </p>
          </div>`;

        container.appendChild(div);
      });
    });

    this._attachFormListeners();
  },

  // ── Submitted notice (upsert keeps form editable) ─────────────────────────
  _showSubmittedNotice() {
    const container = document.getElementById("questionsContainer");
    if (!container || container.querySelector(".submitted-notice")) return;

    const badge = document.createElement("div");
    badge.className =
      "submitted-notice mb-4 p-3 bg-yellow-50 border border-yellow-300 " +
      "rounded-lg text-yellow-800 text-sm font-medium";
    badge.textContent =
      "✓ You already submitted this evaluation. You may update your answers and resubmit.";
    container.prepend(badge);
  },

  // ── Form listeners ─────────────────────────────────────────────────────────
  _attachFormListeners() {
    document.querySelectorAll("input[type='radio']").forEach((radio) => {
      radio.addEventListener("change", () => {
        this.collectAnswers();
        this.validateAllAnswered();
      });
    });

    document.querySelectorAll(".comment-box").forEach((textarea) => {
      textarea.addEventListener("input", (e) => {
        const counter = e.target.closest("div").querySelector(".char-count");
        if (counter) counter.textContent = e.target.value.length;
        this.collectAnswers();
      });
    });
  },

  // ── Collect answers ────────────────────────────────────────────────────────
  collectAnswers() {
    const answers = {};

    document.querySelectorAll(".question").forEach((el) => {
      const qid = el.dataset.qid;
      const radio = el.querySelector("input[type='radio']:checked");
      const textarea = el.querySelector(".comment-box");

      answers[qid] = {
        score: radio ? parseInt(radio.value, 10) : null,
        comment: textarea ? textarea.value.trim() : "",
      };
    });

    this.answers[this.currentTeacher.load_id] = answers;
    return answers;
  },

  // ── Validate scores (comment is optional) ────────────────────────────────
  validateAllAnswered() {
    const localAnswers = this.answers[this.currentTeacher.load_id] ?? {};

    const allAnswered = this.questions.every((q) => {
      const score = localAnswers[q.question_id]?.score;
      return Number.isInteger(score) && score >= 1 && score <= 5;
    });

    const nextBtn = document.getElementById("nextBtn");
    if (nextBtn) {
      nextBtn.disabled = !allAnswered;
      nextBtn.classList.toggle("opacity-50", !allAnswered);
      nextBtn.classList.toggle("cursor-not-allowed", !allAnswered);
    }

    return allAnswered;
  },

  // ── Submit evaluation ──────────────────────────────────────────────────────
  async submitEvaluation() {
    if (!this.validateAllAnswered()) {
      alert("Please select a rating for every question before proceeding.");
      return;
    }

    const answers = this.collectAnswers();

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=submit_evaluation",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            load_id: this.currentTeacher.load_id,
            answers,
          }),
        },
      );

      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!data.success) {
        alert(data.error ?? "Submission failed. Please try again.");
        return;
      }

      this.isSubmitted = true;

      // Check completion immediately — enables the button when last teacher is done
      await this.checkCompletion();

      await this.nextTeacher();
    } catch (err) {
      console.error("submitEvaluation:", err);
      alert("Failed to submit evaluation. Please try again.");
    }
  },

  // ── Next teacher ──────────────────────────────────────────────────────────
  async nextTeacher() {
    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=next_teacher",
        { method: "POST" },
      );
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!data.success) {
        alert(data.error ?? "Could not advance to next teacher.");
        return;
      }

      if (data.is_last) {
        // Already checked completion in submitEvaluation — no need to alert here
        return;
      }

      await this.fetchTeacher();
    } catch (err) {
      console.error("nextTeacher:", err);
      alert("Failed to proceed to next teacher. Please try again.");
    }
  },

  // ── Previous teacher ──────────────────────────────────────────────────────
  async previousTeacher() {
    this.collectAnswers();

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=previous_teacher",
        { method: "POST" },
      );
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!data.success) {
        alert(data.error ?? "Could not go back.");
        return;
      }

      await this.fetchTeacher();
    } catch (err) {
      console.error("previousTeacher:", err);
      alert("Failed to go to previous teacher. Please try again.");
    }
  },

  // ── Check completion ───────────────────────────────────────────────────────
  async checkCompletion() {
    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=check_completion",
      );
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!data.success) return null;

      const counterEl = document.querySelector("[data-completion-count]");
      if (counterEl) {
        counterEl.textContent = `${data.completed_count} / ${data.total_count} completed`;
      }

      if (data.all_completed) {
        this._enableCompleteButton();
      }

      return data;
    } catch (err) {
      console.error("checkCompletion:", err);
      return null;
    }
  },

  // ── Finalize all evaluations ───────────────────────────────────────────────
  async completeEvaluation() {
    // Guard — backend also verifies this
    const completion = await this.checkCompletion();
    if (!completion?.all_completed) {
      const done = completion?.completed_count ?? 0;
      const total = completion?.total_count ?? 0;
      alert(
        `Please complete all evaluations first. (${done} of ${total} done)`,
      );
      return;
    }

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=complete_evaluation",
        { method: "POST" },
      );
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!data.success) {
        alert(
          data.error ?? "Could not finalize evaluations. Please try again.",
        );
        return;
      }

      window.location.href = data.redirect;
    } catch (err) {
      console.error("completeEvaluation:", err);
      alert("Failed to complete evaluation. Please try again.");
    }
  },

  // ── Navigation button states ───────────────────────────────────────────────
  updateNavigationButtons() {
    const prevBtn = document.getElementById("previousBtn");
    const nextBtn = document.getElementById("nextBtn");
    const isFirst = this.currentIndex === 0;
    const isLast = this.currentIndex === this.totalTeachers - 1;

    if (prevBtn) {
      prevBtn.disabled = isFirst;
      prevBtn.classList.toggle("opacity-50", isFirst);
      prevBtn.classList.toggle("cursor-not-allowed", isFirst);
    }

    if (nextBtn) {
      nextBtn.textContent = isLast ? "Submit & Finish" : "Submit & Next →";
    }
  },

  // ── Enable complete button ─────────────────────────────────────────────────
  _enableCompleteButton() {
    const btn = document.getElementById("completeBtn");
    if (!btn) return;
    btn.disabled = false;
    btn.classList.remove("opacity-50", "cursor-not-allowed", "bg-gray-300");
    btn.classList.add("bg-purple-700", "hover:bg-purple-800", "cursor-pointer");
  },

  // ── Fatal error display ────────────────────────────────────────────────────
  _handleFatalError(message) {
    console.error("Fatal:", message);
    const container = document.getElementById("questionsContainer");
    if (container) {
      container.innerHTML = `
        <div class="text-center text-red-500 py-12">
          <p class="font-semibold text-lg">Something went wrong</p>
          <p class="text-sm mt-2">${this._escapeHtml(message)}</p>
        </div>`;
    }
    alert(message);
  },

  // ── XSS guard ─────────────────────────────────────────────────────────────
  _escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = String(text ?? "");
    return div.innerHTML;
  },

  // ── Review modal — open ────────────────────────────────────────────────────
  async openReviewModal() {
    const modal = document.getElementById("reviewModal");
    if (!modal) {
      console.error(
        "reviewModal element not found. Did you include review_modal.php?",
      );
      return;
    }

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/evaluation.php?action=get_all_answers",
      );
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();

      if (!data.success) {
        alert(data.error ?? "Could not load your answers for review.");
        return;
      }

      this._reviewData = data.data;
      this._reviewIndex = 0;

      this._renderReviewTeacher();
      modal.classList.remove("hidden");
      document.body.style.overflow = "hidden";
    } catch (err) {
      console.error("openReviewModal:", err);
      alert("Failed to load review. Please try again.");
    }
  },

  // ── Review modal — close ───────────────────────────────────────────────────
  closeReviewModal() {
    const modal = document.getElementById("reviewModal");
    if (modal) modal.classList.add("hidden");
    document.body.style.overflow = "";
    console.log("clicked");
  },

  // ── Review modal — render current teacher ─────────────────────────────────
  _renderReviewTeacher() {
    const SCORE_LABELS = {
      1: "Strongly Disagree",
      2: "Disagree",
      3: "Neutral",
      4: "Agree",
      5: "Strongly Agree",
    };

    // Tailwind-safe classes for score badges
    const SCORE_CLASS = {
      1: "bg-red-100 text-red-700",
      2: "bg-orange-100 text-orange-700",
      3: "bg-gray-100 text-gray-600",
      4: "bg-blue-100 text-blue-700",
      5: "bg-green-100 text-green-700",
    };

    const entry = this._reviewData[this._reviewIndex];
    if (!entry) return;

    const { teacher, answers } = entry;

    document.getElementById("reviewTeacherName").textContent =
      teacher.full_name;
    document.getElementById("reviewTeacherDept").textContent =
      teacher.department;
    document.getElementById("reviewTeacherAvatar").textContent =
      teacher.full_name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join("");
    document.getElementById("reviewTeacherCounter").textContent =
      `Teacher ${this._reviewIndex + 1} of ${this._reviewData.length}`;

    // Average score
    const scores = Object.values(answers)
      .map((a) => a.score)
      .filter(Boolean);
    document.getElementById("reviewScoreAvg").textContent = scores.length
      ? (scores.reduce((s, n) => s + n, 0) / scores.length).toFixed(1)
      : "—";

    // Answer rows
    const list = document.getElementById("reviewAnswerList");
    list.innerHTML = "";

    this.questions.forEach((q, i) => {
      const ans = answers[q.question_id] ?? {};
      const score = ans.score ?? null;
      const comment = ans.comment ?? "";
      const badgeCls = score ? SCORE_CLASS[score] : "bg-red-100 text-red-600";
      const label = score ? SCORE_LABELS[score] : "Not answered";

      const row = document.createElement("div");
      row.className = "py-3 border-b border-gray-100 last:border-0";
      row.innerHTML = `
        <div class="flex items-start justify-between gap-3">
          <p class="text-sm text-gray-700 m-0 leading-relaxed flex-1">
            ${i + 1}. ${this._escapeHtml(q.question_text)}
          </p>
          <span class="flex-shrink-0 text-xs font-medium px-2.5 py-1 rounded-full ${badgeCls}">
            ${label}
          </span>
        </div>
        ${
          comment
            ? `<p class="text-xs text-gray-400 italic mt-1.5 m-0">"${this._escapeHtml(comment)}"</p>`
            : ""
        }
      `;
      list.appendChild(row);
    });

    // Dots
    const dots = document.getElementById("reviewDots");
    dots.innerHTML = "";
    this._reviewData.forEach((_, i) => {
      const d = document.createElement("button");
      d.type = "button";
      d.className = `w-2 h-2 rounded-full transition-colors ${
        i === this._reviewIndex ? "bg-gray-700" : "bg-gray-300"
      }`;
      d.onclick = () => {
        this._reviewIndex = i;
        this._renderReviewTeacher();
      };
      dots.appendChild(d);
    });

    // Prev / Next
    const isLast = this._reviewIndex === this._reviewData.length - 1;
    document.getElementById("reviewPrevBtn").disabled = this._reviewIndex === 0;
    document.getElementById("reviewNextBtn").disabled = isLast;

    // Confirm button — highlight green only on last teacher page
    const confirmBtn = document.getElementById("reviewConfirmBtn");
    if (isLast) {
      confirmBtn.className =
        "text-sm px-4 py-1.5 rounded-lg border font-medium transition-colors " +
        "bg-green-600 border-green-600 text-white hover:bg-green-700";
    } else {
      confirmBtn.className =
        "text-sm px-4 py-1.5 rounded-lg border font-medium transition-colors " +
        "bg-white border-gray-300 text-gray-700 hover:bg-gray-50";
    }
  },

  // ── Review modal — navigate between teachers ───────────────────────────────
  reviewNavigate(dir) {
    const next = this._reviewIndex + dir;
    if (next < 0 || next >= this._reviewData.length) return;
    this._reviewIndex = next;
    this._renderReviewTeacher();
  },
};

// ── Bootstrap ────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", async () => {
  try {
    await studentEvaluation.fetchQuestions();
    if (!studentEvaluation.questions.length) return;

    await studentEvaluation.fetchTeacher();
    if (!studentEvaluation.currentTeacher) return;

    document.getElementById("nextBtn")?.addEventListener("click", (e) => {
      e.preventDefault();
      studentEvaluation.submitEvaluation();
    });

    document.getElementById("previousBtn")?.addEventListener("click", (e) => {
      e.preventDefault();
      studentEvaluation.previousTeacher();
    });

    // Opens review modal — completeEvaluation() fires from inside the modal
    document.getElementById("completeBtn")?.addEventListener("click", (e) => {
      e.preventDefault();
      studentEvaluation.openReviewModal();
    });

    document.getElementById("reviewCloseBtn")?.addEventListener("click", () => {
      studentEvaluation.closeReviewModal();
    });

    document.getElementById("reviewPrevBtn")?.addEventListener("click", () => {
      studentEvaluation.reviewNavigate(-1);
    });

    document.getElementById("reviewNextBtn")?.addEventListener("click", () => {
      studentEvaluation.reviewNavigate(1);
    });

    document
      .getElementById("reviewConfirmBtn")
      ?.addEventListener("click", () => {
        studentEvaluation.completeEvaluation();
      });
  } catch (err) {
    console.error("Initialization error:", err);
    alert("Failed to initialize the evaluation. Please refresh the page.");
  }
});
