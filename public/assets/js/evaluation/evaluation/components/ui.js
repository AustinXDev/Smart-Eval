import { getState, getCurrentTeacher, getAnswer } from "./state.js";

import {
  escapeHTML,
  getTeacherImage,
  getInitials,
  renderRatingStars,
} from "./utils.js";

import { saveTeacherComment } from "./state.js";

const RATING_OPTIONS = [
  {
    score: 1,
    emoji: "😡",
    label: "Strongly Disagree",
    color: "red",
  },
  {
    score: 2,
    emoji: "😕",
    label: "Disagree",
    color: "orange",
  },
  {
    score: 3,
    emoji: "😐",
    label: "Neutral",
    color: "amber",
  },
  {
    score: 4,
    emoji: "🙂",
    label: "Agree",
    color: "green",
  },
  {
    score: 5,
    emoji: "🤩",
    label: "Strongly Agree",
    color: "rose",
  },
];

const RATING_LABELS = {
  1: "Strongly Disagree",
  2: "Disagree",
  3: "Neutral",
  4: "Agree",
  5: "Strongly Agree",
};

const RATING_EMOJIS = {
  1: "😡",
  2: "😕",
  3: "😐",
  4: "🙂",
  5: "🤩",
};

function getRatingEmoji(score) {
  return RATING_EMOJIS[Number(score)] ?? "—";
}

export function showLoading() {
  document.getElementById("questionsLoader")?.classList.remove("hidden");
}

export function hideLoading() {
  document.getElementById("questionsLoader")?.classList.add("hidden");
}

export function renderTeacher() {
  const teacher = getCurrentTeacher();

  if (!teacher) return;

  const nameElement = document.getElementById("teacherName");

  const departmentElement = document.getElementById("teacherDepartment");

  const imageElement = document.getElementById("teacherImage");

  const fallbackElement = document.getElementById("teacherImageFallback");

  nameElement.textContent = teacher.full_name || "Unknown Teacher";

  departmentElement.textContent = teacher.department || "Department";

  const image = getTeacherImage(teacher);

  if (image) {
    imageElement.src = `${window.BASE_URL}uploads/teachers/${image}`;
    imageElement.classList.remove("hidden");
    fallbackElement.classList.add("hidden");
  } else {
    imageElement.classList.add("hidden");
    fallbackElement.classList.remove("hidden");
  }
}

export function renderTeacherProgress() {
  const state = getState();

  const current = state.currentTeacherIndex + 1;

  const total = state.teachers.length;

  const element = document.getElementById("teacherProgressText");

  element.textContent = `Teacher ${current} of ${total}`;
}

export function renderQuestions() {
  const state = getState();

  const container = document.getElementById("questionsContainer");

  container.innerHTML = "";

  state.questions.forEach((question, index) => {
    const questionElement = createQuestionElement(question, index);

    container.appendChild(questionElement);
  });

  container.appendChild(createTeacherComment());
}

function createQuestionElement(question, index) {
  const questionId = question.question_id ?? question.id;

  const selectedScore = getAnswer(questionId);

  const article = document.createElement("article");

  article.className = `
    mb-2
    rounded-xl
    border
    border-slate-200
    bg-white
    p-3.5
    transition
    duration-200
    last:mb-0
    hover:border-violet-100
  `;

  article.dataset.questionId = questionId;

  article.innerHTML = `
    <div class="flex items-start gap-3">

      <!-- QUESTION NUMBER -->

      <div
        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-violet-600"
      >
        ${index + 1}
      </div>


      <!-- QUESTION CONTENT -->

      <div class="min-w-0 flex-1">

        <h3
          class="pt-1 text-sm font-bold leading-6 text-[#17346b]"
        >
          ${escapeHTML(question.question_text)}
        </h3>


        <!-- RATINGS -->

        <div
          class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5"
          data-rating-group
        >
          ${RATING_OPTIONS.map((rating) =>
            createRatingButton(questionId, rating, selectedScore),
          ).join("")}
        </div>

      </div>

    </div>
  `;

  return article;
}

