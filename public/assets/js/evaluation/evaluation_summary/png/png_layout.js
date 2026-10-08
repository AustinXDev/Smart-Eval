export const LAYOUT = {
  WIDTH: 600,
  MARGIN: 48,
  HEADER_HEIGHT: 220,
  STAT_ROW_HEIGHT: 110,
  LIST_TITLE_HEIGHT: 46,
  ROW_HEIGHT: 74,
  FOOTER_HEIGHT: 130,
  SECTION_GAP: 28,
  BOTTOM_MARGIN: 40,
};

export function computeCanvasHeight(teacherCount) {
  const {
    HEADER_HEIGHT,
    STAT_ROW_HEIGHT,
    LIST_TITLE_HEIGHT,
    ROW_HEIGHT,
    FOOTER_HEIGHT,
    SECTION_GAP,
    BOTTOM_MARGIN,
  } = LAYOUT;

  return Math.round(
    HEADER_HEIGHT +
      SECTION_GAP +
      STAT_ROW_HEIGHT +
      SECTION_GAP +
      LIST_TITLE_HEIGHT +
      ROW_HEIGHT * Math.max(teacherCount, 1) +
      SECTION_GAP +
      FOOTER_HEIGHT +
      BOTTOM_MARGIN,
  );
}
