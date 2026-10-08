export function getAdjectiveRating(score) {
  if (score >= 4.5) return "Outstanding";
  if (score >= 3.5) return "Very Satisfactory";
  if (score >= 2.5) return "Satisfactory";
  if (score >= 1.5) return "Fair";
  return "Poor";
}

export function calculateGrowthRate(current, previous) {
  current = Number(current);
  previous = Number(previous);

  if (!previous || previous === 0) return 0;
  return ((current - previous) / previous) * 100;
}

export function formatYearLevel(raw) {
  const num = parseInt(raw, 10);

  if (num === 11 || num === 12) return `Grade ${num}`;

  const suffixes = ["", "1st", "2nd", "3rd", "4th"];
  if (num >= 1 && num <= 4) return `${suffixes[num]} Year`;

  return String(raw);
}
