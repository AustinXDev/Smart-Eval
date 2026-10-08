class StatusModal {
  static currentConfirmAction = null;

  // ── inject fonts + styles once ──────────────────────
  static _injectStyles() {
    if (document.getElementById("sma-styles")) return;

    const font = document.createElement("link");
    font.rel = "stylesheet";
    font.href =
      "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=Inter:wght@400;500;600&display=swap";
    document.head.appendChild(font);

    const s = document.createElement("style");
    s.id = "sma-styles";
    s.textContent = `
      .sma-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(28, 25, 23, 0.45);
        backdrop-filter: blur(6px) saturate(120%);
        align-items: center;
        justify-content: center;
        z-index: 9998;
        opacity: 0;
        transition: opacity .3s ease;
      }

      .sma-backdrop.sma-visible { display: flex; opacity: 1; }

      .sma-box {
        position: relative;
        background: #FEFDFB;
        border-radius: 15px;
        max-width: 380px;
        width: 90%;
        padding: 3rem 2.25rem 2.25rem;
        text-align: center;
        margin: 0 1rem;
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        box-shadow:
          0 1px 1px rgba(28,25,23,0.02),
          0 8px 24px -6px rgba(28,25,23,0.12),
          0 24px 48px -12px rgba(28,25,23,0.18);
        transform: scale(0.92) translateY(14px);
        transition: transform .45s cubic-bezier(.19,1,.22,1);
      }

      .sma-backdrop.sma-visible .sma-box { transform: scale(1) translateY(0); }

      .sma-box::before {
        content: '';
        position: absolute; top: 0; left: 50%;
        width: 44px; height: 3px;
        background: currentColor;
        border-radius: 0 0 4px 4px;
        transform: translateX(-50%);
        opacity: .9;
      }

      .sma-box.success { color: #0F9D6C; }
      .sma-box.error   { color: #D64545; }
      .sma-box.verify  { color: #6010ff; }

      .sma-icon {
        width: 84px; height: 84px;
        margin: 0 auto 1.75rem;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        position: relative;
      }

      .sma-icon.success { background: #E9F7F1; color: #0F9D6C; }
      .sma-icon.error   { background: #FBEDEC; color: #D64545; }
      .sma-icon.verify  { background: #EEF1FF; color: #4C6FFF; }

      .sma-icon::before {
        content: '';
        position: absolute; inset: -1px;
        border-radius: 50%;
        border: 1px solid currentColor;
        opacity: 0;
      }
      .sma-backdrop.sma-visible .sma-icon::before {
        animation: sma-ring-breathe 1.1s ease-out .1s;
      }
      @keyframes sma-ring-breathe {
        0%   { transform: scale(0.85); opacity: .5; }
        100% { transform: scale(1.55); opacity: 0; }
      }

      .sma-icon svg { width: 34px; height: 34px; }
      .sma-icon path {
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-dasharray: 46;
        stroke-dashoffset: 46;
      }
      .sma-backdrop.sma-visible .sma-icon path {
        animation: sma-draw .5s cubic-bezier(.65,0,.35,1) forwards .3s;
      }
      @keyframes sma-draw { to { stroke-dashoffset: 0; } }

      .sma-backdrop.sma-visible .sma-icon.error {
        animation: sma-settle .5s cubic-bezier(.34,1.56,.64,1) .1s both;
      }
      @keyframes sma-settle {
        0%   { transform: scale(0.8) rotate(-4deg); }
        60%  { transform: scale(1.06) rotate(2deg); }
        100% { transform: scale(1) rotate(0deg); }
      }

      .sma-stagger { opacity: 0; transform: translateY(6px); }
      .sma-backdrop.sma-visible .sma-stagger {
        animation: sma-rise .45s ease forwards;
      }
      .sma-backdrop.sma-visible .sma-title    { animation-delay: .38s; }
      .sma-backdrop.sma-visible .sma-message  { animation-delay: .46s; }
      .sma-backdrop.sma-visible .sma-otp-row  { animation-delay: .50s; }
      .sma-backdrop.sma-visible .sma-resend   { animation-delay: .58s; }
      .sma-backdrop.sma-visible .sma-actions  { animation-delay: .54s; }
      @keyframes sma-rise { to { opacity: 1; transform: translateY(0); } }

      .sma-title {
        font-family: roboto, sans-serif, system-ui, sans-serif;
        font-size: clamp(1rem, 1.25rem, 1.5rem);
        line-height: 1.25;
        color: #1C1917;
        margin: 0 0 .5rem;
      }
      .sma-message {
        font-size: .80rem;
        color: #969696;
        line-height: 1.6;
        margin: 0 0 2rem;
      }

      .sma-actions { display: flex; flex-direction: column; gap: .5rem; margin-top: 0; }
      .sma-actions.row { flex-direction: row; justify-content: flex-end; gap: .75rem; }

      .sma-btn {
        width: 100%;
        padding: .75rem 1rem;
        border: none;
        border-radius: 999px;
        font-size: .875rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, color .15s ease;
      }
      .sma-btn:hover { transform: translateY(-1px); }
      .sma-btn:active { transform: translateY(0) scale(0.98); }
      .sma-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }

      .sma-btn.success { background: #0F9D6C; color: #fff; box-shadow: 0 8px 20px -6px rgba(15,157,108,.45); }
      .sma-btn.success:hover { background: #0B7A54; }
      .sma-btn.error   { background: #D64545; color: #fff; box-shadow: 0 8px 20px -6px rgba(214,69,69,.4); }
      .sma-btn.error:hover { background: #B23434; }
      .sma-btn.verify  { background: #6010ff; color: #fff; box-shadow: 0 8px 20px -6px rgba(76,111,255,.4); }
      .sma-btn.verify:hover { background: #3A56E8; }

      .sma-btn.confirm { background: #1C1917; color: #fff; width: auto; padding: .625rem 1.25rem; }
      .sma-btn.cancel  { background: transparent; color: #928C86; width: auto; padding: .625rem 1rem; box-shadow: none; }
      .sma-btn.cancel:hover { color: #1C1917; transform: none; }

       /* ── OTP input row ── */
       .sma-otp-row {
        display: flex;
        justify-content: center;
        gap: .5rem;
        margin-bottom: 1.5rem;
      }
      .sma-otp-digit {
        width: 42px;
        height: 52px;
        text-align: center;
        font-family: 'Inter', sans-serif;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1C1917;
        background: #FBFAF8;
        border: 1.5px solid #6010ff;
        border-radius: 10px;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        -moz-appearance: textfield;
      }
      .sma-otp-digit::-webkit-outer-spin-button,
      .sma-otp-digit::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
      .sma-otp-digit:focus {
        border-color: #6010ff;
        box-shadow: 0 0 0 3px rgba(76,111,255,.15);
      }
      .sma-otp-digit.filled { border-color: #C7CEFF; background: #F5F6FF; }
      .sma-otp-digit.error {
        border-color: #D64545;
        background: #FDF3F2;
      }
      .sma-otp-row.shake { animation: sma-shake .4s ease; }
      @keyframes sma-shake {
        10%, 90% { transform: translateX(-1px); }
        20%, 80% { transform: translateX(2px); }
        30%, 50%, 70% { transform: translateX(-4px); }
        40%, 60% { transform: translateX(4px); }
      }
 
      .sma-otp-error-msg {
        font-size: .75rem;
        color: #D64545;
        margin: -1rem 0 1rem;
        height: 0;
        opacity: 0;
        transition: opacity .2s ease;
      }
      .sma-otp-error-msg.show { opacity: 1; height: auto; }
 
      .sma-resend {
        font-size: .78rem;
        color: #969696;
        margin-top: 0.75rem;
      }
      .sma-resend button {
        background: none;
        border: none;
        padding: 0;
        font: inherit;
        font-weight: 600;
        color: #6010ff;
        cursor: pointer;
      }
      .sma-resend button:disabled {
        color: #C7CEDB;
        cursor: not-allowed;
      }
 
      .sma-spinner {
        display: inline-block;
        width: 14px; height: 14px;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: sma-spin .7s linear infinite;
        vertical-align: -2px;
        margin-right: .4rem;
      }
      @keyframes sma-spin { to { transform: rotate(360deg); } }
    `;
    document.head.appendChild(s);
  }

  // ── build modals once ───────────────────────────────
  static _injectModals() {
    if (document.getElementById("sma-success")) return;

    document.body.insertAdjacentHTML(
      "beforeend",
      `
      <!-- SUCCESS -->
      <div id="sma-success" class="sma-backdrop">
        <div class="sma-box success">
          <div class="sma-icon success">
            <svg viewBox="0 0 52 52">
              <path stroke="currentColor" stroke-width="3.5" d="M14 27 L22 35 L38 17" />
            </svg>
          </div>
          <h3 class="sma-title sma-stagger">Success</h3>
          <p class="sma-message sma-stagger">Operation completed.</p>
          <div class="sma-actions sma-stagger">
            <button class="sma-btn success" onclick="StatusModal.hide('success')">Continue</button>
          </div>
        </div>
      </div>

      <!-- ERROR -->
      <div id="sma-error" class="sma-backdrop">
        <div class="sma-box error">
          <div class="sma-icon error">
            <svg viewBox="0 0 52 52">
              <path stroke="currentColor" stroke-width="3.5" d="M16 16 L36 36" />
              <path stroke="currentColor" stroke-width="3.5" d="M36 16 L16 36" />
            </svg>
          </div>
          <h3 class="sma-title sma-stagger">Something went wrong</h3>
          <p class="sma-message sma-stagger">Please try again.</p>
          <div class="sma-actions sma-stagger">
            <button class="sma-btn error" onclick="StatusModal.hide('error')">Dismiss</button>
          </div>
        </div>
      </div>

      <!-- CONFIRM -->
      <div id="sma-confirm" class="sma-backdrop">
        <div class="sma-box">
          <h3 class="sma-title sma-stagger" style="margin-top:0">Confirm action</h3>
          <p class="sma-message sma-stagger">Are you sure you want to proceed?</p>
          <div class="sma-actions row sma-stagger">
            <button id="sma-confirm-cancel" class="sma-btn cancel">Cancel</button>
            <button id="sma-confirm-action" class="sma-btn confirm">Confirm</button>
          </div>
        </div>
      </div>
    
    <!-- VERIFY -->
    <div id="sma-verify" class="sma-backdrop">
      <div class="sma-box verify">
        <div class="sma-icon verify">
          <svg viewBox="0 0 52 52" fill="none">
            <path stroke="currentColor" stroke-width="3.2" d="M9 15 L26 27 L43 15" />
            <path stroke="currentColor" stroke-width="3.2" d="M9 15 h34 v22 h-34 z" />
          </svg>
        </div>
        <h3 class="sma-title sma-stagger">Verify your identity</h3>
        <p class="sma-message sma-stagger">Enter the code we sent you.</p>

        <div class="sma-otp-row sma-stagger" id="sma-otp-row"></div>
        <p class="sma-otp-error-msg" id="sma-otp-error-msg">Incorrect code. Try again.</p>

        <div class="sma-actions sma-stagger">
          <button class="sma-btn verify" id="sma-verify-submit">Verify</button>
        </div>
        <p class="sma-resend sma-stagger" id="sma-resend">
          Didn't get a code?
          <button id="sma-resend-btn" type="button">Resend</button>
        </p>
      </div>
    </div>
    `,
    );

    this._wireVerify();
  }

  // ── verify modal internals ──────────────────────────
  static _wireVerify() {
    const row = document.getElementById("sma-otp-row");

    row.addEventListener("input", (e) => {
      const el = e.target;
      if (!el.classList.contains("sma-otp-digit")) return;
      el.value = el.value.replace(/[^0-9]/g, "").slice(-1);
      el.classList.toggle("filled", !!el.value);
      this._clearOtpError();
      if (el.value && el.nextElementSibling) el.nextElementSibling.focus();
      this._maybeAutoSubmit();
    });

    row.addEventListener("keydown", (e) => {
      const el = e.target;
      if (!el.classList.contains("sma-otp-digit")) return;
      if (e.key === "Backspace" && !el.value && el.previousElementSibling) {
        el.previousElementSibling.focus();
      }
      if (e.key === "ArrowLeft" && el.previousElementSibling)
        el.previousElementSibling.focus();
      if (e.key === "ArrowRight" && el.nextElementSibling)
        el.nextElementSibling.focus();
    });

    row.addEventListener("paste", (e) => {
      e.preventDefault();
      const digits = (e.clipboardData.getData("text") || "")
        .replace(/[^0-9]/g, "")
        .split("");
      const boxes = [...row.querySelectorAll(".sma-otp-digit")];
      boxes.forEach((box, i) => {
        box.value = digits[i] || "";
        box.classList.toggle("filled", !!box.value);
      });
      this._clearOtpError();
      const next = boxes.find((b) => !b.value) || boxes[boxes.length - 1];
      next.focus();
      this._maybeAutoSubmit();
    });

    document
      .getElementById("sma-verify-submit")
      .addEventListener("click", () => this._submitOtp());
    document
      .getElementById("sma-resend-btn")
      .addEventListener("click", () => this._resend());
  }

  static _buildOtpBoxes(length) {
    const row = document.getElementById("sma-otp-row");
    row.innerHTML = "";
    for (let i = 0; i < length; i++) {
      const input = document.createElement("input");
      input.type = "tel";
      input.inputMode = "numeric";
      input.maxLength = 1;
      input.autocomplete = i === 0 ? "one-time-code" : "off";
      input.className = "sma-otp-digit";
      row.appendChild(input);
    }
  }

  static _getOtpValue() {
    return [...document.querySelectorAll(".sma-otp-digit")]
      .map((b) => b.value)
      .join("");
  }

  static _maybeAutoSubmit() {
    const boxes = document.querySelectorAll(".sma-otp-digit");
    const full = [...boxes].every((b) => b.value);
    if (full) this._submitOtp();
  }

  static _clearOtpError() {
    document
      .querySelectorAll(".sma-otp-digit")
      .forEach((b) => b.classList.remove("error"));
    document.getElementById("sma-otp-error-msg").classList.remove("show");
  }

  static _showOtpError(message) {
    const boxes = document.querySelectorAll(".sma-otp-digit");
    boxes.forEach((b) => b.classList.add("error"));
    const msg = document.getElementById("sma-otp-error-msg");
    msg.textContent = message || "Incorrect code. Try again.";
    msg.classList.add("show");
    const row = document.getElementById("sma-otp-row");
    row.classList.remove("shake");
    void row.offsetWidth;
    row.classList.add("shake");
    boxes.forEach((b) => (b.value = ""));
    boxes[0]?.focus();
  }

  static async _submitOtp() {
    const code = this._getOtpValue();
    if (code.length < document.querySelectorAll(".sma-otp-digit").length)
      return;

    const btn = document.getElementById("sma-verify-submit");
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = `<span class="sma-spinner"></span>Verifying…`;

    try {
      const result = this._verifyOptions.onSubmit
        ? await this._verifyOptions.onSubmit(code)
        : true;

      if (result === false) {
        this._showOtpError();
        btn.disabled = false;
        btn.textContent = originalText;
        return;
      }

      this.hide("verify-close");
      if (typeof this._verifyOptions.onSuccess === "function") {
        this._verifyOptions.onSuccess(code);
      }
    } catch (err) {
      this._showOtpError(err?.message);
    } finally {
      btn.disabled = false;
      btn.textContent = originalText;
    }
  }

  static async _resend() {
    const resendBtn = document.getElementById("sma-resend-btn");
    resendBtn.disabled = true;

    try {
      if (typeof this._verifyOptions.onResend === "function") {
        await this._verifyOptions.onResend();
      }
    } catch (err) {
      // swallow — still run the cooldown so the user can retry
    }

    this._clearOtpError();
    document.querySelectorAll(".sma-otp-digit").forEach((b) => {
      b.value = "";
      b.classList.remove("filled");
    });
    document.querySelector(".sma-otp-digit")?.focus();

    let seconds = 60;
    resendBtn.textContent = `Resend (${seconds}s)`;
    const timer = setInterval(() => {
      seconds -= 1;
      if (seconds <= 0) {
        clearInterval(timer);
        resendBtn.disabled = false;
        resendBtn.textContent = "Resend";
      } else {
        resendBtn.textContent = `Resend (${seconds}s)`;
      }
    }, 1000);
  }

  // ── public API ──────────────────────────────────────
  static show(title, message, type = "success", options = {}) {
    const { button = true, buttonText = "Continue", onClick = null } = options;

    const backdrop = document.getElementById(
      type === "success" ? "sma-success" : "sma-error",
    );

    backdrop.querySelector(".sma-title").textContent = title;

    backdrop.querySelector(".sma-message").textContent = message;

    const btn = backdrop.querySelector(".sma-btn");

    if (button) {
      btn.style.display = "";
      btn.textContent = buttonText;

      btn.onclick = () => {
        StatusModal.hide(type);

        if (typeof onClick === "function") {
          onClick();
        }
      };
    } else {
      btn.style.display = "none";
    }

    // restart animations on repeat calls
    backdrop.classList.remove("sma-visible");
    void backdrop.offsetWidth;
    backdrop.classList.add("sma-visible");
    document.body.style.overflow = "hidden";
  }

  static hide(type = "success") {
    const id =
      type === "success"
        ? "sma-success"
        : type === "error"
          ? "sma-error"
          : "sma-verify";
    document.getElementById(id).classList.remove("sma-visible");
    document.body.style.overflow = "";
  }

  static confirm(title, message, onConfirmCallback) {
    const backdrop = document.getElementById("sma-confirm");
    backdrop.querySelector(".sma-title").textContent = title;
    backdrop.querySelector(".sma-message").textContent = message;
    this.currentConfirmAction = onConfirmCallback;

    backdrop.classList.remove("sma-visible");
    void backdrop.offsetWidth;
    backdrop.classList.add("sma-visible");
    document.body.style.overflow = "hidden";
  }

  static hideConfirm() {
    document.getElementById("sma-confirm").classList.remove("sma-visible");
    document.body.style.overflow = "";
    this.currentConfirmAction = null;
  }

  static verify(title, message, options = {}) {
    const { length = 6 } = options;
    this._verifyOptions = options;

    const backdrop = document.getElementById("sma-verify");
    backdrop.querySelector(".sma-title").textContent = title;
    backdrop.querySelector(".sma-message").textContent = message;

    this._buildOtpBoxes(length);
    this._clearOtpError();

    const resendBtn = document.getElementById("sma-resend-btn");
    resendBtn.disabled = false;
    resendBtn.textContent = "Resend";

    backdrop.classList.remove("sma-visible");
    void backdrop.offsetWidth;
    backdrop.classList.add("sma-visible");
    document.body.style.overflow = "hidden";

    setTimeout(() => document.querySelector(".sma-otp-digit")?.focus(), 400);
  }
}

// ── boot ──────────────────────────────────────────────
StatusModal._injectStyles();
StatusModal._injectModals();

document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("sma-confirm-action")
    ?.addEventListener("click", () => {
      if (typeof StatusModal.currentConfirmAction === "function") {
        StatusModal.currentConfirmAction();
      }
      StatusModal.hideConfirm();
    });

  document
    .getElementById("sma-confirm-cancel")
    ?.addEventListener("click", () => {
      StatusModal.hideConfirm();
    });
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    StatusModal.hide("success");
    StatusModal.hide("error");
    StatusModal.hideConfirm();
  }
});
