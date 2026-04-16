import { nameToInitials } from "../shared/utils.js";

const queryString = new URL(window.location.href);
const department = queryString.searchParams.get("dept");

async function fetchDashboardData() {
  const res = await fetch(
    `/Smart-Eval/app/Controllers/dashboard/dashboard.php?req=get_dashboard_data&department=${department}`,
  );

  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const data = await res.json();

  document.getElementById("totalStudents").textContent =
    data.student_total || 0;
  document.getElementById("totalTeachers").textContent =
    data.teacher_total || 0;
  document.getElementById("totalTeachers").textContent =
    data.teacher_total || 0;
  document.getElementById("academic_year").textContent =
    data.evaluation_period.academic_year || "--";
  document.getElementById("semester").textContent =
    data.evaluation_period.semester || "--";
  document.getElementById("start-date").textContent =
    convertDateStr(data.evaluation_period.start_date) || "--";
  document.getElementById("end-date").textContent =
    convertDateStr(data.evaluation_period.end_date) || "--";

  const particpationPercentage =
    data.student_total > 0
      ? Math.round((data.completed_student * 100) / data.student_total)
      : 0;

  const fill = document.getElementById("progress-fill");
  document.getElementById("percentage").textContent =
    `${particpationPercentage}%` || "--";

  setTimeout(() => {
    fill.style.width = `${particpationPercentage}%`;
  }, 200);
}

function convertDateStr(date) {
  const dateStr = date;
  const dateObj = new Date(dateStr);

  const formattedDate = dateObj.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
  });

  return formattedDate;
}

async function fetchTeacherRanking() {
  const res = await fetch(
    `/Smart-Eval/app/Controllers/dashboard/dashboard.php?req=get_teacherRanking&department=${department}`,
  );

  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const data = await res.json();

  if (data.length > 0) {
    const topTeacher = data[0];

    const top_initials = document.getElementById("top_initials");
    const top_name = document.getElementById("highest-teacher-name");
    const top_score = document.getElementById("avg-score");

    if (top_name && top_score) {
      top_initials.textContent = nameToInitials(topTeacher.teacher_name);
      top_name.textContent = topTeacher.teacher_name;
      top_score.textContent = topTeacher.overall_mean_score;
    }
  }

  let tbody = document.getElementById("tbody-ranking");

  const rowsHTML = data
    .map((d, i) => {
      return `
      <tr class="bg-amber-50 hover:bg-amber-100 transition-colors">
        <td class="px-4 py-3 text-center">
          <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-white font-semibold text-xs">${i + 1}</span>
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-xs font-semibold text-purple-800 shrink-0">${nameToInitials(d.teacher_name)}</div>
            <span class="font-medium text-gray-800 whitespace-nowrap">${d.teacher_name}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-center text-gray-600">${d.total_expected_students}</td>
        <td class="px-4 py-3 text-center text-gray-600">${d.total_evaluated_students}</td>
        <td class="px-4 py-3 text-center">
          <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-1 rounded-full">${d.overall_mean_score}</span>
        </td>
      </tr>
    `;
    })
    .join("");

  tbody.innerHTML = rowsHTML;
}

async function fetchScoreChart() {
  try {
    const res = await fetch(
      `/Smart-Eval/app/Controllers/dashboard/dashboard.php?req=score_chart&department=${encodeURIComponent(department)}`,
    );

    if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);

    const data = await res.json();

    const ctx = document.getElementById("scoreChart").getContext("2d");

    createScoreDoughnutChart(ctx, data.labels, data.data);
  } catch (error) {
    console.error("Failed to fetch score chart:", error);
  }
}

async function fetchParticipationChart() {
  try {
    const res = await fetch(
      `/Smart-Eval/app/Controllers/dashboard/dashboard.php?req=participation_chart&department=${encodeURIComponent(department)}`,
    );

    const data = await res.json();

    const ctx = document.getElementById("participationChart").getContext("2d");

    createPieChart(ctx, data.labels, data.data);
  } catch (error) {
    console.error("Failed to fetch score chart:", error);
  }
}

async function fetchProgramChart() {
  try {
    const res = await fetch(
      `/Smart-Eval/app/Controllers/dashboard/dashboard.php?req=program_chart&department=${encodeURIComponent(department)}`,
    );

    const data = await res.json();

    console.log(data);

    const ctx = document.getElementById("programChart").getContext("2d");

    createProgramBarChart(
      ctx,
      data.labels,
      data.finished,
      data.not_finished,
      data.totals,
    );
  } catch (error) {
    console.log("Failed to fetch score chart:", error);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  fetchDashboardData();
  fetchTeacherRanking();
  fetchScoreChart();
  fetchParticipationChart();
  fetchProgramChart();
});
