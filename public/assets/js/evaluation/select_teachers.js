document.addEventListener("DOMContentLoaded", async () => {
  const grid = document.getElementById("teachersGrid");
  const proceedBtn = document.getElementById("proceedBtn");

  let selected = [];

  // ===== LOAD TEACHERS =====
  const res = await fetch(
    "/Smart-Eval/app/handlers/evaluation/get_teachers.php",
  );
  const data = await res.json();

  if (data.status !== "success") {
    alert(data.message);
    return;
  }

  data.data.forEach((teacher) => {
    const card = document.createElement("div");
    card.className =
      "border p-4 rounded cursor-pointer hover:shadow text-center";

    card.innerHTML = `
      <h3 class="font-semibold">${teacher.full_name}</h3>
      <p class="text-sm text-gray-500">${teacher.department}</p>
    `;

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

  function updateUI() {
    const count = document.getElementById("selectedCount");
    const list = document.getElementById("selectedList");
    const summary = document.getElementById("summarySection");

    if (selected.length > 0) {
      summary.classList.remove("hidden");
      count.textContent = selected.length;
      list.textContent = selected.join(", ");
      proceedBtn.disabled = false;
    } else {
      summary.classList.add("hidden");
      proceedBtn.disabled = true;
    }
  }

  // ===== SUBMIT =====
  proceedBtn.addEventListener("click", async () => {
    const res = await fetch(
      "/Smart-Eval/app/handlers/evaluation/select_teachers.php",
      {
        method: "POST",
        body: JSON.stringify({ teachers: selected }),
      },
    );

    const data = await res.json();

    if (data.status === "success") {
      window.location.href = data.redirect;
    } else {
      alert(data.message);
    }
  });
});
