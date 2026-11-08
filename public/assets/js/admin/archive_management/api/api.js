import { get, post } from "../../../services/http.js";

const BASE = "admin/archive/";

async function request(path, data) {
  const result =
    data === undefined ? await get(BASE + path) : await post(BASE + path, data);
  if (result.status !== "success") {
    throw new Error(result.message || "Unable to complete the request.");
  }
  return result;
}

export function fetchOverview() {
  return request("overview.php");
}

export function fetchArchivedPeriods(filters) {
  return request("list.php?" + new URLSearchParams(filters));
}

export function fetchRecoveryLogs(filters) {
  return request("logs.php?" + new URLSearchParams(filters));
}

export function fetchArchiveDetails(periodId) {
  return request("details.php?period_id=" + encodeURIComponent(periodId));
}

export function fetchRecoveryLogDetails(id) {
  return request("logs.php?id=" + encodeURIComponent(id));
}

export function verifyBackup(periodId) {
  return request("verify.php", { period_id: Number(periodId) });
}

export function previewBackup(formData) {
  return request("preview.php", formData);
}

export function restoreBackup(body) {
  return request("restore.php", body);
}

export function deleteArchive(periodId) {
  return request("delete.php", {
    period_id: Number(periodId),
  });
}
