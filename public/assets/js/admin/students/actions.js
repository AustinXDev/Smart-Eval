import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import { loadStudents, loadStudentCard } from "./table.js";
import { fetchAllPrograms } from "./student_api.js";
import { nameToInitials, formatStatus } from "../shared/utils.js";

const wrapper = document.getElementById("tableWrapper");
const department = wrapper ? wrapper.dataset.department : "";

const csvForm = document.getElementById("uploadCsvForm");
const csvInput = document.getElementById("csvInput");
const csvSummaryContent = document.getElementById("csvSummaryContent");

//populate the select program
async function populateProgramSelect(department) {
  const select = document.getElementById("programSelect");
  if (!select) return;

  select.innerHTML = '<option value="" disabled> SELECT Program </option>';

  const programs = await fetchAllPrograms(department);

  //console.log(programs);

  programs.forEach((program) => {
    const option = document.createElement("option");
    option.value = program.program_id;
    option.textContent = program.program_name;

    select.appendChild(option);
  });
}

//create populate program select for edit form
async function populateProgramSelectFormEdit(student) {
  const programSelect = document.getElementById("editProgramSelect");
  if (!programSelect) return;

  programSelect.innerHTML = '<option value="" disabled>Select Program</option>';

  const programs = await fetchAllPrograms(student.department);

  programs.forEach((program) => {
    const option = document.createElement("option");
    option.value = program.program_id;
    option.textContent = program.program_name;

    // select the current program
    if (program.program_id == student.program_id) {
      option.selected = true;
    }

    programSelect.appendChild(option);
  });
}

document.addEventListener("click", (e) => {
  const addBtn = e.target.closest(".add-btn");
  const csvBtn = e.target.closest(".csv-btn");
  const viewBtn = e.target.closest(".viewBtn");
  const editBtn = e.target.closest(".editBtn");
  const deleteBtn = e.target.closest(".deleteBtn");

  //Open Add Student Modal
  if (addBtn) {
    populateProgramSelect(department);
    openModal("addStudentModal");
    return;
  }

  // Open CSV Modal
  if (csvBtn) {
    openModal("uploadCSVModal");
    return;
  }

  // View Student Details
  if (viewBtn) {
    const student_id = viewBtn.dataset.studentId;
    fetch(`/Smart-Eval/app/handlers/students/get_students.php?id=${student_id}`)
      .then((res) => res.json())
      .then((data) => {
        //console.log(data);
        const student = data.student;

        if (student && student.student_id) {
          document.getElementById("studentAvatar").textContent = nameToInitials(
            student.full_name,
          );
          document.getElementById("studentName").textContent =
            student.full_name;
          document.getElementById("studentId").textContent = student.student_id;
          document.getElementById("studentStatus").textContent = formatStatus(
            student.is_active,
          ).text;
          document.getElementById("studentStatus").className =
            `inline-block mt-2 px-3 py-1 text-sm ${formatStatus(student.is_active).color} text-white rounded-md`;
          document.getElementById("studentIdDetail").textContent =
            student.student_id;
          document.getElementById("studentDepartment").textContent =
            student.department;
          document.getElementById("studentYearLevel").textContent =
            student.year_level;
          document.getElementById("studentProgram").textContent =
            student.program_name;
          document.getElementById("studentEmail").textContent = student.email;
          document.getElementById("studentStatusDetail").textContent =
            formatStatus(student.is_active).text;
          document.getElementById("studentStatusDetail").className =
            `inline-block mt-2 px-3 py-1 text-sm ${formatStatus(student.is_active).color} text-white rounded-md`;
          document.getElementById("resetPasswordBtn").dataset.studentId =
            student.student_id;
        }
      });
    openModal("viewStudentModal");
    return;
  }

  // Edit student Modal
  if (editBtn) {
    const student_id = editBtn.dataset.studentId;

    fetch(`/Smart-Eval/app/handlers/students/get_students.php?id=${student_id}`)
      .then((res) => res.json())
      .then(async (data) => {
        const student = data.student;

        if (student && student.student_id) {
          document.getElementById("editStudentAvatar").textContent =
            nameToInitials(student.full_name);
          document.getElementById("editStudentName").textContent =
            student.full_name;
          document.getElementById("oldStudentIdInput").value =
            student.student_id;
          document.getElementById("editStudentId").textContent =
            `Student ID: ${student.student_id}`;
          document.getElementById("editStudentStatus").textContent =
            formatStatus(student.is_active).text;
          document.getElementById("editStudentStatus").className =
            `inline-flex items-center gap-1 mt-2 px-3 py-1 text-xs ${formatStatus(student.is_active).color} text-white rounded-full shadow`;
          document.getElementById("editStudentIDInput").value =
            student.student_id;
          document.getElementById("editStudentNameInput").value =
            student.full_name;
          document.getElementById("editStudentEmailInput").value =
            student.email;
          document.getElementById("editStudentYearLevel").value =
            student.year_level;

          await populateProgramSelectFormEdit(student);
        }
      });

    openModal("editStudentModal");
    return;
  }

  ///Delete Student
  if (deleteBtn) {
    const student_id = deleteBtn.dataset.studentId;
    if (!student_id) return;

    deleteStudent(student_id);
    return;
  }
});

document.addEventListener("click", (e) => {
  const closeBtn = e.target.closest("[data-close-modal]");
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});

