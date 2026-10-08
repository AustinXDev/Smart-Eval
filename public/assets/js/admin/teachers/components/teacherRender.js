export function renderViewDetails(teacher) {
  if (!teacher || !teacher.teacher_id) return;

  console.log(teacher);

  setText("name", teacher.full_name);
  setText("id", teacher.employee_id);
  setText("department", `${teacher.department.toUpperCase()} Department`);
  setText("email", teacher.email);
  setValue("handle_teacher_id", teacher.teacher_id);

  const img = document.querySelector("#viewDetails img");
  if (img) {
    img.src = teacher.image_path
      ? `${window.BASE_URL}uploads/teachers/${teacher.image_path}`
      : `${window.BASE_URL}uploads/teachers/default_teacher.png`;
  }
}

export function renderEditForm(teacher) {
  if (!teacher || !teacher.teacher_id) return;

  console.log(teacher);

  setText("header-name", teacher.full_name);
  setValue("teacherId", teacher.teacher_id);
  setValue("employee_Id", teacher.employee_id);
  setValue("teacherName", teacher.full_name);
  setValue("teacherEmail", teacher.email);

  const img = document.getElementById("editTeacherPhotoPreview");

  if (img) {
    img.src = teacher.image_path
      ? `${window.BASE_URL}uploads/teachers/${teacher.image_path}`
      : `${window.BASE_URL}uploads/teachers/default_teacher.png`;
  }
}

function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function setValue(id, value) {
  const el = document.getElementById(id);
  if (el) el.value = value;
}
