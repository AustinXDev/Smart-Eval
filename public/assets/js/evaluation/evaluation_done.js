document.addEventListener("DOMContentLoaded", async () => {
  try {
    const res = await fetch(
      "/Smart-Eval/app/Controllers/evaluation/evaluation_done.php",
    );
    const data = await res.json();

    if (!data.success) {
      showError(data.error);
      return;
    }

    // Hide loading
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";

    // Set period name
    document.getElementById("period-name").textContent = data.period_name;

    if (data.total_evaluated === 0) {
      // Show empty state
      document.getElementById("empty-state").style.display = "block";
      document.getElementById("teachers-list").style.display = "none";
    } else {
      // Show teachers list
      document.getElementById("empty-state").style.display = "none";
      document.getElementById("teachers-list").style.display = "block";

      // Update counts
      document.getElementById("total-count").textContent = data.total_evaluated;
      document.getElementById("plural-s").textContent =
        data.total_evaluated !== 1 ? "s" : "";

      // Render teachers
      const teachersList = document.querySelector("#teachers-list .space-y-2");
      teachersList.innerHTML = data.evaluated_teachers
        .map(
          (teacher, index) => `
                <div class="teacher-item flex items-center justify-between p-3 hover:bg-gray-50 rounded stagger-${Math.min(index + 1, 10)}">
                    <div class="flex items-center flex-1">
                        <div class="teacher-avatar" style="width: 35px; height: 35px; font-size: 1rem;">
                            ${teacher.full_name.charAt(0).toUpperCase()}
                        </div>
                        <div class="ml-3 flex-1">
                            <div class="font-medium text-gray-800 text-sm">${escapeHtml(teacher.full_name)}</div>
                            <div class="text-xs text-gray-500">${escapeHtml(teacher.department)}</div>
                        </div>
                    </div>
                    <div class="text-green-500 font-bold">✓</div>
                </div>
            `,
        )
        .join("");

      buildPDFContent(data);

      const btn = document.getElementById("download-pdf");
      if (btn) {
        btn.addEventListener("click", () => generatePDF(data)); // pass data here
      }

      // Apply stagger animation
      document.querySelectorAll(".teacher-item").forEach((el, i) => {
        el.style.animation = `slideUp 0.5s ease-out ${(i + 1) * 0.1}s forwards`;
        el.style.opacity = "0";
      });
    }
  } catch (err) {
    console.error("Error:", err);
    showError("Failed to load evaluation data. Please try again.");
  }
});

