import { queueReminders } from "./api.js";

const COOLDOWN_SECONDS = 60;

/**
 * "Notify All" button: queues evaluation reminders for every student who still
 * has evaluations remaining in the active period. Absent for roles without access.
 */
export function initNotifyAll(department, departmentLabel) {
  const button = document.getElementById("notify-all");
  if (!button) return { update() {} };

  const label = button.querySelector("[data-label]");
  let remaining = 0;
  let hasPeriod = false;
  let sending = false;
  let cooldownUntil = 0;

  const render = () => {
    const secondsLeft = Math.ceil((cooldownUntil - Date.now()) / 1000);

    if (sending) {
      label.textContent = "Queuing…";
    } else if (secondsLeft > 0) {
      label.textContent = `Notify again in ${secondsLeft}s`;
    } else {
      label.textContent = remaining > 0 ? `Notify All (${remaining})` : "Notify All";
    }

    button.disabled = sending || secondsLeft > 0 || !hasPeriod || remaining === 0;
    button.title = !hasPeriod
      ? "No active evaluation period"
      : remaining === 0
        ? "Every eligible student has completed their evaluations"
        : `Send a reminder to ${remaining} student${remaining === 1 ? "" : "s"} with evaluations remaining`;
  };

  const startCooldown = () => {
    cooldownUntil = Date.now() + COOLDOWN_SECONDS * 1000;
    const timer = setInterval(() => {
      render();
      if (Date.now() >= cooldownUntil) clearInterval(timer);
    }, 1000);
    render();
  };

  const send = async () => {
    if (sending) return;
    sending = true;
    render();

    try {
      const result = await queueReminders(department);
      const queued = Number(result?.data?.processed) || 0;
      StatusModal.show(
        "Reminders Queued",
        queued > 0
          ? `${queued} reminder${queued === 1 ? "" : "s"} queued for ${departmentLabel} students with pending evaluations. Emails are sent in the background.`
          : `No ${departmentLabel} students currently need a reminder.`,
        "success",
      );
      startCooldown();
    } catch (error) {
      StatusModal.show("Unable to Send Reminders", error.message || "Please try again in a moment.", "error");
    } finally {
      sending = false;
      render();
    }
  };

  button.addEventListener("click", () => {
    if (button.disabled) return;
    StatusModal.confirm(
      "Notify All Remaining Students",
      `Send an evaluation reminder email to ${remaining} ${departmentLabel} student${remaining === 1 ? "" : "s"} ` +
        "who are unresponsive or have incomplete evaluations?",
      send,
    );
  });

  render();

  return {
    /** Sync with the latest monitoring data (period + remaining participants). */
    update(period, summary = {}) {
      hasPeriod = Boolean(period);
      remaining = Number(summary.remaining) || 0;
      render();
    },
  };
}
