export const COLORS = {
  darkBg: "#1E0F4E",
  purple: "#4D1D95",
  purpleLight: "#7C3AED",
  purpleSoft: "#F5F3FF",
  purpleBorder: "#DDD4F5",
  white: "#FFFFFF",
  gray: "#6B6B6B",
  grayLight: "#9CA3AF",
  lineGray: "#E5E5EA",
  green: "#16A34A",
  greenSoft: "#ECFDF5",
  textDark: "#111111",
  rowAlt: "#F8F6FF",
};

export const FONT_STACK = "Segoe UI, Arial, sans-serif";

export function font(size, weight = "normal", family = FONT_STACK) {
  return `${weight} ${size}px ${family}`;
}

export function roundRect(ctx, x, y, w, h, r) {
  const radius = typeof r === "number" ? { tl: r, tr: r, br: r, bl: r } : r;
  ctx.beginPath();
  ctx.moveTo(x + radius.tl, y);
  ctx.lineTo(x + w - radius.tr, y);
  ctx.arcTo(x + w, y, x + w, y + radius.tr, radius.tr);
  ctx.lineTo(x + w, y + h - radius.br);
  ctx.arcTo(x + w, y + h, x + w - radius.br, y + h, radius.br);
  ctx.lineTo(x + radius.bl, y + h);
  ctx.arcTo(x, y + h, x, y + h - radius.bl, radius.bl);
  ctx.lineTo(x, y + radius.tl);
  ctx.arcTo(x, y, x + radius.tl, y, radius.tl);
  ctx.closePath();
}

export function drawCenteredText(ctx, text, x, y, opts = {}) {
  ctx.font = font(opts.size ?? 14, opts.weight ?? "normal");
  ctx.fillStyle = opts.color ?? COLORS.textDark;
  ctx.textAlign = opts.align ?? "center";
  ctx.textBaseline = "alphabetic";
  ctx.fillText(text, x, y);
}

export function wrapText(ctx, text, x, y, maxWidth, lineHeight, opts = {}) {
  ctx.font = font(opts.size ?? 12, opts.weight ?? "normal");
  ctx.fillStyle = opts.color ?? COLORS.gray;
  ctx.textAlign = opts.align ?? "center";

  const words = text.split(" ");
  let line = "";
  let curY = y;

  for (const word of words) {
    const testLine = line ? `${line} ${word}` : word;
    if (ctx.measureText(testLine).width > maxWidth && line) {
      ctx.fillText(line, x, curY);
      line = word;
      curY += lineHeight;
    } else {
      line = testLine;
    }
  }
  ctx.fillText(line, x, curY);
  return curY;
}

export function drawCheckBadge(ctx, cx, cy, radius, color) {
  ctx.beginPath();
  ctx.arc(cx, cy, radius, 0, Math.PI * 2);
  ctx.fillStyle = color;
  ctx.fill();

  ctx.strokeStyle = COLORS.white;
  ctx.lineWidth = radius * 0.16;
  ctx.lineCap = "round";
  ctx.lineJoin = "round";
  ctx.beginPath();
  ctx.moveTo(cx - radius * 0.45, cy);
  ctx.lineTo(cx - radius * 0.1, cy + radius * 0.35);
  ctx.lineTo(cx + radius * 0.5, cy - radius * 0.35);
  ctx.stroke();
}
