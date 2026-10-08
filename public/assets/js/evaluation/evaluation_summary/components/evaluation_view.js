function el(id) {
  return document.getElementById(id);
}

export function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

export function showLoadingDone() {
  el("loading").style.display = "none";
  el("content").style.display = "block";
}

export function showError(message) {
  showLoadingDone();
  el("error-state").style.display = "block";
  el("error-message").textContent = message;
}

export function showEmptyState(periodName) {
  el("period-name").textContent = periodName;
  el("empty-state").style.display = "block";
  el("teachers-list").style.display = "none";
}

export function renderTeachersList(data) {
  el("period-name").textContent = data.period_name;
  el("empty-state").style.display = "none";
  el("teachers-list").style.display = "block";

  el("total-count").textContent = data.total_evaluated;
  el("plural-s").textContent = data.total_evaluated !== 1 ? "s" : "";

  const teachersList = document.querySelector("#teachers-list .space-y-2");

  teachersList.innerHTML = data.evaluated_teachers
    .map(
      (teacher, index) => `
      <div
        class="
          teacher-item
          group
          flex
          items-center
          justify-between
          gap-3
          rounded-2xl
          border
          border-slate-200
          bg-white
          p-3.5
          transition
          duration-200
          ease-out
          hover:border-violet-200
          hover:bg-violet-50/30
          hover:shadow-sm
          stagger-${Math.min(index + 1, 10)}
        "
      >

        <!-- Teacher Information -->
        <div class="flex min-w-0 flex-1 items-center gap-3">

          <!-- Avatar -->
          <div
            class="
              flex
              h-10
              w-10
              shrink-0
              items-center
              justify-center
              rounded-xl
              bg-violet-50
              text-sm
              font-bold
              text-violet-600
              ring-1
              ring-violet-100
              transition
              duration-200
              group-hover:bg-violet-100
            "
          >
            ${escapeHtml(teacher.full_name?.charAt(0).toUpperCase() || "?")}
          </div>


          <!-- Name / Department -->
          <div class="min-w-0 flex-1">

            <p
              class="
                truncate
                text-sm
                font-semibold
                text-[#16213E]
              "
            >
              ${escapeHtml(teacher.full_name)}
            </p>

            <div class="mt-0.5 flex items-center gap-1.5">

              <i
                class="
                  fa-regular
                  fa-building
                  text-[9px]
                  text-slate-400
                "
              ></i>

              <p
                class="
                  truncate
                  text-[11px]
                  font-medium
                  text-slate-500
                "
              >
                ${escapeHtml(teacher.department)}
              </p>

            </div>

          </div>

        </div>


        <!-- Completed Status -->
        <div
          class="
            inline-flex
            shrink-0
            items-center
            gap-1.5
            rounded-full
            bg-emerald-50
            px-2.5
            py-1.5
            text-[10px]
            font-bold
            text-emerald-600
            ring-1
            ring-emerald-100
          "
        >
          <i class="fa-regular fa-circle-check text-[10px]"></i>
          <span>Completed</span>
        </div>

      </div>
    `,
    )
    .join("");

  animateStagger();

  animateStagger();
}

function animateStagger() {
  document.querySelectorAll(".teacher-item").forEach((elNode, i) => {
    elNode.style.animation = `slideUp 0.5s ease-out ${(i + 1) * 0.1}s forwards`;
    elNode.style.opacity = "0";
  });
}
