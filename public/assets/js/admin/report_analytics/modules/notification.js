import { state } from "./state.js";

export async function processNotificationBatches(deptParam) {
  let finished = false;
  const btn = document.getElementById("btn-notify-all");

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

  while (!finished) {
    const processUrl = `/Smart-Eval/app/Controllers/notification/NotificationController.php?action=process&dept=${deptParam}`;
    try {
      const response = await fetch(processUrl);
      const data = await response.json();

      if (data.status === "finished") {
        finished = true;
        alert("All emails sent successfully!");
        startNotifyCooldown(btn);
      } else if (data.status === "processing") {
        console.log(`Sent batch... Total so far: ${data.sent}`);
      } else {
        finished = true;
        alert("Batch processing stopped: " + data.message);
        resetNotifyButton(btn);
      }
    } catch (error) {
      finished = true;
      console.error("Fetch error:", error);
      resetNotifyButton(btn);
    }
  }
}

function resetNotifyButton(btn) {
  btn.disabled = false;
  btn.innerHTML = "Notify All Non-participants";
}

function startNotifyCooldown(btn) {
  let secondsLeft = 60;
  btn.innerHTML = `Wait ${secondsLeft}s to re-notify`;
  btn.classList.add("opacity-50", "cursor-not-allowed");

  const cooldown = setInterval(() => {
    secondsLeft--;
    btn.innerHTML = `Wait ${secondsLeft}s to re-notify`;

    if (secondsLeft <= 0) {
      clearInterval(cooldown);
      btn.classList.remove("opacity-50", "cursor-not-allowed");
      resetNotifyButton(btn);
    }
  }, 1000);
}

export function updateNotifyButtonState() {
  const notifyButton = document.getElementById("btn-notify-all");
  if (!notifyButton) return;

  if (state.isActive === true) {
    notifyButton.disabled = false;
    notifyButton.classList.remove(
      "opacity-50",
      "cursor-not-allowed",
      "pointer-events-none",
    );
    notifyButton.classList.add("cursor-pointer");
  } else {
    notifyButton.disabled = true;
    notifyButton.classList.add(
      "opacity-50",
      "cursor-not-allowed",
      "pointer-events-none",
    );
    notifyButton.classList.remove("cursor-pointer");
  }
}
