import {
  COLORS,
  roundRect,
  drawCenteredText,
  wrapText,
  drawCheckBadge,
  font,
} from "./png_theme.js";
import { LAYOUT } from "./png_layout.js";

const { WIDTH: W, MARGIN: mar } = LAYOUT;
const contentW = W - mar * 2;

export function drawBackground(ctx, height) {
  ctx.fillStyle = COLORS.white;
  ctx.fillRect(0, 0, W, height);
}

export function drawHeader(ctx, periodName) {
  const h = LAYOUT.HEADER_HEIGHT;
  const badgeMarginBottom = 15;

  const gradient = ctx.createLinearGradient(0, 0, W, h);
  gradient.addColorStop(0, COLORS.darkBg);
  gradient.addColorStop(1, COLORS.purple);
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, W, h);

  // accent line
  ctx.fillStyle = COLORS.purpleLight;
  ctx.fillRect(0, h - 3, W, 3);

  // success badge
  drawCheckBadge(ctx, W / 2, 40, 25, COLORS.green);

  drawCenteredText(
    ctx,
    "EVALUATION DONE",
    W / 2,
    100 + badgeMarginBottom - 10,
    {
      size: 24,
      weight: "700",
      color: COLORS.white,
    },
  );

  wrapText(
    ctx,
    "Thank you for completing your teacher evaluations. Your responses have been successfully recorded.",
    W / 2,
    130,
    contentW - 150,
    20,
    { size: 13, color: "#DCD3FF" },
  );

  // period pill
  if (periodName) {
    ctx.font = font(11, "600");
    const textW = ctx.measureText(periodName.toUpperCase()).width;
    const pillW = textW + 32;
    const pillY = 182;

    ctx.strokeStyle = "#B4A0FF";
    ctx.lineWidth = 1;
    roundRect(ctx, W / 2 - pillW / 2, pillY - 10, pillW, 26, 13);
    ctx.stroke();

    drawCenteredText(ctx, periodName.toUpperCase(), W / 2, pillY + 6, {
      size: 11,
      weight: "600",
      color: "#DCD3FF",
    });
  }

  return h;
}

export function drawStatRow(ctx, y, { dateStr, total }) {
  const h = LAYOUT.STAT_ROW_HEIGHT - 20;
  const gap = 20;
  const cardW = (contentW - gap) / 2;

  drawStatCard(ctx, mar, y, cardW, h, {
    label: "DATE COMPLETED",
    value: dateStr,
    valueSize: 15,
  });

  drawStatCard(ctx, mar + cardW + gap, y, cardW, h, {
    label: `TEACHER${total !== 1 ? "S" : ""} EVALUATED`,
    value: String(total),
    valueSize: 24,
    accent: true,
  });

  return y + h;
}

function drawStatCard(ctx, x, y, w, h, { label, value, valueSize, accent }) {
  ctx.fillStyle = COLORS.purpleSoft;
  roundRect(ctx, x, y, w, h, 12);
  ctx.fill();
  ctx.strokeStyle = COLORS.purpleBorder;
  ctx.lineWidth = 1;
  roundRect(ctx, x, y, w, h, 12);
  ctx.stroke();

  const labelY = y + 30;
  const labelMarginBottom = 40;

  drawCenteredText(ctx, label, x + w / 2, labelY, {
    size: 10.5,
    weight: "600",
    color: COLORS.gray,
  });

  const valueY = labelY + labelMarginBottom;

  drawCenteredText(ctx, value, x + w / 2, valueY, {
    size: valueSize,
    weight: "600",
    color: accent ? COLORS.purple : COLORS.textDark,
  });
}

export function drawTeacherListTitle(ctx, y) {
  ctx.font = font(12, "700");
  ctx.fillStyle = COLORS.gray;
  ctx.textAlign = "left";
  ctx.fillText("EVALUATED FACULTY", mar, y + 14);

  ctx.strokeStyle = COLORS.lineGray;
  ctx.lineWidth = 1;
  ctx.beginPath();
  ctx.moveTo(mar, y + 24);
  ctx.lineTo(W - mar, y + 24);
  ctx.stroke();

  return y + LAYOUT.LIST_TITLE_HEIGHT;
}

export function drawTeacherRows(ctx, y, teachers) {
  const rowH = LAYOUT.ROW_HEIGHT;
  const marginTop = 20;

  const startY = y - marginTop;

  teachers.forEach((teacher, i) => {
    const rowY = startY + i * rowH;

    // avatar
    const cx = mar + 26;
    const cy = rowY + rowH / 2;
    ctx.beginPath();
    ctx.arc(cx, cy, 20, 0, Math.PI * 2);
    ctx.fillStyle = COLORS.purple;
    ctx.fill();
    drawCenteredText(
      ctx,
      teacher.full_name.charAt(0).toUpperCase(),
      cx,
      cy + 6,
      {
        size: 15,
        weight: "700",
        color: COLORS.white,
      },
    );

    // name + department
    ctx.textAlign = "left";
    ctx.font = font(14, "600");
    ctx.fillStyle = COLORS.textDark;
    ctx.fillText(teacher.full_name, cx + 34, cy - 2);

    ctx.font = font(12, "normal");
    ctx.fillStyle = COLORS.gray;
    ctx.fillText(teacher.department, cx + 34, cy + 16);

    // check badge
    drawCheckBadge(ctx, W - mar - 20, cy, 13, COLORS.green);

    // divider
    ctx.strokeStyle = COLORS.lineGray;
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(mar, rowY + rowH);
    ctx.lineTo(W - mar, rowY + rowH);
    ctx.stroke();
  });

  return y + rowH * teachers.length;
}

export function drawFooter(ctx, y) {
  const h = LAYOUT.FOOTER_HEIGHT;

  // status badge
  ctx.font = font(11, "700");
  const badgeText = "STATUS: COMPLETED";
  const badgeW = ctx.measureText(badgeText).width + 28;
  const badgeY = y + 16;

  ctx.fillStyle = COLORS.greenSoft;
  roundRect(ctx, W / 2 - badgeW / 2, badgeY, badgeW, 26, 13);
  ctx.fill();

  drawCenteredText(ctx, badgeText, W / 2, badgeY + 17, {
    size: 11,
    weight: "700",
    color: COLORS.green,
  });

  ctx.strokeStyle = COLORS.lineGray;
  ctx.beginPath();
  ctx.moveTo(mar, y + 58);
  ctx.lineTo(W - mar, y + 58);
  ctx.stroke();

  drawCenteredText(
    ctx,
    "This document serves as an official record of evaluation submission.",
    W / 2,
    y + 82,
    { size: 11, color: COLORS.grayLight },
  );
  drawCenteredText(ctx, "Generated by SMART-EVAL", W / 2, y + 102, {
    size: 10.5,
    weight: "600",
    color: COLORS.grayLight,
  });

  return y + h;
}