function showError(message) {
  document.getElementById("loading").style.display = "none";
  document.getElementById("content").style.display = "block";
  document.getElementById("error-state").style.display = "block";
  document.getElementById("error-message").textContent = message;
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function buildPDFContent(data) {
  document.getElementById("pdf-period").textContent = data.period_name;

  document.getElementById("pdf-total").textContent = data.total_evaluated;

  document.getElementById("pdf-teacher-list").innerHTML =
    data.evaluated_teachers
      .map(
        (t) => `
        <div style="
          padding:6px 0;
          border-bottom:1px solid #ddd;
          font-size:12px;">
          ${t.full_name} - ${t.department}
        </div>
      `,
      )
      .join("");
}

async function generatePDF(data) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ unit: "mm", format: "a4", orientation: "portrait" });

  const W = 210,
    mar = 20,
    cW = W - mar * 2;
  let y = 0;

  // ── Color tokens
  const PURPLE = [77, 29, 149];
  const DARK = [30, 15, 78];
  const WHITE = [255, 255, 255];
  const GRAY = [107, 107, 107];
  const LGRAY = [220, 220, 220];
  const GREEN = [22, 163, 74];
  const BGROW = [248, 248, 255];

  // ── Helpers
  const fill = (...c) => doc.setFillColor(...c);
  const drw = (r, g, b, lw) => {
    doc.setDrawColor(r, g, b);
    doc.setLineWidth(lw ?? 0.3);
  };
  const Rect = (x, _y, w, h, m = "F") => doc.rect(x, _y, w, h, m);
  const Line = (x1, _y, x2, y2) => doc.line(x1, _y, x2, y2);
  const Txt = (str, x, _y, o = {}) => {
    doc.setFont(o.font ?? "helvetica", o.style ?? "normal");
    doc.setFontSize(o.size ?? 9);
    doc.setTextColor(...(o.color ?? [17, 17, 17]));
    doc.text(str, x, _y, { align: o.align ?? "left", maxWidth: o.maxW });
  };

  // ══════════════════════════════
  // HEADER BAND
  // ══════════════════════════════
  fill(...DARK);
  Rect(0, 0, W, 50);

  // subtle bottom accent line
  fill(...PURPLE);
  Rect(0, 48, W, 2);

  Txt("SMART-EVAL", W / 2, 14, {
    style: "bold",
    size: 7.5,
    color: [180, 160, 255],
    align: "center",
  });
  Txt("Evaluation Receipt", W / 2, 28, {
    font: "times",
    size: 22,
    color: WHITE,
    align: "center",
  });
  Txt("OFFICIAL SUBMISSION RECORD", W / 2, 37, {
    size: 6.5,
    color: [200, 180, 255],
    align: "center",
  });

  // period pill
  const pText = document.getElementById("pdf-period").textContent;
  const pW = Math.max(55, doc.getStringUnitWidth(pText) * 6.5 + 12);
  drw(...[180, 160, 255], 0.4);
  Rect(W / 2 - pW / 2, 40, pW, 7, "S");
  Txt(pText, W / 2, 45.5, {
    size: 6.5,
    color: [220, 210, 255],
    align: "center",
  });

  y = 62;

  // ══════════════════════════════
  // DATE / TIME ROW
  // ══════════════════════════════
  const now = new Date();
  const dateStr = now.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
  const timeStr = now.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
  });

  Txt("Date Issued:", mar, y, { size: 8, color: GRAY });
  Txt(dateStr, mar + 25, y, { size: 8 });
  Txt("Time:", W - mar - 36, y, { size: 8, color: GRAY });
  Txt(timeStr, W - mar - 23, y, { size: 8 });
  y += 5;
  drw(...LGRAY, 0.3);
  Line(mar, y, W - mar, y);
  y += 10;

  // ══════════════════════════════
  // STAT BLOCK
  // ══════════════════════════════
  fill(245, 243, 255);
  Rect(mar, y, cW, 24);
  drw(...[200, 190, 240], 0.3);
  Rect(mar, y, cW, 24, "S");

  const cnt = parseInt(document.getElementById("pdf-total").textContent);
  Txt(String(cnt), W / 2, y + 15, {
    font: "times",
    style: "bold",
    size: 26,
    color: PURPLE,
    align: "center",
  });
  Txt(`Teacher${cnt !== 1 ? "s" : ""} Evaluated`, W / 2, y + 21, {
    size: 6.5,
    color: GRAY,
    align: "center",
  });
  y += 32;

  // ══════════════════════════════
  // FACULTY TABLE
  // ══════════════════════════════
  Txt("EVALUATED FACULTY", mar, y, { style: "bold", size: 7, color: GRAY });
  y += 3;
  drw(...LGRAY, 0.4);
  Line(mar, y, W - mar, y);
  y += 8;

  const teachers = document.querySelectorAll("#pdf-teacher-list div");
  teachers.forEach((el, i) => {
    if (y > 262) {
      doc.addPage();
      y = 20;
    }

    // alternating row background
    fill(...(i % 2 === 0 ? [255, 255, 255] : [248, 246, 255]));
    Rect(mar, y - 4, cW, 12);

    // avatar circle
    fill(...PURPLE);
    doc.circle(mar + 5, y + 2, 4, "F");
    const parts = el.textContent.trim().split(" - ");
    const name = parts[0] ?? "";
    const dept = parts[1] ?? "";
    Txt(name.charAt(0).toUpperCase(), mar + 5, y + 4, {
      style: "bold",
      size: 7.5,
      color: WHITE,
      align: "center",
    });

    // name + dept
    Txt(name, mar + 13, y + 1, { style: "bold", size: 9 });
    Txt(dept, mar + 13, y + 5.5, { size: 7.5, color: GRAY });

    // green check
    fill(...GREEN);
    doc.circle(W - mar - 4.5, y + 2, 3.5, "F");
    Txt("✓", W - mar - 4.5, y + 4, {
      style: "bold",
      size: 7,
      color: WHITE,
      align: "center",
    });

    y += 12;
    drw(...LGRAY, 0.2);
    Line(mar, y, W - mar, y);
    y += 2;
  });

  y += 8;

  // ══════════════════════════════
  // CERTIFICATION BOX
  // ══════════════════════════════
  if (y > 242) {
    doc.addPage();
    y = 20;
  }
  fill(245, 243, 255);
  Rect(mar, y, cW, 26);
  drw(...[200, 190, 240], 0.3);
  Rect(mar, y, cW, 26, "S");

  Txt("CERTIFICATION", W / 2, y + 8, {
    style: "bold",
    size: 7,
    color: GRAY,
    align: "center",
  });
  doc.setFont("helvetica", "normal");
  doc.setFontSize(7.5);
  doc.setTextColor(...GRAY);
  doc.text(
    doc.splitTextToSize(
      "This document certifies that the above-named student has duly submitted teacher evaluation forms for the indicated academic period. This record is computer-generated and does not require a physical signature.",
      cW - 14,
    ),
    W / 2,
    y + 14,
    { align: "center" },
  );

  // ══════════════════════════════
  // FOOTER (all pages)
  // ══════════════════════════════
  const pages = doc.getNumberOfPages();
  for (let p = 1; p <= pages; p++) {
    doc.setPage(p);
    fill(...DARK);
    Rect(0, 285, W, 12);
    fill(...PURPLE);
    Rect(0, 285, W, 1.5);
    Txt(
      "This document serves as an official record of evaluation submission  •  Do not alter",
      W / 2,
      292,
      { size: 6.5, color: [200, 180, 255], align: "center" },
    );
    Txt(`Page ${p} of ${pages}`, W - mar, 292, {
      size: 6.5,
      color: [200, 180, 255],
      align: "right",
    });
  }

  const periodSlug = (pText || "record").replace(/\s+/g, "-");
  doc.save(`Evaluation_Receipt_${periodSlug}.pdf`);
}
