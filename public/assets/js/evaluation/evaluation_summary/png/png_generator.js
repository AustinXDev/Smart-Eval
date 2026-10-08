import { COLORS } from "./png_theme.js";
import { LAYOUT, computeCanvasHeight } from "./png_layout.js";
import {
  drawBackground,
  drawHeader,
  drawStatRow,
  drawTeacherListTitle,
  drawTeacherRows,
  drawFooter,
} from "./png_section.js";

function formatDate(date = new Date()) {
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

function slugify(text) {
  return (text || "record").replace(/\s+/g, "-");
}

export function generateEvaluationPNG(data) {
  const height = computeCanvasHeight(data.evaluated_teachers.length);
  const dpr = window.devicePixelRatio || 1;

  const canvas = document.createElement("canvas");
  canvas.width = LAYOUT.WIDTH * dpr;
  canvas.height = height * dpr;

  const ctx = canvas.getContext("2d");
  ctx.scale(dpr, dpr);

  drawBackground(ctx, height);

  let y = drawHeader(ctx, data.period_name);
  y += LAYOUT.SECTION_GAP;

  y = drawStatRow(ctx, y, {
    dateStr: formatDate(),
    total: data.total_evaluated,
  });
  y += LAYOUT.SECTION_GAP;

  y = drawTeacherListTitle(ctx, y);
  y = drawTeacherRows(ctx, y, data.evaluated_teachers);
  y += LAYOUT.SECTION_GAP;

  drawFooter(ctx, y);

  downloadCanvasAsPNG(
    canvas,
    `Evaluation_Done_${slugify(data.period_name)}.png`,
  );
}

function downloadCanvasAsPNG(canvas, filename) {
  canvas.toBlob((blob) => {
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    link.click();
    URL.revokeObjectURL(url);
  }, "image/png");
}
