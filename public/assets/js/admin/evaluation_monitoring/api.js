import { get, post } from "../../services/http.js";

/** Queue reminder emails for unresponsive + incomplete students (NotificationQueueService). */
export async function queueReminders(department) {
  return post("notification/notification.php", { department });
}

export async function getParticipationMonitoring(department, params = {}) {
  const query = new URLSearchParams({ dept: department, ...params });
  return get(`admin/analytics/participation-monitoring.php?${query}`);
}
