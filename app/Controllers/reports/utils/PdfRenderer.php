<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/path.php';

use Dompdf\Dompdf;

class PdfRenderer
{
    public function render($view, $data)
    {
        extract($data);

        ob_start();
        require VIEW_PATH . "reports/$view";
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("report.pdf", ["Attachment" => false]);
    }

    public function getPdfBinary($view, $data) {
        extract($data);
        ob_start();
        require VIEW_PATH . "reports/$view";
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }

}