function createRatingButton(questionId, rating, selectedScore) {
  const selected = Number(selectedScore) === rating.score;

  return `
    <button
      type="button"
      class="
        rating-option
        group
        relative
        flex
        min-h-[76px]
        flex-col
        items-center
        justify-center
        rounded-xl
        border
        px-2
        py-2
        text-center
        transition-all
        duration-200
        ease-out

        ${
          selected
            ? "border-violet-500 bg-violet-50 shadow-sm shadow-violet-500/10"
            : "border-slate-200 bg-white hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-50/40 hover:shadow-sm"
        }
      "
      data-question-id="${questionId}"
      data-score="${rating.score}"
      aria-label="${escapeHTML(rating.label)}"
      aria-pressed="${selected}"
    >

      <!-- CHECK -->

      <span
        class="
          absolute
          right-1.5
          top-1.5
          flex
          h-4
          w-4
          items-center
          justify-center
          rounded-full
          bg-violet-600
          text-white
          transition-all
          duration-200

          ${selected ? "scale-100 opacity-100" : "scale-75 opacity-0"}
        "
      >
        <i class="fa-solid fa-check text-[8px]"></i>
      </span>


      <!-- EMOJI -->

      <span
        class="
          text-[27px]
          leading-none
          transition-transform
          duration-200
          group-hover:scale-105
        "
        aria-hidden="true"
      >
        ${rating.emoji}
      </span>


      <!-- LABEL -->

      <span
        class="
          mt-2
          text-[10px]
          font-semibold
          leading-tight
          ${selected ? "text-violet-700" : "text-[#5872a4]"}
        "
      >
        ${escapeHTML(rating.label)}
      </span>

    </button>
  `;
}

function createTeacherComment() {
  const state = getState();
  const teacher = getCurrentTeacher();

  const teacherId = teacher?.teacher_id;

  const comment = state.comments?.[teacherId] ?? "";

  const wrapper = document.createElement("div");

  wrapper.className = `
    mt-4
    rounded-xl
    border
    border-slate-200
    bg-slate-50/70
    p-4
  `;

  wrapper.innerHTML = `
    <div class="mb-3 flex items-start gap-3">

      <div
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-100"
      >
        <i class="fa-regular fa-comment-dots text-sm text-violet-600"></i>
      </div>

      <div>
        <h3 class="text-sm font-bold text-[#17346b]">
          Additional Comment
        </h3>

        <p class="mt-0.5 text-xs text-[#5872a4]">
          Share any additional feedback about this teacher.
        </p>
      </div>

    </div>

    <textarea
      id="teacherComment"
      rows="3"
      maxlength="500"
      placeholder="Write your feedback here..."
      class="
        w-full
        resize-none
        rounded-xl
        border
        border-slate-200
        bg-white
        px-3.5
        py-3
        text-sm
        text-slate-700
        outline-none
        transition
        placeholder:text-slate-400
        focus:border-violet-400
        focus:ring-4
        focus:ring-violet-100
      "
      required
    >${escapeHTML(comment)}</textarea>

    <div class="mt-1.5 flex justify-between">

      <span class="text-[11px] text-slate-400">
        Required
      </span>

      <span
        id="commentCounter"
        class="text-[11px] text-slate-400"
      >
        ${comment.length}/500
      </span>

    </div>
  `;

  const textarea = wrapper.querySelector("#teacherComment");
  const counter = wrapper.querySelector("#commentCounter");

  textarea.addEventListener("input", () => {
    saveTeacherComment(textarea.value);

    counter.textContent = `${textarea.value.length}/500`;

    renderQuestionProgress();
    updateNavigation();
  });

  return wrapper;
}

export function updateRatingSelection(questionId, score) {
  const question = document.querySelector(`[data-question-id="${questionId}"]`);

  if (!question) return;

  const buttons = question.querySelectorAll(".rating-option");

  buttons.forEach((button) => {
    const buttonScore = Number(button.dataset.score);

    const selected = buttonScore === Number(score);

    button.classList.toggle("border-violet-500", selected);

    button.classList.toggle("bg-violet-50", selected);

    button.classList.toggle("shadow-sm", selected);

    button.classList.toggle("border-slate-200", !selected);

    button.classList.toggle("bg-white", !selected);

    button.setAttribute("aria-pressed", String(selected));

    const check = button.querySelector("span.absolute");

    check?.classList.toggle("scale-100", selected);

    check?.classList.toggle("opacity-100", selected);

    check?.classList.toggle("scale-75", !selected);

    check?.classList.toggle("opacity-0", !selected);

    const label = button.querySelector("span:last-child");

    label?.classList.toggle("text-violet-700", selected);

    label?.classList.toggle("text-[#5872a4]", !selected);
  });

  updateNavigation();
}

