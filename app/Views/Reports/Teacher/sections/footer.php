<?php

return static function ($canvas, string $generatedAt): void {
    $canvas->page_script(
        static function (
            int $pageNumber,
            int $pageCount,
            $canvas,
            $fontMetrics
        ) use ($generatedAt): void {
            if ($pageNumber !== $pageCount) {
                return;
            }

            $font = $fontMetrics->getFont('Arial', 'normal');
            $boldFont = $fontMetrics->getFont('Arial', 'bold');
            $pageWidth = $canvas->get_width();
            $pageHeight = $canvas->get_height();
            $sideInset = 27.0;
            $ruleY = $pageHeight - 42.0;
            $textY = $pageHeight - 28.0;
            $rightText = 'Asian Institute of Technology and Education  ·  CONFIDENTIAL';

            $canvas->line(
                $sideInset,
                $ruleY,
                $pageWidth - $sideInset,
                $ruleY,
                [0.89, 0.88, 0.92],
                0.7
            );

            $canvas->text(
                $sideInset,
                $textY,
                'Smart-Eval  ·  Generated ' . $generatedAt,
                $font,
                7,
                [0.24, 0.20, 0.54]
            );

            $rightWidth = $fontMetrics->getTextWidth(
                $rightText,
                $font,
                7
            );

            $canvas->text(
                $pageWidth - $sideInset - $rightWidth,
                $textY,
                $rightText,
                $boldFont,
                7,
                [0.33, 0.29, 0.59]
            );
        }
    );
};