//handle add student fetch
const addForm = document.getElementById("addStudentForm");
addForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const formData = new FormData(addForm);

  showConfirmation({
    title: "Add Student",
    message: "Do you want to add this student?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/students/add_students.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          console.log(data);
          if (data.status === "success") {
            alert(data.message);
            closeModal("addStudentModal");
            addForm.reset();
            loadStudents();
            loadStudentCard();
          } else if (data.status === "inactive") {
            showConfirmation({
              title: "Reactive Student",
              message:
                "This student exist but is inactive. Do you want to reactive?",
              onConfirm: () => {
                fetch(
                  "/Smart-Eval/app/handlers/students/reactive_student.php",
                  {
                    method: "POST",
                    body: new URLSearchParams({ student_id: data.student_id }),
                  },
                )
                  .then((res) => res.json())
                  .then((resp) => {
                    if (resp.status === "success") {
                      alert(resp.message);
                      close("addStudentModal");
                      addForm.reset();
                      loadStudents();
                      loadStudentCard();
                    } else {
                      alert(`Error: ${resp.message}`);
                    }
                  })
                  .catch((err) => console.log(err));
              },
            });
          } else {
            alert(`Error: ${data.message}`);
          }
        })
        .catch((err) => console.log(err));
    },
  });
});

//drag and drop file in csv
const input = document.getElementById("csvInput");
const fileName = document.getElementById("fileName");
const dropZone = document.getElementById("dropZone");

// Show file name
input.addEventListener("change", () => {
  if (input.files.length > 0) {
    fileName.textContent = "Selected: " + input.files[0].name;
    fileName.classList.remove("hidden");
  }
});

// Drag & Drop
dropZone.addEventListener("dragover", (e) => {
  e.preventDefault();
  dropZone.classList.add("border-purple-500");
});

dropZone.addEventListener("dragleave", () => {
  dropZone.classList.remove("border-purple-500");
});

dropZone.addEventListener("drop", (e) => {
  e.preventDefault();
  input.files = e.dataTransfer.files;

  if (input.files.length > 0) {
    fileName.textContent = "Selected: " + input.files[0].name;
    fileName.classList.remove("hidden");
  }
});

//csv upload
csvForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const file = csvInput.files[0];
  if (!file) return alert("Please select a CSV file.");

  const formData = new FormData();
  formData.append("csv", file);
  formData.append("department", department);

  showConfirmation({
    title: "Upload CSV",
    message: "Are you sure you want to upload this CSV file?",
    onConfirm: () => {
      openModal("uploadLoading");
      const startTime = Date.now();

      fetch("/Smart-Eval/app/handlers/students/csv_upload.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          const elapsed = Date.now() - startTime;
          const delay = Math.max(3000 - elapsed, 0);

          let html = `<p><strong>Total Records:</strong> ${data.total}</p>
                    <p><strong>Successfully Imported:</strong> ${data.success}</p>
                    <p><strong>Failed:</strong> ${data.failed}</p>`;

          if (data.errors && data.errors.length > 0) {
            html += '<ul class="ml-4 list-disc text-red-600">';
            data.errors.forEach((err) => (html += `<li>${err}</li>`));
            html += "</ul>";
          }

          csvSummaryContent.innerHTML = html;

          closeModal("uploadCSVModal"); // close upload modal

          setTimeout(() => {
            closeModal("uploadLoading"); // hide loader after delay
            openModal("csvSummaryModal");
          }, delay);

          if (data.success > 0) {
            loadStudents();
            loadStudentCard();
          }
        })
        .catch((err) => console.log(err));
    },
  });
});

//Reset Student Password
const resetPasswordBtn = document.getElementById("resetPasswordBtn");
resetPasswordBtn.addEventListener("click", (e) => {
  const student_id = e.target.dataset.studentId;

  showConfirmation({
    title: "Reset Password",
    message:
      "Are you sure you want to reset the password of this student? The new password will be the same as their student ID.",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/students/reset_password.php", {
        method: "POST",
        body: JSON.stringify({ student_id: student_id }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            closeModal("viewStudentModal");
          } else {
            alert(`Error: ${data.message}`);
          }
        })
        .catch((err) => alert(err));
    },
  });
});

//Edit Student Form
const editForm = document.getElementById("editStudentForm");
editForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(editForm);
  const student_name = formData.get("full_name");

  console.log([...formData.entries()]);

  showConfirmation({
    title: "Edit Student",
    message: "Are you sure you want to save changes " + student_name + "?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/students/edit_student.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.text())
        .then((text) => {
          console.log(text);
          return JSON.parse(text);
        })
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            closeModal("editStudentModal");
            loadStudents();
          } else {
            alert(`Error: ${data.message}`);
          }
        })
        .catch((err) => console.error(err));
    },
  });
});

//Delete Request
function deleteStudent(id) {
  sendDeleteRequest(id, false);
}

function sendDeleteRequest(id, force = false) {
  showConfirmation({
    title: "Delete Student",
    message: "Are you sure you want to delete this student?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/students/delete_student.php", {
        method: "POST",
        body: new URLSearchParams({
          student_id: id,
          force: force ? 1 : 0,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          // ✅ SUCCESS
          if (data.status === "success") {
            alert(data.message);
            loadStudents();
            loadStudentCard();
          }

          // ⚠️ WARNING (second confirmation)
          else if (data.status === "warning") {
            showConfirmation({
              title: "Warning",
              message: data.message + " Do you want to proceed?",
              onConfirm: () => {
                sendDeleteRequest(id, true);
              },
            });
          } else {
            alert(`Error: ${data.message}`);
          }
        })
        .catch((err) => console.error(err));
    },
  });
}