export function renderQuestionProgress() {
  const state = getState();

  const teacher = getCurrentTeacher();

  if (!teacher) return;

  const teacherId = teacher.teacher_id;

  const currentAnswers = state.answers?.[teacherId] ?? {};

  const currentComment = state.comments?.[teacherId] ?? "";

  const answeredCount =
    Object.keys(currentAnswers).length + (currentComment.trim() !== "" ? 1 : 0);

  const total = state.questions.length + 1;

  document.getElementById("questionCounter").textContent =
    `${answeredCount} / ${total}`;

  document.getElementById("questionProgressText").textContent =
    `${answeredCount} of ${total} completed`;

  renderProgressDots(answeredCount, total);
}

function renderProgressDots(answered, total) {
  const container = document.getElementById("questionDots");

  container.innerHTML = "";

  for (let index = 0; index < total; index++) {
    const dot = document.createElement("span");

    const active = index < answered;

    dot.className = `
      h-2
      w-2
      rounded-full
      transition-all
      duration-200
      ${active ? "bg-violet-600" : "bg-slate-200"}
    `;

    container.appendChild(dot);
  }
}

export function updateNavigation() {
  const state = getState();

  const previousButton = document.getElementById("previousBtn");

  const nextButton = document.getElementById("nextBtn");

  previousButton.disabled = state.currentTeacherIndex === 0;

  const currentTeacher = getCurrentTeacher();

  const teacherId = currentTeacher.teacher_id;

  const currentAnswers = state.answers?.[teacherId] ?? {};

  const currentComment = state.comments?.[teacherId] ?? "";

  const answeredCount =
    Object.keys(currentAnswers).length + (currentComment.trim() !== "" ? 1 : 0);

  const total = state.questions.length + 1;

  nextButton.disabled = answeredCount !== total || state.isSaving;
}

export function showError(message) {
  const existing = document.getElementById("evaluationError");

  existing?.remove();

  const error = document.createElement("div");

  error.id = "evaluationError";

  error.className = `
    mx-5
    mb-4
    rounded-xl
    border
    border-red-200
    bg-red-50
    px-4
    py-3
    text-sm
    font-medium
    text-red-700
    sm:mx-6
  `;

  error.innerHTML = `
    <div class="flex items-start gap-2">
      <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
      <span>${escapeHTML(message)}</span>
    </div>
  `;

  document.getElementById("questionsContainer").before(error);
}

export function showSuccess(message) {
  const notification = document.createElement("div");

  notification.className = `
    fixed
    right-4
    top-20
    z-50
    flex
    max-w-sm
    items-center
    gap-3
    rounded-xl
    border
    border-emerald-200
    bg-white
    px-4
    py-3
    shadow-xl
  `;

  notification.innerHTML = `
    <div
      class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"
    >
      <i class="fa-solid fa-check text-xs"></i>
    </div>

    <span class="text-sm font-semibold text-slate-700">
      ${escapeHTML(message)}
    </span>
  `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 2500);
}

