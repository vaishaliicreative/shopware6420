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
    public function mainProduct(Request $request): Response
    {
        $context = Context::createDefaultContext();
        $servername = $this->systemConfigService->get('ICTECHMigration.config.databaseHost');
        $username = $this->systemConfigService->get('ICTECHMigration.config.databaseUser');
        $password = $this->systemConfigService->get('ICTECHMigration.config.databasePassword');
        $database = 'usrdb_amanwyeh5';

        $conn = new mysqli($servername, $username, $password, $database);

        $offSet = $request->request->get('offSet');
        $totalProduct = 0;

        $currencyId = $context->getCurrencyId();


        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('position',1));
        $taxDetails = $this->taxRepository->search($criteria, $context)->first();

        $criteriaLanguage = new Criteria();
        $languageDetails = $this->languageRepository->search($criteriaLanguage, $context);

        $productCountSql = 'SELECT COUNT(*) as total_products FROM product';
        $productCountDetails = mysqli_query($conn, $productCountSql);

        if (mysqli_num_rows($productCountDetails) > 0) {
            $row = mysqli_fetch_assoc($productCountDetails);
//            dd($row);
            $totalProduct = $row['total_products'];
        }
        $responseArray['totalProduct'] = $totalProduct;

        $productSql = 'SELECT * FROM product LIMIT 1 OFFSET '.$offSet;
//        $productSql = 'SELECT * FROM product INNER JOIN product_data ON product.p_id = product_data.product_id ORDER BY product_id';
        $productDetails = mysqli_query($conn, $productSql);

        if (mysqli_num_rows($productDetails) > 0) {
            while($row = mysqli_fetch_assoc($productDetails)) {
                $products = array();
                $productDataSql = 'SELECT * from product_data where product_id = '.$row['p_id'];
                $productDataDetails = mysqli_query($conn,$productDataSql);
                if (mysqli_num_rows($productDataDetails) > 0) {
                    $productArray = [];
                    while ($product = mysqli_fetch_assoc($productDataDetails)){
                        if($product['language'] === 'en'){
                            $productArray['name']['en-GB'] = $product['title'];
                            $productArray['description']['en-GB'] = $product['description'];
                            $productArray['metaTitle']['en-GB'] = $product['seo_title'];
                            $productArray['metaDescription']['en-GB'] = $product['seo_description'];
                        }
                        if($product['language'] === 'de'){
                            $productArray['name']['de-DE'] = $product['title'];
                            $productArray['description']['de-DE'] = $product['description'];
                            $productArray['metaTitle']['de-DE'] = $product['seo_title'];
                            $productArray['metaDescription']['de-DE'] = $product['seo_description'];
                        }
                    }
                    $productArray['taxId'] = $taxDetails->getId();
                    $productArray['productNumber'] = bin2hex(random_bytes(16));
                    $productArray['price'] = [
                        [
                            'currencyId' => $currencyId,
                            'gross' => $row['price_brutto'],
                            'net' => $row['price_netto'],
                            "linked" => true,
                        ],
                    ];
                    $productArray['stock'] = (int)$row['quantity_available'];
                    $productArray['weight'] = $row['weight'];
                    $productArray['width'] = $row['width'];
                    $productArray['height'] = $row['height'];
                    $products[] = $productArray;
                    $this->productsRepository->create($products, $context);

                }
            }
        }
        if($offSet < $totalProduct){
            $responseArray['type'] = 'Pending';
            $responseArray['importProduct'] = ($offSet+1);
            $responseArray['message'] = 'Product remaining';
        }else{
            $responseArray['type'] = 'Success';
            $responseArray['importProduct'] = ($offSet+1);
            $responseArray['message'] = 'Main Product Imported';
        }
//        print_r($languageArray);
//        print_r($productDetailsArray);
//        echo json_encode($productDetailsArray);

        return new JsonResponse($responseArray);
    }
}
