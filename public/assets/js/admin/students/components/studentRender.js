import { nameToInitials, formatStatus } from "../../shared/utils.js";
import { populateEditProgramSelect } from "./programSelect.js";

export function renderViewModal(student) {
  if (!student || !student.student_id) return;

  const status = formatStatus(student.is_active);

  setText("studentAvatar", nameToInitials(student.full_name));
  setText("studentName", student.full_name);
  setText("studentId", student.student_id);
  setStatusBadge("studentStatus", status);

  setText("studentIdDetail", student.student_id);
  setText("studentDepartment", student.department);
  setText("studentYearLevel", student.year_level);
  setText("studentProgram", student.program_name);
  setText("studentEmail", student.email);
  setStatusBadge("studentStatusDetail", status);

  const resetBtn = document.getElementById("resetPasswordBtn");
  if (resetBtn) resetBtn.dataset.studentId = student.student_id;
}

export async function renderEditModal(student) {
  if (!student || !student.student_id) return;

  setText("editStudentAvatar", nameToInitials(student.full_name));
  setText("editStudentName", student.full_name);
  setText("editStudentId", `Student ID: ${student.student_id}`);
  setStatusPill("editStudentStatus", formatStatus(student.is_active));

  setValue("oldStudentIdInput", student.student_id);
  setValue("editStudentIDInput", student.student_id);
  setValue("editStudentNameInput", student.full_name);
  setValue("editStudentEmailInput", student.email);
  setValue("editStudentYearLevel", student.year_level);

  await populateEditProgramSelect(student);
}

// helpers
function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function setValue(id, value) {
  const el = document.getElementById(id);
  if (el) el.value = value;
}

// matches the "detail page" style badge (studentStatus / studentStatusDetail)
function setStatusBadge(id, isActive) {
  const el = document.getElementById(id);
  if (!el) return;

  const text = isActive ? "Active" : "Inactive";
  const color = isActive ? "bg-green-500" : "bg-gray-500";

  el.textContent = text;
  el.className = `inline-block mt-2 px-3 py-1 text-sm ${color} text-white rounded-md`;
}

// matches the smaller pill style used on the edit modal
function setStatusPill(id, isActive) {
  const el = document.getElementById(id);
  if (!el) return;

  const text = isActive ? "Active" : "Inactive";
  const color = isActive ? "bg-green-500" : "bg-gray-500";

  el.textContent = text;
  el.className = `inline-flex items-center gap-1 mt-2 px-3 py-1 text-xs ${color} text-white rounded-full shadow`;
}