export function showReviewModal() {
  const state = getState();

  const teachers = state.teachers;

  if (!teachers.length) return;

  let reviewIndex = 0;

  const modal = document.createElement("div");

  modal.id = "reviewAnswerModal";

  modal.className = `
    fixed
    inset-0
    z-[100]
    flex
    items-center
    justify-center
    bg-slate-950/60
    p-3
    backdrop-blur-md
    sm:p-6
  `;

  modal.innerHTML = `
    <div
      class="
        relative
        flex
        h-[min(880px,94vh)]
        w-full
        max-w-4xl
        flex-col
        overflow-hidden
        rounded-[28px]
        border
        border-white/60
        bg-white
        shadow-[0_30px_100px_rgba(15,23,42,0.25)]
      "
      role="dialog"
      aria-modal="true"
      aria-labelledby="reviewModalTitle"
    >

      <!-- ======================================================
           HEADER
      ======================================================= -->

      <header
        class="
          relative
          shrink-0
          overflow-hidden
          border-b
          border-slate-200/80
          bg-white
          px-5
          py-5
          sm:px-8
          sm:py-6
        "
      >

        <!-- Decorative accent -->

        <div
          class="
            pointer-events-none
            absolute
            -right-16
            -top-20
            h-44
            w-44
            rounded-full
            bg-violet-100/70
            blur-3xl
          "
        ></div>

        <div
          class="
            pointer-events-none
            absolute
            bottom-0
            left-1/3
            h-20
            w-32
            rounded-full
            bg-indigo-50/60
            blur-2xl
          "
        ></div>


        <div
          class="
            relative
            flex
            items-start
            justify-between
            gap-5
          "
        >

          <div>

            <h2
              id="reviewModalTitle"
              class="
                text-xl
                font-semibold
                tracking-tight
                text-slate-900
                sm:text-2xl
              "
            >
              Review & Confirm
            </h2>

            <p
              class="
                mt-1.5
                max-w-xl
                text-xs
                leading-5
                text-slate-500
                sm:text-sm
              "
            >
              Take a final look at your responses before
              submitting this evaluation.
            </p>

          </div>


          <!-- Close -->

          <button
            type="button"
            id="closeReviewModal"
            class="
              group
              flex
              h-9
              w-9
              shrink-0
              items-center
              justify-center
              rounded-xl
              border
              border-slate-200
              bg-white
              text-slate-400
              shadow-sm
              transition-all
              duration-200
              hover:border-slate-300
              hover:bg-slate-50
              hover:text-slate-700
              active:scale-95
            "
            aria-label="Close review modal"
          >

            <svg
              class="
                h-4
                w-4
                transition-transform
                duration-200
                group-hover:rotate-90
              "
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>

          </button>

        </div>

      </header>


      <!-- ======================================================
           SCROLLABLE CONTENT
      ======================================================= -->

      <div
        id="reviewContent"
        class="
          min-h-0
          flex-1
          overflow-y-auto
          px-4
          py-5
          sm:px-8
          sm:py-7
        "
      ></div>


      <!-- ======================================================
           FOOTER
      ======================================================= -->

      <footer
        class="
          shrink-0
          border-t
          border-slate-200/80
          bg-white
          px-4
          py-4
          sm:px-8
          sm:py-5
        "
      >

        <div
          class="
            flex
            flex-col
            gap-4
            sm:flex-row
            sm:items-center
            sm:justify-between
          "
        >

          <!-- Progress -->

          <div class="min-w-0">

            <div
              class="
                mb-2
                flex
                items-center
                gap-2
              "
            >

              <span
                class="
                  text-[10px]
                  font-bold
                  uppercase
                  tracking-wider
                  text-slate-400
                "
              >
                Teacher
              </span>

              <span
                class="
                  h-1
                  w-1
                  rounded-full
                  bg-slate-300
                "
              ></span>

              <span
                class="
                  text-xs
                  font-semibold
                  text-slate-700
                "
              >
                <span id="reviewProgress">1</span>
                of
                ${teachers.length}
              </span>

            </div>


            <div
              class="
                h-1.5
                w-32
                overflow-hidden
                rounded-full
                bg-slate-100
                sm:w-44
              "
            >

              <div
                id="teacherProgressBar"
                class="
                  h-full
                  rounded-full
                  bg-gradient-to-r
                  from-violet-600
                  to-indigo-600
                  transition-all
                  duration-500
                "
                style="width: ${(1 / teachers.length) * 100}%"
              ></div>

            </div>

          </div>


          <!-- Actions -->

          <div
            class="
              flex
              w-full
              items-center
              gap-2
              sm:w-auto
            "
          >

            <button
              type="button"
              id="reviewBackBtn"
              class="
                inline-flex
                flex-1
                items-center
                justify-center
                gap-2
                rounded-xl
                border
                border-slate-200
                bg-white
                px-4
                py-2.5
                text-xs
                font-semibold
                text-slate-600
                transition-all
                duration-200
                hover:border-slate-300
                hover:bg-slate-50
                hover:text-slate-900
                active:scale-[0.98]
                disabled:cursor-not-allowed
                disabled:opacity-40
                sm:flex-none
              "
            >
              <span>←</span>
              <span>Back</span>
            </button>


            <button
              type="button"
              id="reviewNextBtn"
              class="
                group
                inline-flex
                flex-1
                items-center
                justify-center
                gap-2
                rounded-xl
                bg-slate-900
                px-5
                py-2.5
                text-xs
                font-bold
                text-white
                shadow-lg
                shadow-slate-900/10
                transition-all
                duration-200
                hover:bg-violet-700
                hover:shadow-violet-700/20
                active:scale-[0.98]
                sm:flex-none
              "
            >

              <span id="reviewNextText">
                Next Teacher
              </span>

              <svg
                class="
                  h-4
                  w-4
                  transition-transform
                  duration-200
                  group-hover:translate-x-0.5
                "
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>

            </button>

          </div>

        </div>

      </footer>

    </div>
  `;

  document.body.appendChild(modal);

  const content = modal.querySelector("#reviewContent");

  const progress = modal.querySelector("#reviewProgress");

  const progressBar = modal.querySelector("#teacherProgressBar");

  const backButton = modal.querySelector("#reviewBackBtn");

  const nextButton = modal.querySelector("#reviewNextBtn");

  const nextText = modal.querySelector("#reviewNextText");

  // ==========================================================
  // RENDER CURRENT TEACHER
  // ==========================================================

  function renderReview() {
    const state = getState();

    console.log(state);

    const teacher = teachers[reviewIndex];

    if (!teacher) return;

    const teacherId = teacher.teacher_id;

    const image = getTeacherImage(teacher);

    const answers = state.answers?.[teacherId] ?? {};

    const comment = state.comments?.[teacherId] ?? "";

    const total = teachers.length;

    const isLast = reviewIndex === total - 1;

    const hasImage = Boolean(image);

    const initials = getInitials(teacher.teacher_fullname ?? teacher.full_name);

    // ========================================================
    // UPDATE PROGRESS
    // ========================================================

    progress.textContent = reviewIndex + 1;

    progressBar.style.width = `${((reviewIndex + 1) / total) * 100}%`;

    backButton.disabled = reviewIndex === 0;

    nextText.textContent = isLast ? "Submit Evaluation" : "Next Teacher";

    // ========================================================
    // ANSWER COUNT
    // ========================================================

    const questionCount = Object.keys(answers).length;

    const totalQuestions = state.questions?.length ?? 0;

    //========================================================
    // Average Score
    //=======================================================
    const scores = Object.values(answers);

    const totalScore = scores.reduce((sum, current) => sum + current, 0);

    const totalRating = totalScore / totalQuestions;

    console.log(totalRating);

    // ========================================================
    // RENDER CONTENT
    // ========================================================

    content.innerHTML = `

      <div class="mx-auto max-w-3xl">


        <!-- ==================================================
             TEACHER HERO
        =================================================== -->

        <section
          class="
            relative
            mb-7
            overflow-hidden
            bg-white
            p-5
            sm:p-6
          "
        >

          <div
            class="
              relative
              flex
              flex-col
              items-center
              gap-4
              sm:gap-5
            "
          >

            <!-- Teacher Image -->

            <div class="relative shrink-0">

              <div
                class="
                  h-[120px]
                  w-[120px]
                  overflow-hidden
                  rounded-full
                  bg-gradient-to-br
                  from-violet-100
                  to-indigo-100
                  ring-4
                  ring-violet-50
                  sm:h-50
                  sm:w-50
                "
              >

                ${
                  hasImage
                    ? `
                      <img
                        src="${window.BASE_URL}uploads/teachers/${escapeHTML(image)}"
                        alt="${escapeHTML(teacher.full_name)}"
                        class="
                          h-full
                          w-full
                          object-cover
                        "
                        onerror="
                          this.classList.add('hidden');
                          this.nextElementSibling.classList.remove('hidden');
                        "
                      >

                      <div
                        class="
                          hidden
                          h-full
                          w-full
                          items-center
                          justify-center
                          text-lg
                          font-bold
                          text-violet-600
                        "
                      >
                        ${escapeHTML(initials)}
                      </div>
                    `
                    : `
                      <div
                        class="
                          flex
                          h-full
                          w-full
                          items-center
                          justify-center
                          text-lg
                          font-bold
                          text-violet-600
                        "
                      >
                        ${escapeHTML(initials)}
                      </div>
                    `
                }

              </div>


              <!-- Status -->

              <span
                class="
                  absolute
                  -bottom-1
                  -right-1
                  flex
                  h-6
                  w-6
                  items-center
                  justify-center
                  rounded-full
                  border-4
                  border-white
                  bg-emerald-500
                  text-[8px]
                  font-bold
                  text-white
                "
              >
                ✓
              </span>

            </div>


            <!-- Teacher Info -->

            <div class="min-w-0 flex-1">

              <p
                class="
                  mb-1
                  text-[10px]
                  text-center
                  font-bold
                  uppercase
                  tracking-[0.14em]
                  text-violet-500
                "
              >
                Currently reviewing
              </p>

              <h3
                class="
                  truncate
                  text-lg
                  text-center
                  font-bold
                  tracking-tight
                  text-slate-900
                  sm:text-xl
                "
              >
                ${escapeHTML(teacher.full_name)}
              </h3>

              <p
                class="
                  mt-0.5
                  text-center
                  truncate
                  text-xs
                  text-slate-500
                  sm:text-sm
                  uppercase
                "
              >
                ${escapeHTML(teacher.department ?? "Faculty")}
              </p>

            </div>

          </div>

          <!-- Overall Rating -->
          <div class="flex justify-center mt-4">

            <div class="flex items-center gap-2">

              <div class="flex items-center gap-0.5">
                ${renderRatingStars(totalRating)}
              </div>

              <span class="text-sm font-bold text-slate-800">
                ${totalRating.toFixed(1)}
              </span>

              <span class="text-xs text-slate-400">
                / 5.0
              </span>

            </div>

          </div>


          <!-- Evaluation metadata -->

          <div
            class="
              relative
              mt-5
              flex
              flex-wrap
              items-center
              justify-center
              gap-x-5
              gap-y-2
              border-t
              border-slate-100
              pt-4
            "
          >

            <div class="flex items-center gap-2">

              <span
                class="
                  flex
                  h-6
                  w-6
                  items-center
                  justify-center
                  rounded-lg
                  bg-violet-50
                  text-[11px]
                "
              >
                <i class="fa-regular fa-circle-check"></i>
              </span>

              <span class="text-xs text-slate-500">
                <strong
                  class="font-semibold text-slate-800"
                >
                  ${questionCount}
                </strong>
                /
                ${totalQuestions}
                questions answered
              </span>

            </div>


            <div
              class="
                hidden
                h-4
                w-px
                bg-slate-200
                sm:block
              "
            ></div>


            <div class="flex items-center gap-2">

              <span
                class="
                  flex
                  h-6
                  w-6
                  items-center
                  justify-center
                  rounded-lg
                  bg-amber-50
                  text-[11px]
                "
              >
               <i class="fa-regular fa-comment"></i>
              </span>

              <span class="text-xs text-slate-500">
                <strong
                  class="font-semibold text-slate-800"
                >
                  ${comment.trim() ? "1" : "0"}
                </strong>
                comment
              </span>

            </div>

          </div>

        </section>


        <!-- ==================================================
             RESPONSE HEADER
        =================================================== -->

        <div
          class="
            mb-4
            flex
            items-end
            justify-between
            gap-4
          "
        >

          <div>

            <p
              class="
                text-[10px]
                font-bold
                uppercase
                tracking-[0.16em]
                text-violet-500
              "
            >
              Your responses
            </p>

            <h3
              class="
                mt-1
                text-lg
                font-semibold
                tracking-tight
                text-slate-900
              "
            >
              How you rated this teacher
            </h3>

          </div>

          <span
            class="
              hidden
              rounded-full
              bg-slate-100
              px-3
              py-1.5
              text-[10px]
              font-semibold
              text-slate-500
              sm:block
            "
          >
            ${totalQuestions} responses
          </span>

        </div>


        <!-- ==================================================
             ANSWERS
        =================================================== -->

        <div
          class="
            overflow-hidden
            rounded-2xl
            bg-white
          "
        >

          ${renderAnswers(answers)}

        </div>


        <!-- ==================================================
             COMMENT
        =================================================== -->

        <section
          class="
            mt-6
            overflow-hidden
            rounded-xl
            border
            border-slate-200
            bg-white
          "
        >

          <div
            class="
              flex
              items-center
              gap-3
              border-b
              border-slate-100
              px-5
              py-4
            "
          >

            <span
              class="
                flex
                h-9
                w-9
                items-center
                justify-center
                rounded-xl
                bg-amber-50
                text-base
              "
            >
              <i class="fa-regular fa-comment text-gray-600"></i>
            </span>

            <div>

              <h3
                class="
                  text-sm
                  font-bold
                  text-slate-900
                "
              >
                Additional Feedback
              </h3>

              <p
                class="
                  mt-0.5
                  text-[10px]
                  text-slate-400
                "
              >
                Your optional comment for this teacher
              </p>

            </div>

          </div>


          <div class="px-5 py-5">

            ${
              comment.trim()
                ? `
                  <div
                    class="
                      relative
                      rounded-xl
                      bg-slate-50
                      px-5
                      py-4
                    "
                  >

                    <span
                      class="
                        absolute
                        left-3
                        top-2
                        text-2xl
                        font-serif
                        leading-none
                        text-violet-200
                      "
                    >
                      “
                    </span>

                    <p
                      class="
                        pl-4
                        text-sm
                        leading-6
                        text-slate-600
                      "
                    >
                      ${escapeHTML(comment)}
                    </p>

                  </div>
                `
                : `
                  <div
                    class="
                      flex
                      items-center
                      gap-3
                      rounded-xl
                      bg-slate-50
                      px-4
                      py-4
                    "
                  >

                    <span
                      class="text-sm"
                    >
                      —
                    </span>

                    <p
                      class="
                        text-xs
                        text-slate-400
                      "
                    >
                      No additional feedback provided.
                    </p>

                  </div>
                `
            }

          </div>

        </section>


        <!-- ==================================================
             CONFIRMATION MESSAGE
        =================================================== -->

        ${
          isLast
            ? `
              <div
                class="
                  mt-5
                  flex
                  items-start
                  gap-3
                  rounded-2xl
                  border
                  border-violet-100
                  bg-violet-50/60
                  px-4
                  py-4
                "
              >

                <span
                  class="
                    flex
                    h-8
                    w-8
                    shrink-0
                    items-center
                    justify-center
                    rounded-lg
                    bg-white
                    text-sm
                    shadow-sm
                  "
                >
                  ✨
                </span>

                <div>

                  <p
                    class="
                      text-xs
                      font-bold
                      text-violet-900
                    "
                  >
                    Almost there
                  </p>

                  <p
                    class="
                      mt-0.5
                      text-[11px]
                      leading-5
                      text-violet-700/70
                    "
                  >
                    You've reviewed all teachers.
                    Submit your evaluation when you're ready.
                  </p>

                </div>

              </div>
            `
            : ""
        }

      </div>

    `;

    updateNavigation();
  }

  // ==========================================================
  // RENDER ANSWERS
  // ==========================================================

  function renderAnswers(answers) {
    const state = getState();

    if (!state.questions?.length) {
      return `
        <div class="px-5 py-10 text-center">

          <div
            class="
              mx-auto
              flex
              h-12
              w-12
              items-center
              justify-center
              rounded-2xl
              bg-slate-100
            "
          >
            ?
          </div>

          <p
            class="
              mt-3
              text-sm
              font-medium
              text-slate-500
            "
          >
            No questions available.
          </p>

        </div>
      `;
    }

    return state.questions
      .map((question, index) => {
        const questionId = question.question_id ?? question.id;

        const score = answers?.[questionId];

        const rating = Number(score);

        const ratingLabel = RATING_LABELS[rating] ?? "Not answered";

        return `

          <div
            class="
              group
              relative
              px-5
              py-5
              transition-colors
              duration-200
              hover:bg-slate-50/70
              ${
                index !== state.questions.length - 1
                  ? "border-b border-slate-100"
                  : ""
              }
            "
          >

            <div
              class="
                flex
                items-center
                gap-4
              "
            >

              <!-- QUESTION NUMBER -->

              <div
                class="
                  flex
                  h-8
                  w-8
                  shrink-0
                  items-center
                  justify-center
                  rounded-xl
                  bg-slate-100
                  text-[10px]
                  font-bold
                  text-slate-500
                  transition-colors
                  group-hover:bg-violet-100
                  group-hover:text-violet-600
                "
              >
                ${String(index + 1).padStart(2, "0")}
              </div>


              <!-- QUESTION CONTENT -->

              <div class="flex flex-row items-center justify-between flex-1">

                <p
                  class="
                    text-sm
                    font-medium
                    leading-6
                    text-slate-800
                  "
                >
                  ${escapeHTML(
                    question.question_text ??
                      question.text ??
                      `Question ${index + 1}`,
                  )}
                </p>


                <!-- SELECTED ANSWER -->

                <div class="mt-3">

                  <div
                    class="
                      inline-flex
                      items-center
                      gap-2
                      rounded-xl
                      border
                      border-violet-200
                      bg-violet-50/70
                      px-3
                      py-2
                    "
                  >

                    <span
                      class="
                        text-lg
                        leading-none
                      "
                    >
                      ${getRatingEmoji(rating)}
                    </span>

                    <span
                      class="
                        text-xs
                        font-bold
                        text-violet-700
                      "
                    >
                      ${escapeHTML(ratingLabel)}
                    </span>

                    <span
                      class="
                        ml-1
                        flex
                        h-4
                        w-4
                        items-center
                        justify-center
                        rounded-full
                        bg-violet-600
                        text-[8px]
                        text-white
                      "
                    >
                      ✓
                    </span>

                  </div>

                </div>

              </div>

            </div>

          </div>

        `;
      })
      .join("");
  }

  // ==========================================================
  // UPDATE NAVIGATION
  // ==========================================================

  function updateNavigation() {
    const isLast = reviewIndex === teachers.length - 1;

    backButton.disabled = reviewIndex === 0;

    nextButton.classList.toggle("bg-violet-600", isLast);

    nextButton.classList.toggle("hover:bg-violet-700", isLast);

    nextButton.classList.toggle("bg-slate-900", !isLast);

    nextButton.classList.toggle("hover:bg-violet-700", !isLast);
  }

  // ==========================================================
  // NEXT / SUBMIT
  // ==========================================================

  nextButton.addEventListener("click", () => {
    const isLast = reviewIndex === teachers.length - 1;

    if (!isLast) {
      reviewIndex++;

      renderReview();

      content.scrollTo({
        top: 0,
        behavior: "smooth",
      });

      return;
    }

    // ======================================================
    // FINAL CONFIRMATION
    // ======================================================

    modal.dispatchEvent(new CustomEvent("confirm"));
  });

  // ==========================================================
  // BACK
  // ==========================================================

  backButton.addEventListener("click", () => {
    if (reviewIndex <= 0) return;

    reviewIndex--;

    renderReview();

    content.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });

  // ==========================================================
  // CLOSE
  // ==========================================================

  const close = () => {
    modal.remove();
  };

  modal.querySelector("#closeReviewModal")?.addEventListener("click", close);

  // Close when clicking backdrop

  modal.addEventListener("click", (event) => {
    if (event.target === modal) {
      close();
    }
  });

  // Escape key

  const handleEscape = (event) => {
    if (event.key === "Escape") {
      close();

      document.removeEventListener("keydown", handleEscape);
    }
  };

  document.addEventListener("keydown", handleEscape);

  // ==========================================================
  // INITIALIZE
  // ==========================================================

  renderReview();

  return {
    modal,
    close,
  };
}

export function setNextButtonLoading(loading) {
  const state = getState();

  const current = state.currentTeacherIndex + 1;
  const total = state.teachers.length;

  const button = document.getElementById("nextBtn");
  const text = document.getElementById("nextBtnText");
  const icon = document.getElementById("nextBtnIcon");
  const loader = document.getElementById("nextBtnLoader");

  if (!button || !text || !icon || !loader) return;

  button.disabled = loading;

  if (loading) {
    text.textContent = "Saving...";
  } else {
    text.textContent = current === total ? "Submit" : "Next";
  }

  icon.classList.toggle("hidden", loading);
  loader.classList.toggle("hidden", !loading);
}
