<?php

declare(strict_types=1);

namespace ICTECHRestockReminder\Core\ProductStock;

use ICTECHRestockReminder\Struct\PdfFile;
use Shopware\Core\Checkout\Document\DocumentGenerator\Counter;
use Shopware\Core\Checkout\Document\Twig\DocumentTemplateRenderer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ProductStockPdfService
{
    private ProductStockDocumentService $productStockDocumentService;
    private DocumentTemplateRenderer $twig;
    private EntityRepositoryInterface $productRepository;
    private SystemConfigService $systemConfigService;

    public function __construct(
        ProductStockDocumentService $productStockDocumentService,
        DocumentTemplateRenderer $twig,
        EntityRepositoryInterface $productRepository,
        SystemConfigService $systemConfigService
    ) {
        $this->productStockDocumentService = $productStockDocumentService;
        $this->twig = $twig;
        $this->productRepository = $productRepository;
        $this->systemConfigService = $systemConfigService;
    }

    public function createPdfForProductStock(Context $context): PdfFile
    {
        $limit = $this->systemConfigService->get(
            'ICTECHRestockReminder.restock.stockLimit'
        );

        $criteria = new Criteria();
        $criteria->addAssociation('visibilities');
        $criteria->addAssociation('options.name');
        $criteria->addAssociation('translations');
        $criteria->addAssociation('options.group');

        $criteria->addFilter(
            new RangeFilter('stock', [RangeFilter::LTE => $limit])
        );
        $products = $this->productRepository->search(
            $criteria,
            $context
        )->getElements();
        $result = [];

        foreach ($products as $product) {
            if ($product->parentId) {
                $variationName = '';
                foreach ($product->variation as $variation) {
                    $variationName .= $variation['group'].':'.$variation['option'].' | ';
                }
                $variantsCriteria = new Criteria();
                $variantsCriteria->addFilter(
                    new EqualsFilter('id', $product->parentId)
                );
                $variants = $this->productRepository->search($variantsCriteria, $context)->getElements();
                foreach ($variants as $productVeriant) {
                    $result[] = [
                        'id' => $product->id,
                        'name' => $productVeriant->name.' ('.$variationName.')',
                        'number' => $product->productNumber,
                        'stock' => $product->stock,
                        'link' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['BASE'] . '/admin#/sw/product/detail/' . $product->id . '/base',
                    ];
                }
            } else {
                $result[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'number' => $product->productNumber,
                    'stock' => $product->stock,
                    'link' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['BASE'] . '/admin#/sw/product/detail/' . $product->id . '/base',
                ];
            }
        }

        $view = '@ICTECHRestockReminder/storefront/page/productstock/product.html.twig';
        $parameters = [
            'product' => $result,
            'counter' => new Counter(),
        ];

        $html = $this->twig->render($view, $parameters, $context, null, $context->getLanguageId());

        $pdfAsBlob = $this->productStockDocumentService->generateDocument($html);

        $filename = 'Products-' . date('d-m-Y') . '.pdf';

        return new PdfFile($filename, $pdfAsBlob);
    }

}
