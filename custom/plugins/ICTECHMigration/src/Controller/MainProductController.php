<?php

declare(strict_types=1);

namespace ICTECHMigration\Controller;

use mysqli;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
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

    private EntityRepository $tagRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository $languageRepository,
        EntityRepository $productsRepository,
        EntityRepository $taxRepository,
        EntityRepository $tagRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->productsRepository = $productsRepository;
        $this->taxRepository = $taxRepository;
        $this->tagRepository = $tagRepository;
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

        $productCountSql = 'SELECT COUNT(*) as total_products FROM product';
        $productCountDetails = mysqli_query($conn, $productCountSql);

        if (mysqli_num_rows($productCountDetails) > 0) {
            $row = mysqli_fetch_assoc($productCountDetails);

            $totalProduct = $row['total_products'];
        }
        $responseArray['totalProduct'] = $totalProduct;

        $productSql = 'SELECT * FROM product LIMIT 1 OFFSET '.$offSet;
        $productDetails = mysqli_query($conn, $productSql);

        if (mysqli_num_rows($productDetails) > 0) {
            while($row = mysqli_fetch_assoc($productDetails)) {
                $productDetail = $this->checkProductExistsInProductTable($context, $row['p_id']);

                if($productDetail === null) {
                    $this->mainProductInsert($row, $context, $conn);
                }else{
//                    $this->mainProductUpdate($productDetail, $row, $context, $conn);
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
        return new JsonResponse($responseArray);
    }

    public function mainProductInsert($row, $context, $conn): void
    {
        $products = array();
        $currencyId = $context->getCurrencyId();
        $taxDetails = $this->getTaxDetails($context);
        $languageDetails = $this->getLanguagesDetail($context);
        $defaultLanguageCode = $this->getDefaultLanguageCode($context);
        $productDataSql = 'SELECT * from product_data where product_id = '.$row['p_id'];
        $productDataDetails = mysqli_query($conn, $productDataSql);
        $productArray = [];
        if (mysqli_num_rows($productDataDetails) > 0) {
            $tagIds = '';
            $mediaArray = array();
            while ($product = mysqli_fetch_assoc($productDataDetails)){
                foreach ($languageDetails as $_language) {
                    $languageCode = $_language->getTranslationCode()->getCode();
                    $languageArray = explode('-', $languageCode);
                    if ($product['language'] === $languageArray[0]) {
                        $productArray['name'][$languageCode] = $product['title'] == null ? '' : $product['title'];
                        $productArray['description'][$languageCode] = $product['description'] == null ? '' : $product['description'];
                        $productArray['metaTitle'][$languageCode] = $product['seo_title'] == null ? '' : $product['seo_title'];
                        $productArray['metaDescription'][$languageCode] = $product['seo_description'] == null ? '' : $product['seo_description'];

                        $additionalData = json_decode($product['additional_data']);
                        $customFieldsData = [];
                        foreach ($additionalData as $key => $value) {
                            $customFieldName = "custom_" . $key;
                            $customFieldsData[$customFieldName] = $value;
                        }
                        $customFieldsData['custom_product_id'] = $product['product_id'];
                        $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                        $customFieldsData['custom_product_video_url'] = $product['video'];
                        $customFieldsData['custom_product_audio'] = $product['audio'];
                        $customFieldsData['custom_product_www'] = $product['www'];
                        $productArray['translations'][$languageCode]['customFields'] = $customFieldsData;

//                        $mediaArray[] = $this->mediaCoverInsert($context, $product);
                    }
                }
                if (empty($productArray['name'][$defaultLanguageCode])) {
                    $productArray['name'][$defaultLanguageCode] = $product['title'] == null ? '' : $product['title'];
                    $productArray['description'][$defaultLanguageCode] = $product['description'] == null ? '' : $product['description'];
                    $productArray['metaTitle'][$defaultLanguageCode] = $product['seo_title'] == null ? '' : $product['seo_title'];
                    $productArray['metaDescription'][$defaultLanguageCode] = $product['seo_description'] == null ? '' : $product['seo_description'];

                    $additionalData = json_decode($product['additional_data']);
                    $customFieldsData = [];
                    foreach ($additionalData as $key => $value) {
                        $customFieldName = "custom_" . $key;
                        $customFieldsData[$customFieldName] = $value;
                    }
                    $customFieldsData['custom_product_id'] = $product['product_id'];
                    $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                    $customFieldsData['custom_product_video_url'] = $product['video'];
                    $customFieldsData['custom_product_audio'] = $product['audio'];
                    $customFieldsData['custom_product_www'] = $product['www'];
                    $productArray['translations'][$defaultLanguageCode]['customFields'] = $customFieldsData;
                }
                if ($product['tags'] !== null && $product['tags'] !== "") {
                    $tagIds = $this->tagInsert($context, $product['tags']);
                }
            }
//            dd($productArray);
            $productArray['taxId'] = $taxDetails->getId();
            $productArray['productNumber'] = $row['article_number'] == '' ? bin2hex(random_bytes(16)) : $row['article_number'];
            $productArray['price'] = [
                [
                    'currencyId' => $currencyId,
                    'gross' => $row['price_brutto'] == null ? 0 : $row['price_brutto'],
                    'net' => $row['price_netto'] == null ? 0 : $row['price_netto'],
                    "linked" => true,
                ],
            ];
            $productArray['stock'] = (int)$row['quantity_available'];
            $productArray['weight'] = $row['weight'];
            $productArray['width'] = $row['width'];
            $productArray['height'] = $row['height'];
//            $productArray['tagIds'][] = $tagIds;
//            dd($productArray);

            $products[] = $productArray;
            $result = $this->productsRepository->create($products, $context);
//            $productInsertId = $result->getPrimaryKeys('product');
//
//            $productDetail = $this->productsRepository->search(new Criteria([$productInsertId]), $context)->first();
//            $tagDetails = $this->tagRepository->search(new Criteria([$tagIds]), $context)->getEntities();
//            if(!empty($tagIds)) {
//                $productDetail->setTags($tagDetails);
//                $productDetail->setTagIds($tagIds);
//            }
        }
    }

    public function getTaxDetails($context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('position',1));
        return $this->taxRepository->search($criteria, $context)->first();
    }

    public function getLanguagesDetail($context): EntitySearchResult
    {
        $criteriaLanguage = new Criteria();
        $criteriaLanguage->addAssociation('translationCode');
        $criteriaLanguage->addSorting(new FieldSorting('createdAt','ASC'));
        return $this->languageRepository->search($criteriaLanguage, $context);
    }

    public function getDefaultLanguageCode($context)
    {
        $criteriaLanguage = new Criteria();
        $criteriaLanguage->addAssociation('translationCode');
        $criteriaLanguage->addFilter(new EqualsFilter('id',$context->getLanguageId()));
        $defaultLanguage = $this->languageRepository->search($criteriaLanguage,$context)->first();

        return $defaultLanguage->getTranslationCode()->getCode();
    }

    public function checkProductExistsInProductTranslationsTable($context, $productDataId, $productId): int
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.custom_product_id', $productId));
//        $criteria->addFilter(new EqualsFilter('customFields.custom_product_data_id', $productDataId));

        $productDetails = $this->productsRepository->search($criteria, $context);

        return $productDetails->getTotal();
    }

    public function checkProductExistsInProductTable($context, $productId)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.custom_product_id',$productId));
        return $this->productsRepository->search($criteria, $context)->first();
    }

    public function mainProductUpdate($productDetail, $row, $context, $conn)
    {
        $products = array();
        $currencyId = $context->getCurrencyId();
        $taxDetails = $this->getTaxDetails($context);
        $languageDetails = $this->getLanguagesDetail($context);
        $defaultLanguageCode = $this->getDefaultLanguageCode($context);
        $productDataSql = 'SELECT * from product_data where product_id = '.$row['p_id'];
        $productDataDetails = mysqli_query($conn, $productDataSql);
        $productArray = [];
        if (mysqli_num_rows($productDataDetails) > 0) {
            while ($product = mysqli_fetch_assoc($productDataDetails)){
                $productExists = $this->checkProductExistsInProductTranslationsTable($context, $product['pd_id'], $product['product_id']);
                if($productExists <= 0) {
                    foreach ($languageDetails as $_language) {
                        $languageCode = $_language->getTranslationCode()->getCode();
                        $languageArray = explode('-', $languageCode);
                        if ($product['language'] === $languageArray[0]) {
                            $productArray['name'][$languageCode] = $product['title'] == null ? '' : $product['title'];
                            $productArray['description'][$languageCode] = $product['description'] == null ? '' : $product['description'];
                            $productArray['metaTitle'][$languageCode] = $product['seo_title'] == null ? '' : $product['seo_title'];
                            $productArray['metaDescription'][$languageCode] = $product['seo_description'] == null ? '' : $product['seo_description'];

                            $additionalData = json_decode($product['additional_data']);
                            $customFieldsData = [];
                            foreach ($additionalData as $key => $value) {
                                $customFieldName = "custom_" . $key;
                                $customFieldsData[$customFieldName] = $value;
                            }
                            $customFieldsData['custom_product_id'] = $product['product_id'];
                            $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                            $customFieldsData['custom_product_video_url'] = $product['video'];
                            $customFieldsData['custom_product_audio'] = $product['audio'];
                            $customFieldsData['custom_product_www'] = $product['www'];
                            $productArray['translations'][$languageCode]['customFields'] = $customFieldsData;
                        }
                    }
                    if (empty($productArray['name'][$defaultLanguageCode])) {
                        $productArray['name'][$defaultLanguageCode] = $product['title'] == null ? '' : $product['title'];
                        $productArray['description'][$defaultLanguageCode] = $product['description'] == null ? '' : $product['description'];
                        $productArray['metaTitle'][$defaultLanguageCode] = $product['seo_title'] == null ? '' : $product['seo_title'];
                        $productArray['metaDescription'][$defaultLanguageCode] = $product['seo_description'] == null ? '' : $product['seo_description'];

                        $additionalData = json_decode($product['additional_data']);
                        $customFieldsData = [];
                        foreach ($additionalData as $key => $value) {
                            $customFieldName = "custom_" . $key;
                            $customFieldsData[$customFieldName] = $value;
                        }
                        $customFieldsData['custom_product_id'] = $product['product_id'];
                        $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                        $customFieldsData['custom_product_video_url'] = $product['video'];
                        $customFieldsData['custom_product_audio'] = $product['audio'];
                        $customFieldsData['custom_product_www'] = $product['www'];
                        $productArray['translations'][$defaultLanguageCode]['customFields'] = $customFieldsData;
                    }
                }
            }
            $productArray['taxId'] = $taxDetails->getId();
            $productArray['productNumber'] = bin2hex(random_bytes(16));
            $productArray['price'] = [
                [
                    'currencyId' => $currencyId,
                    'gross' => $row['price_brutto'] == null ? 0 : $row['price_brutto'],
                    'net' => $row['price_netto'] == null ? 0 : $row['price_netto'],
                    "linked" => true,
                ],
            ];
            $productArray['stock'] = (int)$row['quantity_available'];
            $productArray['weight'] = $row['weight'];
            $productArray['width'] = $row['width'];
            $productArray['height'] = $row['height'];
            $productArray['id'] = $productDetail->getId();
            $products[] = $productArray;
//            dd($products);
            $this->productsRepository->update($products, $context);
        }
    }

    public function tagInsert($context, $tags)
    {
        $tags = rtrim($tags,",");
        $tagArray = explode(",", $tags);
        $tagIds  = [];
        foreach ($tagArray as $tag){
            $tagDetail = $this->searchTagsInTable($context, trim($tag));
            $tagId = '';
            $tagData = [];
            if ($tagDetail !== null) {
                $tagIds[] = $tagDetail->getId();
                $tagData = [
                    'id' => $tagDetail->getId(),
                    'name' => trim($tag),
                ];
            } else{
                $tagId = Uuid::randomHex();
                $tagIds[] = $tagId;
                $tagData = [
                    'id' => $tagId,
                    'name' => trim($tag),
                ];
            }
            $this->tagRepository->upsert([$tagData], $context);
        }
//        dd($tagIds);
        return $tagIds;
    }

    public function searchTagsInTable($context, $tag)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name',$tag));
        return $this->tagRepository->search($criteria, $context)->first();
    }

    public function mediaCoverInsert($context, $row)
    {
        $baseURL = "https://www.purivox.com/uploads/shop/";

        $baseURL .= $row['product_id'];
        $coverImageUrl = $baseURL . "/" . $row['image'];
//        dd($coverImageUrl);

    }

    public function mediaInsert($context, $row)
    {
        $baseURL = "https://www.purivox.com/uploads/shop/";
    }
}
