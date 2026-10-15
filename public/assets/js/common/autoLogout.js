const defaults = { idleTimeout: 120000, warningDuration: 30000 };
let state;

function api(path) {
  return `${String(window.API_URL).replace(/\/+$/, "")}/${path.replace(/^\/+/, "")}`;
}

async function json(url, options = {}) {
  const response = await fetch(url, {
    credentials: "same-origin",
    cache: "no-store",
    ...options,
    signal: AbortSignal.any([state.abort.signal, AbortSignal.timeout(10000)]),
  });
  const result = await response.json();
  if (!response.ok) {
    const error = new Error(result.message || "Unable to verify your session.");
    error.status = response.status;
    throw error;
  }
  return result;
}

function hide(s) {
  if (!s.modal.open) return;
  s.modal.classList.add("opacity-0");
  clearTimeout(s.exitTimer);
  s.exitTimer = setTimeout(() => {
    s.modal.close();
    if (s.focus?.isConnected) s.focus.focus({ preventScroll: true });
  }, 150);
}

function show(s) {
  clearTimeout(s.exitTimer);
  if (!s.modal.open) {
    s.focus = document.activeElement;
    s.modal.showModal();
  }
  s.modal.classList.remove("opacity-0");
}

function schedule(s) {
  clearTimeout(s.timer);
  if (state !== s || s.loggingOut || !s.verified) return;
  const now = Date.now();
  const warningAt = s.lastActivity + s.options.idleTimeout;
  const end = Math.min(warningAt + s.options.warningDuration, s.serverDeadline);
  if (now >= end) {
    void logout(s);
    return;
  }
  if (now >= warningAt) {
    show(s);
    const seconds = Math.ceil((end - now) / 1000);
    const value = `${String(Math.floor(seconds / 60)).padStart(2, "0")}:${String(seconds % 60).padStart(2, "0")}`;
    if (s.countdown.textContent !== value) s.countdown.textContent = value;
  }
  s.timer = setTimeout(
    () => schedule(s),
    Math.max(1, Math.min(end - now, now < warningAt ? warningAt - now : 250)),
  );
}

async function verify(s, resume = false) {
  if (s.checking || s.loggingOut) return;
  s.checking = true;
  try {
    const result = await json(s.options.statusUrl);
    if (state !== s || s.loggingOut) return;
    // A late response must never resurrect a locally expired session.
    if (
      Date.now() >=
      Math.min(
        s.lastActivity + s.options.idleTimeout + s.options.warningDuration,
        s.serverDeadline,
      )
    ) {
      void logout(s);
      return;
    }
    if (result.status !== "success" || !Number.isFinite(result.expiresIn))
      throw new Error("Unable to verify your session.");
    s.serverDeadline = Date.now() + result.expiresIn * 1000;
    s.verified = true;
    s.message.textContent = "";
    if (resume) {
      s.lastActivity = Date.now();
      hide(s);
    }
    schedule(s);
  } catch (error) {
    if (state !== s || error.name === "AbortError") return;
    if (error.status === 401) {
      window.location.replace(s.options.loginUrl);
      return;
    }
    show(s);
    s.message.textContent =
      "Unable to verify your session. Refresh to try again.";
    // Keep the original deadline even when the network is unavailable.
    s.verified = true;
    schedule(s);
  } finally {
    s.checking = false;
  }
}

async function logout(s) {
  if (state !== s || s.loggingOut) return;
  s.loggingOut = true;
  clearTimeout(s.timer);
  show(s);
  s.countdown.textContent = "00:00";
  s.button.disabled = true;
  s.message.textContent = "Securely logging you out…";
  try {
    // Fetch a fresh CSRF token: another tab or an expired session can invalidate
    // the token cached by the normal HTTP helper. Use the same existing endpoint.
    const security = await json(api("csrf.php"));
    if (state !== s) return;
    const result = await json(api(s.options.logoutUrl), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": security.token,
      },
      body: "{}",
    });
    if (state !== s) return;
    if (result.status !== "success") throw new Error("Logout failed");
    window.location.replace(s.options.loginUrl);
  } catch (error) {
    if (state !== s || error.name === "AbortError") return;
    if (error.status === 401) {
      window.location.replace(s.options.loginUrl);
      return;
    }
    // Do not claim that a failed network request destroyed the PHP session.
    s.message.textContent =
      "Logout could not be completed. Check your connection and retry.";
    s.button.textContent = "Retry Logout";
    s.button.disabled = false;
    s.failed = true;
  }
}

