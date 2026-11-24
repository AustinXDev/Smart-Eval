import { initCsvImport as initSharedCsvImport } from "../../shared/csv_import.js";
import { uploadCsv } from "../api/api.js";
import { loadStudents, loadStudentCard } from "../table.js";

/** Student CSV import (student_id, full_name, email, program_id, year_level) using the shared import modals. */
export function initCsvImport(department) {
  initSharedCsvImport({
    department,
    headers: ["student_id", "full_name", "email", "program_id", "year_level"],
    noun: "students",
    idField: "student_id",
    upload: uploadCsv,
    onImported: () => {
      loadStudents();
      loadStudentCard();
    },
  });
}
