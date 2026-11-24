import { get, post } from "../../services/http.js";

// Account Management API (super admin + PIN). Errors are thrown by http.js with
// error.status and error.data (422 → data.errors, 423 → PIN required).

export const listAccounts = () => get("admin/accounts/list.php");

export const createAccount = (data) => post("admin/accounts/create.php", data);

export const updateAccount = (data) => post("admin/accounts/update.php", data);

export const deleteAccount = (adminId) =>
  post("admin/accounts/delete.php", { admin_id: Number(adminId) });

/** action: "verify" | "set" | "lock" */
export const pinAction = (data) => post("admin/accounts/pin.php", data);

/** The PIN unlock expired (or was locked in another tab): show the PIN gate again. */
export function isPinRequired(error) {
  return error?.status === 423 || error?.data?.code === "pin_required";
}
