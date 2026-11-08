import { logout } from "../shared/logout.js";
import { initArchiveTable } from "./table.js";
import { loadOverview } from "./components/overview.js";
import { initNavigation } from "./components/navigation.js";
import { initDetailsModal } from "./components/detailsModal.js";
import { initBackupActions } from "./components/backupActions.js";
import { initBackupForm } from "./components/backupForm.js";

window.logout = logout;

initArchiveTable();
initNavigation();
initDetailsModal();
initBackupActions();
initBackupForm();
loadOverview();
