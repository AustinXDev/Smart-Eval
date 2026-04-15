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
  console.log(data);

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

document.addEventListener("DOMContentLoaded", () => {
  fetchDashboardData();
});
