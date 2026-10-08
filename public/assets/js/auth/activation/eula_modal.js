export function initTermsModal() {
  const overlay = document.getElementById("seTermsOverlay");
  const card = document.getElementById("seTermsCard");
  const title = document.getElementById("seTermsTitle");
  const body = document.getElementById("seTermsBody");
  const closeBtn = document.getElementById("seTermsClose");
  const declineBtn = document.getElementById("seTermsDecline");
  const agreeBtn = document.getElementById("seTermsAgree");

  const tabTerms = document.getElementById("seTabTerms");
  const tabPrivacy = document.getElementById("seTabPrivacy");
  const contentTerms = document.getElementById("seContentTerms");
  const contentPrivacy = document.getElementById("seContentPrivacy");

  const eula = document.getElementById("eula");

  const activeTab = "bg-[#5e17eb] text-white";
  const inactiveTab = "bg-gray-100 text-gray-500";

  function showTab(tab) {
    body.scrollTop = 0;

    if (tab === "privacy") {
      title.textContent = "Privacy Policy";

      contentTerms.classList.add("hidden");
      contentTerms.classList.remove("flex");

      contentPrivacy.classList.remove("hidden");
      contentPrivacy.classList.add("flex");

      tabPrivacy.className =
        "se-tab text-xs font-semibold px-3.5 py-2 rounded-full transition-colors " +
        activeTab;

      tabTerms.className =
        "se-tab text-xs font-semibold px-3.5 py-2 rounded-full transition-colors " +
        inactiveTab;
    } else {
      title.textContent = "Terms of Service";

      contentPrivacy.classList.add("hidden");
      contentPrivacy.classList.remove("flex");

      contentTerms.classList.remove("hidden");
      contentTerms.classList.add("flex");

      tabTerms.className =
        "se-tab text-xs font-semibold px-3.5 py-2 rounded-full transition-colors " +
        activeTab;

      tabPrivacy.className =
        "se-tab text-xs font-semibold px-3.5 py-2 rounded-full transition-colors " +
        inactiveTab;
    }
  }

  function onEscape(e) {
    if (e.key === "Escape") close();
  }

  function open(tab = "terms") {
    showTab(tab);

    overlay.classList.remove("hidden");
    overlay.classList.add("flex");

    requestAnimationFrame(() => {
      card.classList.remove("scale-95", "opacity-0");
      card.classList.add("scale-100", "opacity-100");
    });

    document.addEventListener("keydown", onEscape);
  }

  function close() {
    card.classList.remove("scale-100", "opacity-100");
    card.classList.add("scale-95", "opacity-0");

    setTimeout(() => {
      overlay.classList.add("hidden");
      overlay.classList.remove("flex");
    }, 150);

    document.removeEventListener("keydown", onEscape);
  }

  tabTerms.addEventListener("click", () => showTab("terms"));
  tabPrivacy.addEventListener("click", () => showTab("privacy"));

  closeBtn.addEventListener("click", close);

  declineBtn.addEventListener("click", () => {
    if (eula) {
      eula.checked = false;
    }

    close();
  });

  agreeBtn.addEventListener("click", () => {
    if (eula) {
      eula.checked = true;
    }

    close();
  });

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      close();
    }
  });

  return {
    open,
    close,
  };
}
