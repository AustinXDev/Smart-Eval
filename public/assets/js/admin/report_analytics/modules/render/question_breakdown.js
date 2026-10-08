function buildRow(item, rank, isHigh) {
  const pct = Math.min((item.score / 5) * 100, 100).toFixed(0);

  const border = isHigh ? "border-emerald-100" : "border-rose-100";

  const bg = isHigh ? "bg-emerald-50/50" : "bg-rose-50/50";

  const badgeClr = isHigh
    ? rank === 1
      ? "bg-emerald-500"
      : "bg-emerald-400"
    : rank === 1
      ? "bg-rose-500"
      : "bg-rose-400";

  const barBg = isHigh ? "bg-emerald-100" : "bg-rose-100";

  const barFill = isHigh ? "bg-emerald-500" : "bg-rose-500";

  const scoreClr = isHigh ? "text-emerald-700" : "text-rose-700";

  return `
    <div
      class="
        group relative overflow-hidden
        rounded-xl
        border ${border} ${bg}
        p-3
        transition-all duration-200
        hover:-translate-y-0.5
        hover:shadow-sm
      "
    >

      <div class="flex items-start gap-3">

        <!-- Rank -->
        <span
          class="
            flex h-7 w-7 shrink-0
            items-center justify-center
            rounded-md
            ${badgeClr}
            text-[10px]
            font-bold
            text-white
            shadow-sm
          "
        >
          ${rank}
        </span>


        <!-- Question Content -->
        <div class="min-w-0 flex-1">

          <!-- Question -->
          <p
            class="
              line-clamp-2
              text-[11px]
              font-medium
              leading-relaxed
              text-slate-700
            "
            title="${item.question}"
          >
            ${item.question}
          </p>


          <!-- Score -->
          <div class="mt-2.5 flex items-center gap-2">

            <!-- Progress Bar -->
            <div
              class="
                h-1.5
                min-w-0
                flex-1
                overflow-hidden
                rounded-full
                ${barBg}
              "
            >
              <div
                class="
                  h-full
                  rounded-full
                  ${barFill}
                  transition-all duration-500
                "
                style="width:${pct}%"
              ></div>
            </div>


            <!-- Score Value -->
            <div class="flex shrink-0 items-baseline gap-0.5">

              <span
                class="
                  text-xs
                  font-bold
                  tracking-tight
                  ${scoreClr}
                "
              >
                ${Number(item.score).toFixed(2)}
              </span>

              <span class="text-[9px] font-medium text-slate-400">
                / 5
              </span>

            </div>

          </div>

        </div>

      </div>

    </div>
  `;
}

export function renderQuestionBreakDown(data) {
  const parentContainer = document.getElementById("parentContainer");
  const strengthsEl = document.getElementById("highestQuestions");
  const weaknessesEl = document.getElementById("lowestQuestions");

  if (!data?.question_breakdown) {
    if (parentContainer) {
      parentContainer.innerHTML = `
        <div class="col-span-full flex flex-1 flex-col items-center justify-center h-full gap-3 px-6 py-12 text-center rounded-xl border border-dashed border-gray-200 bg-gray-50">
          <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l4-4 3 3 5-6" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">No question insights yet</p>
            <p class="text-xs text-gray-400 mt-1">
              Once question results are available, performance highlights will appear here.
            </p>
          </div>
        </div>
      `;
    }
    return;
  }

  const strengths = data.question_breakdown.strengths || [];
  const weaknesses = data.question_breakdown.weaknesses || [];

  if (strengthsEl) {
    strengthsEl.innerHTML = strengths.length
      ? strengths.map((item, i) => buildRow(item, i + 1, true)).join("")
      : `
        <div class="flex min-h-32 flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 bg-white/70 px-4 py-6 text-center">
          <span class="mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
            <i class="fas fa-arrow-trend-up text-xs" aria-hidden="true"></i>
          </span>
          <p class="text-xs font-medium text-slate-600">No standout questions yet</p>
          <p class="mt-1 text-[11px] leading-relaxed text-slate-400">
            No questions met the strength criteria for this period.
          </p>
        </div>
      `;
  }

  if (weaknessesEl) {
    weaknessesEl.innerHTML = weaknesses.length
      ? weaknesses.map((item, i) => buildRow(item, i + 1, false)).join("")
      : `
        <div class="flex min-h-32 flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 bg-white/70 px-4 py-6 text-center">
          <span class="mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
            <i class="fas fa-check text-xs" aria-hidden="true"></i>
          </span>
          <p class="text-xs font-medium text-slate-600">No priority areas identified</p>
          <p class="mt-1 text-[11px] leading-relaxed text-slate-400">
            No questions fell within the lower-performance range this period.
          </p>
        </div>
      `;
  }
}
