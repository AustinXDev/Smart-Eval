export function initImagePreview(inputId, previewId, options = {}) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);

  if (!input || !preview) {
    return { reset: () => {} };
  }

  const {
    defaultSrc = "",
    maxSizeMB = null,
    fileNameId = null,
    placeholderId = null,
    checkId = null, // NEW
    emptyFileNameText = "No file selected",
    formId = null,
  } = options;

  const fileNameEl = fileNameId ? document.getElementById(fileNameId) : null;

  const placeholderEl = placeholderId
    ? document.getElementById(placeholderId)
    : null;

  const checkEl = checkId ? document.getElementById(checkId) : null;

  // -----------------------------
  // RESET
  // -----------------------------
  function reset() {
    checkEl?.classList.add("hidden");
    if (fileNameEl) fileNameEl.textContent = emptyFileNameText;

    if (defaultSrc) {
      preview.src = defaultSrc;
      preview.classList.remove("hidden");
      placeholderEl?.classList.add("hidden");
    } else {
      preview.removeAttribute("src");
      preview.classList.add("hidden");
      placeholderEl?.classList.remove("hidden");
    }
  }

  // -----------------------------
  // SHOW PREVIEW
  // -----------------------------
  function showPreview(src, name) {
    preview.src = src;

    preview.classList.remove("hidden");

    placeholderEl?.classList.add("hidden");

    // Show success check
    checkEl?.classList.remove("hidden");

    if (fileNameEl) {
      fileNameEl.textContent = name;
    }
  }

  // -----------------------------
  // FILE CHANGE
  // -----------------------------
  input.addEventListener("change", () => {
    const file = input.files?.[0];

    if (!file) {
      return reset();
    }

    // Validate image
    if (!file.type.startsWith("image/")) {
      alert("Please select an image file.");

      input.value = "";

      return reset();
    }

    // Validate size
    if (maxSizeMB && file.size > maxSizeMB * 1024 * 1024) {
      alert(`Image must be smaller than ${maxSizeMB}MB.`);

      input.value = "";

      return reset();
    }

    // Read image
    const reader = new FileReader();

    reader.onload = (e) => {
      showPreview(e.target.result, file.name);
    };

    reader.onerror = () => {
      alert("Couldn't read that image. Please try another file.");

      input.value = "";

      reset();
    };

    reader.readAsDataURL(file);
  });

  // -----------------------------
  // FORM RESET
  // -----------------------------
  if (formId) {
    document.getElementById(formId)?.addEventListener("reset", reset);
  }

  reset();

  // -----------------------------
  // RETURN API
  // -----------------------------
  return {
    reset,
    updateDefault(newDefaultSrc) {
      defaultSrc = newDefaultSrc || "";
      reset();
    },
  };
}
