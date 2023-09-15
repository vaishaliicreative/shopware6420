<?php

declare(strict_types=1);

namespace ICTECHRestockReminder\Core\ProductStock;

use Dompdf\Dompdf;
use Dompdf\Options;

class ProductStockDocumentService
{
    /**
     * Generate PDF
     *
     * @param $html
     */
    public function generateDocument($html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->setIsHtml5ParserEnabled(true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->set_option('isPhpEnabled', true);
        $dompdf->loadHtml($html);

        $gcEnabledAtStart = gc_enabled();
        if ($gcEnabledAtStart) {
            gc_collect_cycles();
            gc_disable();
        }

        $dompdf->render();

        if ($gcEnabledAtStart) {
            gc_enable();
        }

        return $dompdf->output();
    }
}
