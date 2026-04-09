document.addEventListener("DOMContentLoaded", async () => {
  try {
    const res = await fetch(
      "/Smart-Eval/app/handlers/evaluation/evaluation_done.php",
    );
    const data = await res.json();

    if (!data.success) {
      showError(data.error);
      return;
    }

    // Hide loading
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";

    // Set period name
    document.getElementById("period-name").textContent = data.period_name;

    if (data.total_evaluated === 0) {
      // Show empty state
      document.getElementById("empty-state").style.display = "block";
      document.getElementById("teachers-list").style.display = "none";
    } else {
      // Show teachers list
      document.getElementById("empty-state").style.display = "none";
      document.getElementById("teachers-list").style.display = "block";

      // Update counts
      document.getElementById("total-count").textContent = data.total_evaluated;
      document.getElementById("plural-s").textContent =
        data.total_evaluated !== 1 ? "s" : "";

      // Render teachers
      const teachersList = document.querySelector("#teachers-list .space-y-2");
      teachersList.innerHTML = data.evaluated_teachers
        .map(
          (teacher, index) => `
                <div class="teacher-item flex items-center justify-between p-3 hover:bg-gray-50 rounded stagger-${Math.min(index + 1, 10)}">
                    <div class="flex items-center flex-1">
                        <div class="teacher-avatar" style="width: 35px; height: 35px; font-size: 1rem;">
                            ${teacher.full_name.charAt(0).toUpperCase()}
                        </div>
                        <div class="ml-3 flex-1">
                            <div class="font-medium text-gray-800 text-sm">${escapeHtml(teacher.full_name)}</div>
                            <div class="text-xs text-gray-500">${escapeHtml(teacher.department)}</div>
                        </div>
                    </div>
                    <div class="text-green-500 font-bold">✓</div>
                </div>
            `,
        )
        .join("");

      // Apply stagger animation
      document.querySelectorAll(".teacher-item").forEach((el, i) => {
        el.style.animation = `slideUp 0.5s ease-out ${(i + 1) * 0.1}s forwards`;
        el.style.opacity = "0";
      });
    }
  } catch (err) {
    console.error("Error:", err);
    showError("Failed to load evaluation data. Please try again.");
  }
});

function showError(message) {
  document.getElementById("loading").style.display = "none";
  document.getElementById("content").style.display = "block";
  document.getElementById("error-state").style.display = "block";
  document.getElementById("error-message").textContent = message;
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}
