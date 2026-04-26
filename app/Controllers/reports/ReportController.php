<?php
require_once __DIR__ . '/services/ReportService.php';
require_once __DIR__ . '/utils/PdfRenderer.php';
require_once __DIR__ . '/validators/ReportValidator.php';

class ReportController
{
    public function generate($type, $params)
    {
        ReportValidator::validate($type, $params);

        $service = new ReportService();
        
        $result = $service->handle($type, $params);

        $renderer = new PdfRenderer();
        $renderer->render($result['view'], $result['data']);
    }

}

?>