document.addEventListener("DOMContentLoaded", async () => {
  const grid = document.getElementById("teachersGrid");
  const proceedBtn = document.getElementById("proceedBtn");

  let selected = []; // stores teacher_ids

  // LOAD AVAILABLE TEACHERS
  try {
    const res = await fetch(
      "/Smart-Eval/app/handlers/evaluation/get_teachers.php",
    );
    const data = await res.json();

    console.log(data);

    if (!data.success) {
      alert(data.error);
      return;
    }

    data.available_teachers.forEach((teacher) => {
      const card = document.createElement("div");
      const isAlreadySelected = selected.includes(teacher.teacher_id);

      card.className = [
        "border p-4 rounded cursor-pointer hover:shadow text-center transition-all duration-200",
        isAlreadySelected ? "bg-amber-100 border-amber-500" : "",
      ]
        .join(" ")
        .trim();

      card.innerHTML = `
        <h3 class="font-semibold">${teacher.full_name}</h3>
        <p class="text-sm text-gray-500">${teacher.department}</p>
      `;

      // Store teacher_id as data attribute
      card.setAttribute("data-teacher-id", teacher.teacher_id);

      // Toggle by teacher_id
      card.addEventListener("click", () => {
        const id = teacher.teacher_id;

        if (selected.includes(id)) {
          selected = selected.filter((t) => t !== id);
          card.classList.remove("bg-amber-100", "border-amber-500");
        } else {
          selected.push(id);
          card.classList.add("bg-amber-100", "border-amber-500");
        }

        updateUI();
      });

      grid.appendChild(card);
    });

    updateUI();
  } catch (err) {
    console.error("Failed to load teachers:", err);
    alert("Failed to load available teachers. Please try again.");
  }

  function updateUI() {
    const count = document.getElementById("selectedCount");
    const list = document.getElementById("selectedList");
    const summary = document.getElementById("summarySection");

    if (selected.length > 0) {
      summary.classList.remove("hidden");
      count.textContent = selected.length;

      // Show teacher names in summary
      const names = [];
      grid.querySelectorAll("[data-teacher-id]").forEach((card) => {
        if (selected.includes(parseInt(card.dataset.teacherId))) {
          names.push(card.querySelector("h3").textContent.trim());
        }
      });
      list.textContent = names.join(", ");

      proceedBtn.disabled = false;
    } else {
      summary.classList.add("hidden");
      proceedBtn.disabled = true;
    }
  }

  // SUBMIT
  proceedBtn.addEventListener("click", async () => {
    if (selected.length === 0) {
      alert("Please select at least one teacher.");
      return;
    }

    proceedBtn.disabled = true;
    proceedBtn.textContent = "Saving...";

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/select_teachers.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ teachers: selected }), // ← Send teacher_ids
        },
      );

      const data = await res.json();

      if (data.success) {
        window.location.href = "/Smart-Eval/views/student/evaluation.view.php";
      } else {
        alert(data.error);
        proceedBtn.disabled = false;
        proceedBtn.textContent = "Proceed";
      }
    } catch (err) {
      console.error("Failed to save selection:", err);
      alert("Failed to save teacher selection. Please try again.");
      proceedBtn.disabled = false;
      proceedBtn.textContent = "Proceed";
    }
  });
});
