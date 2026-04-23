import { programToInitials } from "../admin/shared/utils.js";

export function createProgramBarChart(
  ctx,
  labels,
  finished,
  notFinished,
  totals,
) {
  return new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Finished",
          data: finished,
          backgroundColor: [
            "rgba(255, 99, 132, 0.5)",
            "rgba(255, 159, 64, 0.5)",
            "rgba(255, 205, 86, 0.5)",
            "rgba(75, 192, 192, 0.5)",
            "rgba(54, 162, 235, 0.5)",
            "rgba(153, 102, 255, 0.5)",
            "rgba(201, 203, 207, 0.5)",
          ],
          hoverBackgroundColor: [
            "rgb(255, 99, 132)",
            "rgb(255, 159, 64)",
            "rgb(255, 205, 86)",
            "rgb(75, 192, 192)",
            "rgb(54, 162, 235)",
            "rgb(153, 102, 255)",
            "rgb(201, 203, 207)",
          ],
          borderWidth: 1,
          borderRadius: 10,
          borderSkipped: false,
          barPercentage: 0.6,
          categoryPercentage: 0.7,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 1000,
        easing: "easeOutQuart",
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "#111827",
          titleColor: "#fff",
          bodyColor: "#e5e7eb",
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            // Show full label in tooltip title
            title: function (items) {
              return labels[items[0].dataIndex];
            },
            label: function (context) {
              return `Finished: ${context.raw} students`;
            },
            afterLabel: function (context) {
              return `Not Finished: ${notFinished[context.dataIndex]} students`;
            },
            footer: function (items) {
              return `Total: ${totals[items[0].dataIndex]} students`;
            },
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            maxRotation: 0,
            minRotation: 0,
            font: { size: 11 },
            callback: function (value, index) {
              return programToInitials(labels[index]);
            },
          },
        },
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            precision: 0,
          },
          grid: {
            color: "rgba(0,0,0,0.05)",
          },
        },
      },
    },
  });
}

export function createPieChart(ctx, labels, data) {
  return new Chart(ctx, {
    type: "pie",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Total",
          data: data,
          backgroundColor: ["#16a34a", "#f87171"],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        animateRotate: true,
        animateScale: true,
        duration: 1500,
        easing: "easeOutBounce",
      },
      plugins: {
        legend: {
          display: true,
          position: "bottom",
        },
      },
    },
  });
}

export function createScoreDoughnutChart(ctx, labels, data) {
  return new Chart(ctx, {
    type: "doughnut", // ✅ doughnut chart
    data: {
      labels: labels,
      datasets: [
        {
          label: "Score",
          data: data,
          backgroundColor: [
            "#16a34a", // Excellent
            "#2563eb", // Good
            "#facc15", // Fair
            "#f87171", // Poor
            "rgb(130, 0, 0)", // Very Poor
          ],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        animateRotate: true,
        animateScale: true,
        duration: 1500,
        easing: "easeOutBounce",
      },
      plugins: {
        legend: {
          display: true,
          position: "bottom",
        },
        tooltip: {
          enabled: true,
        },
      },
      cutout: "50%",
    },
  });
}

export function createTrendLineChart(ctx, labels, scores) {
  const gradient = ctx.createLinearGradient(0, 0, 0, 280);
  gradient.addColorStop(0, "rgba(83, 74, 183, 0.18)");
  gradient.addColorStop(1, "rgba(83, 74, 183, 0.00)");

  return new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Mean Score",
          data: scores,
          borderColor: "#534AB7",
          backgroundColor: gradient,
          borderWidth: 2.5,
          pointBackgroundColor: "#fff",
          pointBorderColor: "#534AB7",
          pointBorderWidth: 2.5,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointHoverBackgroundColor: "#534AB7",
          pointHoverBorderColor: "#fff",
          pointHoverBorderWidth: 2,
          tension: 0.45,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: "index", intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "#1E1A14",
          titleColor: "#9E9A93",
          bodyColor: "#fff",
          padding: { top: 8, bottom: 8, left: 12, right: 12 },
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            title: (items) => items[0].label,
            label: (item) => `Mean Score: ${item.raw.toFixed(2)} / 5.00`,
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          border: { display: false },
          ticks: { color: "#9E9A93", font: { size: 11 }, padding: 6 },
        },
        y: {
          min: 3.0,
          max: 5.0,
          grid: { color: "#F0EDE7", drawTicks: false },
          border: { display: false },
          ticks: {
            color: "#B8B3AA",
            font: { size: 10 },
            padding: 10,
            stepSize: 0.5,
            callback: (val) => val.toFixed(1),
          },
        },
      },
      plugins: {
        tooltip: {
          enabled: true,
        },
      },
    },
  });
}

export function createYearLevelParticipationChart(
  ctx,
  labels,
  finished,
  pending,
) {
  return new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Completed evaluation",
          data: finished,
          backgroundColor: "#534AB7",
          borderRadius: 5,
          borderSkipped: false,
        },
        {
          label: "Did not evaluate",
          data: pending,
          backgroundColor: "#AFA9EC",
          borderRadius: 10,
          borderSkipped: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { bottom: 10 } },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "#1E1A14",
          titleColor: "#9E9A93",
          bodyColor: "#fff",
          padding: { top: 8, bottom: 8, left: 12, right: 12 },
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            label: (item) => `${item.dataset.label}: ${item.raw}%`,
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          border: { display: false },
          ticks: {
            color: "#9E9A93",
            font: { size: 11 },
            padding: 6,
            autoSkip: false,
          },
        },
        y: {
          min: 0,
          max: 100,
          grid: { color: "#F0EDE7", drawTicks: false },
          border: { display: false },
          ticks: {
            color: "#B8B3AA",
            font: { size: 10 },
            padding: 10,
            stepSize: 25,
            callback: (v) => v + "%",
          },
        },
      },
    },
  });
}
