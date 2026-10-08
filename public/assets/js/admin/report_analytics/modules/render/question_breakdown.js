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
        <div class="col-span-full text-center py-10 w-full h-full flex justify-center items-center">
          <p class="text-gray-400 text-sm">No performance highlights available for this period.</p>
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
      : `<p class="text-xs text-gray-400 italic py-2">No strengths identified for this period.</p>`;
  }

  if (weaknessesEl) {
    weaknessesEl.innerHTML = weaknesses.length
      ? weaknesses.map((item, i) => buildRow(item, i + 1, false)).join("")
      : `<p class="text-xs text-gray-400 italic py-2">No areas for improvement identified for this period.</p>`;
  }
}
