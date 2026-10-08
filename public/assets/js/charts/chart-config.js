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
  const chartArea = ctx.canvas.parentElement;

  const createGradient = (chart) => {
    const { chartArea } = chart;

    if (!chartArea) {
      return "rgba(96, 16, 255, 0.08)";
    }

    const gradient = chart.ctx.createLinearGradient(
      0,
      chartArea.top,
      0,
      chartArea.bottom,
    );

    gradient.addColorStop(0, "rgba(96, 16, 255, 0.16)");
    gradient.addColorStop(0.55, "rgba(96, 16, 255, 0.06)");
    gradient.addColorStop(1, "rgba(96, 16, 255, 0)");

    return gradient;
  };

  return new Chart(ctx, {
    type: "line",

    data: {
      labels,

      datasets: [
        {
          label: "Mean Score",
          data: scores,

          borderColor: "#6010FF",
          backgroundColor: (context) => {
            return createGradient(context.chart);
          },

          borderWidth: 2.5,

          fill: true,

          tension: 0.4,

          pointRadius: 4,
          pointHoverRadius: 6,

          pointBackgroundColor: "#FFFFFF",
          pointBorderColor: "#6010FF",
          pointBorderWidth: 2,

          pointHoverBackgroundColor: "#6010FF",
          pointHoverBorderColor: "#FFFFFF",
          pointHoverBorderWidth: 2,

          spanGaps: true,
        },
      ],
    },

    options: {
      responsive: true,

      // Very important because your parent controls the height.
      maintainAspectRatio: false,

      resizeDelay: 50,

      interaction: {
        mode: "index",
        intersect: false,
      },

      layout: {
        padding: {
          top: 10,
          right: 8,
          bottom: 4,
          left: 4,
        },
      },

      plugins: {
        legend: {
          display: false,
        },

        tooltip: {
          enabled: true,

          backgroundColor: "#0F172A",
          titleColor: "#CBD5E1",
          bodyColor: "#FFFFFF",

          padding: {
            top: 10,
            bottom: 10,
            left: 13,
            right: 13,
          },

          cornerRadius: 10,

          displayColors: false,

          titleFont: {
            size: 11,
            weight: "600",
          },

          bodyFont: {
            size: 12,
            weight: "600",
          },

          callbacks: {
            title: (items) => items[0]?.label ?? "",

            label: (item) => {
              const value = Number(item.raw);

              return Number.isFinite(value)
                ? `Mean Score  ${value.toFixed(2)} / 5.00`
                : "No score available";
            },
          },
        },
      },

      scales: {
        x: {
          grid: {
            display: false,
          },

          border: {
            display: false,
          },

          ticks: {
            color: "#94A3B8",

            font: {
              size: 9,
              weight: "500",
            },

            padding: 6,

            maxRotation: 0,
            minRotation: 0,

            autoSkip: true,
            maxTicksLimit: 5,

            callback: function (value) {
              const label = this.getLabelForValue(value);

              if (!label) return "";

              // Desktop / tablet
              if (window.innerWidth >= 640) {
                return label;
              }

              // Mobile
              const match = label.match(
                /(\d{4}-\d{4})\s*\((1st|2nd)\s+Semester\)/,
              );

              if (match) {
                return [match[1], match[2] === "1st" ? "1st Sem." : "2nd Sem."];
              }

              // Fallback
              return label.length > 14 ? label.substring(0, 14) + "…" : label;
            },
          },
        },

        y: {
          min: 3,
          max: 5,

          grid: {
            color: "rgba(226, 232, 240, 0.7)",
            drawTicks: false,
          },

          border: {
            display: false,
          },

          ticks: {
            color: "#94A3B8",

            font: {
              size: 10,
              weight: "500",
            },

            padding: 10,

            stepSize: 0.5,

            callback: (value) => Number(value).toFixed(1),
          },
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

export function createRadarChart(ctx, labels, scores) {
  return new Chart(ctx, {
    type: "radar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Score",
          data: scores,
          borderColor: "#534AB7",
          backgroundColor: "rgba(83, 74, 183, 0.15)",
          borderWidth: 2,
          pointBackgroundColor: "#534AB7",
          pointBorderColor: "#fff",
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointHoverBackgroundColor: "#fff",
          pointHoverBorderColor: "#534AB7",
          pointHoverBorderWidth: 2,
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
        },
      },
      scales: {
        r: {
          min: 0,
          max: 5,
          beginAtZero: true,
          ticks: {
            stepSize: 1,
            color: "#9E9A93",
            font: { size: 10 },
            backdropColor: "transparent",
          },
          grid: {
            color: "#E5E7EB",
          },
          angleLines: {
            color: "#E5E7EB",
          },
          pointLabels: {
            color: "#374151",
            font: { size: 11, weight: "500" },
          },
        },
      },
    },
  });
}
