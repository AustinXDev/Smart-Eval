// ===== Global Elements (FIXED SCOPE) =====
const optionCards = document.querySelectorAll(".option-card");
const selectedTypeInput = document.getElementById("selectedTypeInput");
const proceedBtn = document.getElementById("proceedBtn");
const btnText = document.getElementById("btnText");
const btnLoader = document.getElementById("btnLoader");

// ===== INIT =====
document.addEventListener("DOMContentLoaded", async () => {
  // Load program
  const program = document.getElementById("program");

  if (program) {
    const program_id = program.dataset.programId;
    console.log("Program ID:", program_id);
    await populateStudentProgram(program_id);
  }

  // Card selection
  optionCards.forEach((card) => {
    card.addEventListener("click", () => handleCardClick(card));
  });
});

// ===== HANDLE CARD CLICK =====
function handleCardClick(card) {
  resetCards(optionCards);

  const title = card.querySelector("h3").innerText;
  const icon = card.querySelector("div > i")?.parentElement;

  let type = "";

  if (title === "Regular Student") {
    type = "Regular";
    icon?.classList.add("bg-blue-300");
  } else {
    type = "Irregular";
    icon?.classList.add("bg-amber-300");
  }

  // Highlight
  card.classList.add("border-blue-500", "shadow-xl", "bg-blue-50", "scale-105");

  // Store value
  selectedTypeInput.value = type;

  console.log("Selected:", type);

  // Enable button
  proceedBtn.disabled = false;

  // Dynamic text
  btnText.textContent =
    type === "Regular"
      ? "Proceed with Assigned Teachers"
      : "Proceed to Select Teachers";
}

// ===== RESET CARDS =====
function resetCards(cards) {
  cards.forEach((c) => {
    c.classList.remove(
      "border-blue-500",
      "shadow-xl",
      "bg-blue-50",
      "scale-105",
    );

    const icon = c.querySelector("div > i")?.parentElement;
    icon?.classList.remove("bg-blue-300", "bg-amber-300");
  });
}

// ===== FETCH PROGRAM =====
async function populateStudentProgram(id) {
  try {
    const res = await fetch(
      "/Smart-Eval/app/handlers/evaluation/get_program.php",
      {
        method: "POST",
        body: new URLSearchParams({ program_id: id }),
      },
    );

    const data = await res.json();

    if (data.status === "success") {
      console.log("Program:", data.data);

      const programName = document.getElementById("programName");
      if (programName) {
        programName.textContent = data.data.program_name;
      }
    } else {
      console.error("Error:", data.message);
    }
  } catch (error) {
    console.error("Fetch error:", error);
  }
}

// ===== FORM SUBMIT =====
document
  .getElementById("enrollmentForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!selectedTypeInput.value) {
      alert("Please select an option");
      return;
    }

    // Loading state
    proceedBtn.disabled = true;
    btnText.textContent = "Processing...";
    btnLoader.classList.remove("hidden");

    try {
      const res = await fetch(
        "/Smart-Eval/app/handlers/evaluation/select_enrollment.php",
        {
          method: "POST",
          body: new URLSearchParams({
            enrollment_type: selectedTypeInput.value,
          }),
        },
      );

      const data = await res.json();

      console.log("Response:", data);

      if (data.status === "success") {
        window.location.href = data.redirect;
      } else {
        alert(data.message);
        resetButton();
      }
    } catch (err) {
      console.error(err);
      alert("Something went wrong");
      resetButton();
    }
  });

// ===== RESET BUTTON STATE =====
function resetButton() {
  proceedBtn.disabled = false;
  btnText.textContent = "Proceed";
  btnLoader.classList.add("hidden");
}
