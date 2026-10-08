import { get } from "../../services/http.js";

// Get all programs by department
export async function fetchAllPrograms(department) {
  try {
    const data = await get(
      `program/get_by_department.php${
        department ? `?department=${encodeURIComponent(department)}` : ""
      }`,
    );

    return data.programs || [];
  } catch (error) {
    console.error("Error fetching all programs:", error);

    return [];
  }
}
