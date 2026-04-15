import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import { loadPrograms } from "./table.js";

document.addEventListener("click", (e) => {
  const addProgram = e.target.closest(".addProgram");
  const editProgram = e.target.closest(".editProgram");
  const deleteProgram = e.target.closest(".deleteProgram");

  if (addProgram) {
    openModal("addProgramModal");
    return;
  }

  if (editProgram) {
    const program_id = editProgram.dataset.programId;
    const program_code = editProgram.dataset.programCode;
    const program_name = editProgram.dataset.programName;
    const department = editProgram.dataset.programDepartment;

    //populate information
    document.getElementById("edit_program_id").value = program_id || "";
    document.getElementById("edit_program_code").value = program_code || "";
    document.getElementById("edit_program_name").value = program_name || "";
    document.getElementById("edit_department").value = department || "";

    openModal("editProgramModal");
  }

  if (deleteProgram) {
    const program_id = deleteProgram.dataset.programId;

    showConfirmation({
      title: "Delete Program",
      message: "Are you sure you want to delete this program?",
      onConfirm: async () => {
        try {
          const res = await fetch(
            "/Smart-Eval/app/Controllers/programs/delete_program.php",
            {
              method: "POST",
              body: new URLSearchParams({ program_id: program_id }),
            },
          );

          const data = await res.json();

          if (data.status === "success") {
            alert(data.message);
            await loadPrograms();
          } else if (data.status === "warning") {
            alert(data.message);
            await loadPrograms();
          } else {
            alert(data.message);
          }
        } catch (err) {
          console.log(err);
        }
      },
    });
  }
});

//trigger close modal
document.addEventListener("click", (e) => {
  const closeBtn = e.target.closest("[data-close-modal]");
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});

//add program
const addProgramForm = document.getElementById("addProgramForm");
document.addEventListener("submit", (e) => {
  if (e.target !== addProgramForm) return;

  e.preventDefault();

  const formData = new FormData(addProgramForm);

  for (let [key, value] of formData.entries()) {
    console.log(key, value);
  }

  showConfirmation({
    title: "Add Program",
    message: "Are you sure you want to add this program?",
    onConfirm: async () => {
      try {
        const res = await fetch(
          "/Smart-Eval/app/Controllers/programs/add_program.php",
          {
            method: "POST",
            body: formData,
          },
        );

        const data = await res.json();

        if (data.status === "success") {
          alert(data.message);
          addProgramForm.reset();
          await loadPrograms();
          closeModal("addProgramModal");
        } else {
          alert(data.message);
        }
      } catch (err) {
        console.log(err);
      }
    },
  });
});

//edit program
const editForm = document.getElementById("editProgramForm");
editForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(editForm);

  showConfirmation({
    title: "Edit Program",
    message: "Are you sure you want to edit?",
    onConfirm: async () => {
      try {
        const res = await fetch(
          "/Smart-Eval/app/Controllers/programs/edit_program.php",
          {
            method: "POST",
            body: formData,
          },
        );

        const data = await res.json();

        if (data.status === "success") {
          alert(data.message);
          await loadPrograms();
          closeModal("editProgramModal");
        } else {
          alert(data.message);
        }
      } catch (err) {
        console.log(err);
      }
    },
  });
});
