import { initCsvImport as initSharedCsvImport } from "../../shared/csv_import.js";
import { importTeachersCsv } from "../api/api.js";
import { loadTeachers, loadCard } from "../table.js";

/** Teacher CSV import (employee_id, full_name, email) using the shared import modals. */
export function initCsvImport(department) {
  initSharedCsvImport({
    department,
    headers: ["employee_id", "full_name", "email"],
    noun: "teachers",
    idField: "employee_id",
    upload: importTeachersCsv,
    onImported: () => {
      loadTeachers();
      loadCard();
    },
    extraText: ({ reactivated = 0 }) =>
      reactivated
        ? `${reactivated} of the imported ${reactivated === 1 ? "teacher was a deactivated record and has" : "teachers were deactivated records and have"} been reactivated.`
        : "",
  });
}
