<?php

declare(strict_types=1);

namespace ICTECHMigration\Controller;

use mysqli;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class MainProductController extends AbstractController
{
    private SystemConfigService $systemConfigService;

    private EntityRepository $languageRepository;

    private EntityRepository $productsRepository;

    private EntityRepository $taxRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository $languageRepository,
        EntityRepository $productsRepository,
        EntityRepository $taxRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->productsRepository = $productsRepository;
        $this->taxRepository = $taxRepository;
    }

    /**
     * @Route("/api/_action/migration/mainproduct",name="api.custom.migration.mainproduct", methods={"POST"})
     */
    public function property(Request $request): Response
    {
        $context = Context::createDefaultContext();
        $servername = $this->systemConfigService->get('ICTECHMigration.config.databaseHost');
        $username = $this->systemConfigService->get('ICTECHMigration.config.databaseUser');
        $password = $this->systemConfigService->get('ICTECHMigration.config.databasePassword');
        $database = 'usrdb_amanwyeh5';

        $conn = new mysqli($servername, $username, $password, $database);

        $currencyId = $context->getCurrencyId();


        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('position',1));
        $taxDetails = $this->taxRepository->search($criteria,$context)->first();


//        $productSql = "SELECT * FROM product_data";
        $productSql = "SELECT * FROM product INNER JOIN product_data ON product.p_id = product_data.product_id";
        $productDetails = mysqli_query($conn, $productSql);
        $price = [ [
            "net" => 100,
            "gross" => 100,
            "linked" => true,
            "currencyId" => $currencyId,


        ]];
        $product = [
            [
                'id' => Uuid::randomHex(),
                'name' => [
                    'en-GB' => 'Test',
                    'de-DE' => 'Test de-DE',
                ],
                'description' => [
                    'en-GB' => 'test description',
                    'de-DE' => 'test description de-DE',
                ],
                'metaTitle' => [
                    'en-GB' => 'Title',
                    'de-DE' => 'Title de-DE',
                ],
                'metaDescription' => [
                    'en-GB' => 'meta_description',
                    'de-DE' => 'meta_description de-DE',
                ],
                'taxId' => $taxDetails->getId(),
                'productNumber' => bin2hex(random_bytes(16)),
                'price' => $price,
                'stock' => 1,
            ],
        ];
        $this->productsRepository->create($product, $context);

//        dd($products);
        echo '<pre>';
        if (mysqli_num_rows($productDetails) > 0) {
            while($row = mysqli_fetch_assoc($productDetails)) {
               print_r($row);
               $product = [];
               $product['name'] = $row['title'];
               $product['description'] = $row['description'];
               $product['metaTitle'] = $row['seo_title'];
               $product['metaDescription'] = $row['seo_description'];
               $product['taxId'] = $taxDetails->getId();
               $product['productNumber'] = bin2hex(random_bytes(16));
               $product['price'] = [
                   'currencyId' => $currencyId,
                   'gross' => $row['price_brutto'],
                   'net' => $row['price_netto']
               ];
               $product['stock'] = $row['quantity_available'];
               $product['weight'] = $row['weight'];
               $product['width'] = $row['width'];
               $product['height'] = $row['height'];
            }
        } else {
            dd("0 results");
        }
        exit;

        return new JsonResponse([
            'type' => 'Success',
            'message' => 'Main Product Imported'
        ]);
    }
}