function activity(s, event) {
  if (!event.isTrusted || s.loggingOut || !s.verified) return;
  const now = Date.now();
  if (
    now >=
    Math.min(
      s.lastActivity + s.options.idleTimeout + s.options.warningDuration,
      s.serverDeadline,
    )
  ) {
    void logout(s);
    return;
  }
  // Let the button's click perform the required reload, including touch/keyboard.
  if (s.button.contains(event.target)) return;
  if (s.modal.open) {
    if (event.type === "keydown" && event.key === "Tab") return;
    void verify(s, true);
  } else if (now - s.lastActivity >= 250) {
    s.lastActivity = now;
    schedule(s);
  }
}

const AutoLogout = {
  init(options = {}) {
    if (state) return this;

    const settings = { ...defaults, ...options };

    for (const key of ["idleTimeout", "warningDuration"]) {
      if (!Number.isFinite(settings[key]) || settings[key] <= 0)
        throw new TypeError(`${key} must be positive milliseconds`);
    }

    if (!settings.loginUrl || !settings.logoutUrl || !settings.statusUrl)
      throw new TypeError("loginUrl, logoutUrl and statusUrl are required");

    const modal = document.createElement("dialog");

    modal.id = "smart-eval-auto-logout";

    modal.className =
      "m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border-0 bg-white p-0 text-slate-800 shadow-2xl backdrop:bg-slate-950/60 backdrop:backdrop-blur-sm opacity-0 transition-opacity duration-150 motion-reduce:transition-none";

    modal.setAttribute("aria-labelledby", "auto-logout-title");

    modal.setAttribute("aria-describedby", "auto-logout-description");

    modal.innerHTML = `
      <div class="p-6 text-center sm:p-8">
        <div class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600"><i class="fas fa-clock text-xl" aria-hidden="true"></i></div>
        <h2 id="auto-logout-title" class="text-xl font-semibold tracking-tight">Your Session Is About to Expire</h2>
        <p id="auto-logout-description" class="mt-3 text-sm leading-relaxed text-slate-600">You've been inactive for a while. For your security, you'll be automatically logged out unless you resume your session.</p>
        <p role="timer" aria-label="Time until automatic logout" class="my-6 rounded-xl bg-amber-50 py-4 text-4xl font-semibold tabular-nums text-amber-700" data-countdown>00:30</p>
        <button type="button" autofocus class="w-full rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-violet-300 active:scale-95 disabled:opacity-60">Stay Logged In</button>
        <p class="mt-4 text-xs leading-relaxed text-slate-500">You'll be securely logged out when the countdown reaches zero.</p>
        <p role="status" class="mt-3 text-sm text-amber-700" data-message></p>
      </div>`;

    document.body.append(modal);

    const s = (state = {
      options: settings,
      modal,
      button: modal.querySelector("button"),
      countdown: modal.querySelector("[data-countdown]"),
      message: modal.querySelector("[data-message]"),
      lastActivity: Date.now(),
      serverDeadline: Infinity,
      abort: new AbortController(),
    });

    const listener = { signal: s.abort.signal };

    for (const name of ["pointerdown", "keydown", "scroll", "touchstart"]) {
      document.addEventListener(name, (event) => activity(s, event), {
        ...listener,
        capture: true,
        passive: true,
      });
    }

    s.button.addEventListener(
      "click",
      () => {
        if (s.failed) {
          s.loggingOut = false;
          s.failed = false;
          void logout(s);
        } else if (
          Date.now() >=
          Math.min(
            s.lastActivity + settings.idleTimeout + settings.warningDuration,
            s.serverDeadline,
          )
        )
          void logout(s);
        else window.location.reload();
      },
      listener,
    );
    modal.addEventListener(
      "cancel",
      (event) => event.preventDefault(),
      listener,
    );
    const reconcile = () => {
      if (document.hidden) return;
      schedule(s);
      if (!s.loggingOut) void verify(s);
    };
    document.addEventListener("visibilitychange", reconcile, listener);
    window.addEventListener("pageshow", reconcile, listener);
    void verify(s);
    return this;
  },
  destroy() {
    if (!state) return;
    const s = state;
    state = undefined;
    s.abort.abort();
    clearTimeout(s.timer);
    clearTimeout(s.exitTimer);
    s.modal.close();
    s.modal.remove();
  },
};
export default AutoLogout;
