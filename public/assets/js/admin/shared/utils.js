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
