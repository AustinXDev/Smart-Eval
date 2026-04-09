import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import {
  loadTeachers,
  loadTeacherHandles,
  loadCard,
} from "../teachers/table.js";
import { fetchAllPrograms } from "../shared/program_api.js";

let teacherId; //global teacher id
const wrapper = document.getElementById("tableWrapper");
const department = wrapper ? wrapper.dataset.department : "";

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

// Open Add Teacher modal
document.addEventListener("click", (e) => {
  const addBtn = e.target.closest(".add-btn");
  const viewBtn = e.target.closest(".viewBtn");
  const editBtn = e.target.closest(".editBtn");
  const deleteBtn = e.target.closest(".deleteBtn");

  if (addBtn) {
    openModal("addTeacherModal");
  }

  // View Teacher Details
  if (viewBtn) {
    teacherId = viewBtn.dataset.teacherId;

    populateProgramSelect(department);

    fetch(`/Smart-Eval/app/handlers/teachers/get_teachers.php?id=${teacherId}`)
      .then((res) => res.json())
      .then((data) => {
        const teacher = data[0];

        if (teacher && teacher.teacher_id) {
          document.getElementById("name").textContent = teacher.full_name;
          document.getElementById("id").textContent = teacher.employee_id;
          document.getElementById("department").textContent =
            teacher.department.toUpperCase() + " " + "Department";
          document.getElementById("email").textContent = teacher.email;
          document.getElementById("handle_teacher_id").value =
            teacher.teacher_id;

          loadTeacherHandles(teacher.teacher_id);

          const img = document.querySelector("#viewDetails img");
          img.src = teacher.image_path
            ? `/Smart-Eval/public/uploads/teachers/${teacher.image_path}`
            : `/Smart-Eval/public/uploads/teachers/default_teacher.png`;
        }
      });

    openModal("viewDetails");
  }

  // Edit Teacher Details
  if (editBtn) {
    teacherId = editBtn.dataset.teacherId;

    console.log(teacherId);
    fetch(`/Smart-Eval/app/handlers/teachers/get_teachers.php?id=${teacherId}`)
      .then((res) => res.json())
      .then((data) => {
        const teacher = data[0];

        if (teacher && teacher.teacher_id) {
          document.getElementById("header-name").textContent =
            teacher.full_name;
          document.getElementById("teacherId").value = teacher.teacher_id;
          document.getElementById("employee_Id").value = teacher.employee_id;
          document.getElementById("teacherName").value = teacher.full_name;
          document.getElementById("teacherEmail").value = teacher.email;
        }
      });

    openModal("editTeacherModal");
  }

  //Delete Teacher
  if (deleteBtn) {
    const teacher_Id = deleteBtn.dataset.teacherId;

    if (!teacher_Id) return;

    showConfirmation({
      title: "Delete Teacher",
      message: "Are you sure you want ot delete this teacher?",
      onConfirm: () => {
        fetch("/Smart-Eval/app/handlers/teachers/delete_teacher.php", {
          method: "POST",
          body: new URLSearchParams({ teacher_id: teacher_Id }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              alert(data.message);
              loadTeachers();
              loadCard();
            } else {
              alert(data.message);
            }
          })
          .catch((err) => console.error(err));
      },
    });
  }
  return;
});

document.addEventListener("click", (e) => {
  const closeBtn = e.target.closest("[data-close-modal]");
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});

// Add Teacher form submission
const form = document.getElementById("addTeacherForm");
form.addEventListener("submit", (e) => {
  e.preventDefault();
  const formData = new FormData(form);

  showConfirmation({
    title: "Add Teacher",
    message: "Are you sure you want to add this teacher?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/teachers/add_teacher.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            closeModal("addTeacherModal");
            form.reset();
            loadTeachers();
            loadCard();
          } else {
            alert(data.errors ? data.errors.join("\n") : data.message);
          }
        })
        .catch((err) => console.error(err));
    },
  });
});

//Add handles
const Handleform = document.getElementById("addHandleForm");

Handleform.addEventListener("submit", (e) => {
  e.preventDefault();
  const formData = new FormData(Handleform);

  showConfirmation({
    title: "Add Course and Year Handle",
    message: "Are you sure want to add this teacher?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/teachers/add_handle.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            //closeModal('viewDetails');
            Handleform.reset();
            loadTeacherHandles(teacherId);
            loadCard();
          } else {
            alert(data.errors ? data.errors.join("\n") : data.message);
          }
        })
        .catch((err) => console.log(err));
    },
  });
});

//delete handles
document.addEventListener("click", (e) => {
  const deleteBtn = e.target.closest(".deleteHandleBtn");
  if (!deleteBtn) return;

  const year = deleteBtn.dataset.year;
  const program = deleteBtn.dataset.program;
  const department = deleteBtn.dataset.department;

  console.log(department);

  showConfirmation({
    title: "Delete Handle",
    message: `Are you sure you want to remove ${program} - ${year} year from this teacher?`,
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/teachers/delete_handle.php", {
        method: "POST",
        body: JSON.stringify({
          teacher_id: teacherId,
          year_level: year,
          program_name: program,
          department: department,
        }),
        headers: {
          "Content-Type": "application/json",
        },
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            loadTeacherHandles(teacherId);
          } else if (data.status === "warning") {
            if (confirm(data.message)) {
              fetch(`/Smart-Eval/app/handlers/teachers/delete_handle.php`, {
                method: "POST",
                body: JSON.stringify({
                  teacher_id: teacherId,
                  year_level: year,
                  program_name: program,
                  department: department,
                  force_delete: true,
                }),
                headers: {
                  "Content-Type": "application/json",
                },
              })
                .then((r) => r.json())
                .then((res) => {
                  if (res.status === "success") {
                    alert(res.message);
                    loadTeacherHandles(teacherId);
                    loadCard();
                  }
                });
            }
          } else {
            alert(data.message);
          }
        });
    },
  });
});

//Edit Teacher
const editForm = document.getElementById("editTeacherForm");
editForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const formData = new FormData(editForm);

  showConfirmation({
    title: "Edit Teacher",
    message: "Are you sure do you want to edit this teacher?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/teachers/edit_teacher.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          console.log(data);
          if (data.status === "success") {
            alert(data.message);
            closeModal("editTeacherModal");
            editForm.reset();
            loadTeachers();
          } else {
            alert(data.message);
          }
        })
        .catch((err) => console.error(err));
    },
  });
});

window.addEventListener("DOMContentLoaded", () => {
  loadCard();
});
