export function nameToInitials(name) {
  if (!name) return "";

  const parts = name.trim().split(/\s+/).filter(Boolean);

  if (parts.length === 1) return parts[0][0].toUpperCase();

  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

export function formatStatus(is_active) {
  if (is_active === 1) {
    return { text: "Active", color: "bg-green-600" };
  } else {
    return { text: "Inactive", color: "bg-red-600" };
  }
}

export function programToInitials(program) {
  if (!program) return "";

  const map = {
    "Bachelor of Science in Information Technology": "BSIT",
    "Bachelor of Science in Computer Science": "BSCS",
    "Bachelor of Science in Secondary Education Major in Math": "BSED-Math",
    "Bachelor of Science in Business Administration": "BSBA",
    "Bachelor of Science in Accountancy": "BSA",
    "Bachelor of Science in Office Administration": "BSOA",
  };

  const trimmed = program.trim();

  // exact match
  if (map[trimmed]) return map[trimmed];

  // If already short (≤10 chars, no spaces), return as-is
  if (trimmed.length <= 10 && !/\s/.test(trimmed)) return trimmed.toUpperCase();

  // fallback: first letters only skipping common words
  const skipWords = new Set(["in", "of", "the", "and", "a", "an", "major"]);
  return trimmed
    .split(/[\s\-]+/)
    .filter((word) => !skipWords.has(word.toLowerCase()) && word.length > 0)
    .map((word) => word[0].toUpperCase())
    .join("");
}

export function getRatingBadge(rating) {
  const map = {
    Outstanding: {
      bg: "#EAF3DE",
      border: "#97C459",
      dot: "#639922",
      text: "#27500A",
    },
    "Very Satisfactory": {
      bg: "#E1F5EE",
      border: "#5DCAA5",
      dot: "#1D9E75",
      text: "#085041",
    },
    Satisfactory: {
      bg: "#E6F1FB",
      border: "#85B7EB",
      dot: "#378ADD",
      text: "#0C447C",
    },
    Fair: {
      bg: "#FAEEDA",
      border: "#EF9F27",
      dot: "#BA7517",
      text: "#633806",
    },
    Poor: {
      bg: "#FCEBEB",
      border: "#F09595",
      dot: "#E24B4A",
      text: "#791F1F",
    },
  };

  const s = map[rating] ?? {
    bg: "#F1EFE8",
    border: "#B4B2A9",
    dot: "#888780",
    text: "#444441",
  };

  return `
    <div class="flex justify-center">
      <span style="
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        background: ${s.bg};
        border: 0.5px solid ${s.border};
        font-size: 11px;
        font-weight: 500;
        color: ${s.text};
        white-space: nowrap;
      ">
        <span style="
          width: 5px; height: 5px;
          border-radius: 50%;
          background: ${s.dot};
          flex-shrink: 0;
        "></span>
        ${rating}
      </span>
    </div>
  `;
}
