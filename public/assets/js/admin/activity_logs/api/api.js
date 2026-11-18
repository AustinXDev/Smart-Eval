import { get } from "../../../services/http.js";

/**
 * Fetch one page of activity logs.
 * @param {Record<string, string|number>} params DataTables paging + filters.
 */
export async function getActivityLogs(params = {}) {
  const query = new URLSearchParams(params);
  const response = await get(`admin/activity_logs/list.php?${query}`);

  if (response.status !== "success" || !Array.isArray(response.data)) {
    throw new Error(response.message || "Unable to load activity logs.");
  }

  return response;
}
