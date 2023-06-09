<?php

declare(strict_types=1);

namespace ICTECHMigration\Controller;

use mysqli;
use Shopware\Core\Content\Media\Exception\DuplicatedMediaFileNameException;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    private FileSaver $fileSaver;
    private EntityRepository $mediaRepository;
    private MediaService $mediaService;
    private EntityRepository $productMediaRepository;
    private EntityRepository $mediaThumbnailSize;
    private EntityRepository $mediaFolderRepository;
    private EntityRepository $salesChannelRepository;
    private EntityRepository $categoryRepository;
    private EntityRepository $ruleRepository;
    private EntityRepository $productPriceRepository;

    private string $baseURL = 'https://www.purivox.com/uploads/shop/';

    private array $folderName = [
        'en' => 'Product Media',
        'de' => 'Product Media',
        'es' => 'Product Media',
        'fr' => 'Product Media',
        'it' => 'Product Media',
    ];

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository $languageRepository,
        EntityRepository $productsRepository,
        EntityRepository $taxRepository,
        EntityRepository $tagRepository,
        EntityRepository $mediaRepository,
        EntityRepository $productMediaRepository,
        MediaService $mediaService,
        FileSaver $fileSaver,
        EntityRepository $mediaThumbnailSize,
        EntityRepository $mediaFolderRepository,
        EntityRepository $salesChannelRepository,
        EntityRepository $categoryRepository,
        EntityRepository $ruleRepository,
        EntityRepository $productPriceRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->productsRepository = $productsRepository;
        $this->taxRepository = $taxRepository;
        $this->tagRepository = $tagRepository;
        $this->mediaRepository = $mediaRepository;
        $this->productMediaRepository = $productMediaRepository;
        $this->mediaService = $mediaService;
        $this->fileSaver = $fileSaver;
        $this->mediaThumbnailSize = $mediaThumbnailSize;
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->categoryRepository = $categoryRepository;
        $this->ruleRepository = $ruleRepository;
        $this->productPriceRepository = $productPriceRepository;
    }

    /**
     * @Route("/api/_action/migration/mainproduct",name="api.custom.migration.mainproduct", methods={"POST"})
     */
    public function mainProduct(Context $context): Response
    {
        $responseArray = [];
        $servername = $this->systemConfigService
            ->get('ICTECHMigration.config.databaseHost');
        $username = $this->systemConfigService
            ->get('ICTECHMigration.config.databaseUser');
        $password = $this->systemConfigService
            ->get('ICTECHMigration.config.databasePassword');
        $database = $this->systemConfigService
            ->get('ICTECHMigration.config.databaseName');

        $conn = new mysqli($servername, $username, $password, $database);

        $totalProduct = 0;
        $offSet = $this->systemConfigService->get('ICTECHMigration.config.mainProductCount');

        $productCountSql = 'SELECT COUNT(*) as total_products FROM product WHERE referto_id = 0';
        $productCountDetails = mysqli_query($conn, $productCountSql);

        if (mysqli_num_rows($productCountDetails) > 0) {
            $row = mysqli_fetch_assoc($productCountDetails);

            $totalProduct = $row['total_products'];
        }
        $responseArray['totalProduct'] = $totalProduct;

        $productSql = 'SELECT * FROM product WHERE referto_id = 0 ORDER BY p_id ASC LIMIT 1 OFFSET '.$offSet;
        $productDetails = mysqli_query($conn, $productSql);

        if (mysqli_num_rows($productDetails) > 0) {
            while ($row = mysqli_fetch_assoc($productDetails)) {
                $productDetail = $this->checkProductExistsInProductTable(
                    $context,
                    $row['p_id']
                );
                if ($productDetail === null) {
                    $this->mainProductInsert($row, $context, $conn);
                } else {
                    $this->mainProductUpdate(
                        $productDetail,
                        $row,
                        $context,
                        $conn
                    );
                }
                $currentCount = $offSet + 1;
                $this->systemConfigService
                    ->set('ICTECHMigration.config.mainProductCount', $currentCount);
            }
        }
        if ($offSet < $totalProduct) {
            $responseArray['type'] = 'Pending';
            $responseArray['importProduct'] = $offSet + 1;
            $responseArray['message'] = 'Product remaining';
        } elseif ($offSet > $totalProduct) {
            $responseArray['type'] = 'Success';
            $responseArray['message'] = 'Main Product Already Imported';
        } else {
            $this->systemConfigService
                ->set('ICTECHMigration.config.mainProductCount', 0);
            $responseArray['type'] = 'Success';
            $responseArray['importProduct'] = $offSet + 1;
            $responseArray['message'] = 'Main Product Imported';
        }
        return new JsonResponse($responseArray);
    }

    // product insert
    public function mainProductInsert(
        array $row,
        Context $context,
        $conn
    ): void {
        $products = [];
        $currencyId = $context->getCurrencyId();
        $taxDetails = $this->getTaxDetails($context);
        $languageDetails = $this->getLanguagesDetail($context);
        $defaultLanguageCode = $this->getDefaultLanguageCode($context);
        $productDataSql = 'SELECT * from product_data WHERE product_id = '.$row['p_id'];
        $productDataDetails = mysqli_query($conn, $productDataSql);
        $productArray = [];
        $media_array = [];
        $tag_array = [];
        if (mysqli_num_rows($productDataDetails) > 0) {
            $tagIds = [];
            $mediaIds = [];
            while ($product = mysqli_fetch_assoc($productDataDetails)) {
                $coverImage = [];
                $mediaIdArray = [];
                $mediaImage = [];
                foreach ($languageDetails as $_language) {
                    $languageCode = $_language->getTranslationCode()->getCode();
                    $languageArray = explode('-', $languageCode);
                    if ($product['language'] === $languageArray[0]) {
                        if ($product['title'] !== '' && $product['title'] !== null) {
                            $productArray['name'][$languageCode] = $product['title'];
                        } else {
                            $productArray['name'][$languageCode] = 'dummy migration';
                        }

                        $productArray['description'][$languageCode] = $product['description'] === null ? '' : $product['description'];
                        $productArray['metaTitle'][$languageCode] = $product['seo_title'] === null ? '' : $product['seo_title'];
                        $productArray['metaDescription'][$languageCode] = $product['seo_description'] === null ? '' : $product['seo_description'];

                        $customFieldsData = [];
                        if ($product['additional_data'] !== '' && $product['additional_data'] !== null) {
                            $additionalData = json_decode($product['additional_data']);
                            foreach ($additionalData as $key => $value) {
                                $customFieldName = 'custom_' . $key;
                                $customFieldsData[$customFieldName] = $value;
                            }
                        }
                        $customFieldsData['custom_product_id'] = $product['product_id'];
                        $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                        $customFieldsData['custom_product_video_url'] = $product['video'];
                        $customFieldsData['custom_product_audio'] = $product['audio'];
                        $customFieldsData['custom_product_www'] = $product['www'];
                        $productArray['translations'][$languageCode]['customFields'] = $customFieldsData;

                        // get Media Id
                        if ($product['image'] !== '') {
                            $coverImage['product_id'] = $product['product_id'];
                            $coverImage['image'] = $product['image'];

                            $coverMediaId = $this->addCoverImageToMedia(
                                $context,
                                $coverImage
                            );

                            if ($coverMediaId) {
                                $mediaIdArray['mediaId'] = $coverMediaId;
                                $mediaIdArray['position'] = 1;
                                $mediaIds[] = $mediaIdArray;
                            }
                        }
                        $mediaImage['product_id'] = $product['product_id'];

                        if ($product['image_1'] !== '') {
                            $mediaImage['image'] = $product['image_1'];
                            $mediaId = $this->addImageToMedia(
                                $context,
                                $mediaImage
                            );

                            if ($mediaId) {
                                $mediaIdArray['mediaId'] = $mediaId;
                                $mediaIdArray['position'] = 1;
                                $mediaIds[] = $mediaIdArray;
                            }
                        }

                        if ($product['image_2'] !== '') {
                            $mediaImage['image'] = $product['image_2'];
                            $mediaIdImage2 = $this->addImageToMedia(
                                $context,
                                $mediaImage
                            );

                            if ($mediaIdImage2) {
                                $mediaIdArray['mediaId'] = $mediaIdImage2;
                                $mediaIdArray['position'] = 1;
                                $mediaIds[] = $mediaIdArray;
                            }
                        }

                        if ($product['image_3'] !== '') {
                            $mediaImage['image'] = $product['image_3'];
                            $mediaIdImage3 = $this->addImageToMedia(
                                $context,
                                $mediaImage
                            );

                            if ($mediaIdImage3) {
                                $mediaIdArray['mediaId'] = $mediaIdImage3;
                                $mediaIdArray['position'] = 1;
                                $mediaIds[] = $mediaIdArray;
                            }
                        }
                        $productArray['media'] = $mediaIds ?? '';
                    }
                }

                if (! isset($productArray['name'][$defaultLanguageCode])) {
                    if ($product['title'] !== '' && $product['title'] !== null) {
                        $productArray['name'][$defaultLanguageCode] = $product['title'];
                    } else {
                        $productArray['name'][$defaultLanguageCode] = 'dummy migration';
                    }
                    $productArray['description'][$defaultLanguageCode] = $product['description'] === null ? '' : $product['description'];
                    $productArray['metaTitle'][$defaultLanguageCode] = $product['seo_title'] === null ? '' : $product['seo_title'];
                    $productArray['metaDescription'][$defaultLanguageCode] = $product['seo_description'] === null ? '' : $product['seo_description'];

                    $customFieldsData = [];
                    if ($product['additional_data'] !== '' && $product['additional_data'] !== null) {
                        $additionalData = json_decode($product['additional_data']);
                        foreach ($additionalData as $key => $value) {
                            $customFieldName = 'custom_' . $key;
                            $customFieldsData[$customFieldName] = $value;
                        }
                    }
                    $customFieldsData['custom_product_id'] = $product['product_id'];
                    $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                    $customFieldsData['custom_product_video_url'] = $product['video'];
                    $customFieldsData['custom_product_audio'] = $product['audio'];
                    $customFieldsData['custom_product_www'] = $product['www'];
                    $productArray['translations'][$defaultLanguageCode]['customFields'] = $customFieldsData;

                    if ($product['image'] !== '') {
                        $coverImage['product_id'] = $product['product_id'];
                        $coverImage['image'] = $product['image'];

                        $coverMediaId = $this->addCoverImageToMedia(
                            $context,
                            $coverImage
                        );

                        if ($coverMediaId) {
                            $mediaIdArray['mediaId'] = $coverMediaId;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }
                    }

                    $mediaImage['product_id'] = $product['product_id'];
                    if ($product['image_1'] !== '') {
                        $mediaImage['image'] = $product['image_1'];
                        $mediaId = $this->addImageToMedia(
                            $context,
                            $mediaImage
                        );

                        if ($mediaId) {
                            $mediaIdArray['mediaId'] = $mediaId;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }
                    }

                    if ($product['image_2'] !== '') {
                        $mediaImage['image'] = $product['image_2'];
                        $mediaIdImage2 = $this->addImageToMedia(
                            $context,
                            $mediaImage
                        );

                        if ($mediaIdImage2) {
                            $mediaIdArray['mediaId'] = $mediaIdImage2;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }
                    }

                    if ($product['image_3'] !== '') {
                        $mediaImage['image'] = $product['image_3'];
                        $mediaIdImage3 = $this->addImageToMedia(
                            $context,
                            $mediaImage
                        );

                        if ($mediaIdImage3) {
                            $mediaIdArray['mediaId'] = $mediaIdImage3;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }
                    }

                    $productArray['media'] = $mediaIds ?? '';
                }
                // get Tag ID
                if ($product['tags'] !== null && $product['tags'] !== '') {
                    $tagIds = $this->tagInsert($context, $product['tags']);
                }
            }
            $i = 0;
            if (isset($productArray['media'])) {
                foreach ($productArray['media'] as $mediaData) {
                    if ($mediaData['mediaId']) {
                        $media_array[$i]['id'] = Uuid::randomHex();
                        $media_array[$i]['mediaId'] = $mediaData['mediaId'];
                        $media_array[$i]['position'] = $mediaData['position'] ?? '999';
                        $i++;
                    }
                }
            }
            $media_array = array_map(
                'unserialize',
                array_unique(array_map('serialize', $media_array))
            );

            usort($media_array, function ($x, $y) {
                return $x['position'] <=> $y['position'];
            });

            $productArray['taxId'] = $taxDetails->getId();

            if ($row['article_number'] !== '' && $row['article_number'] !== null) {
                $productNumberDetails = $this->checkProductNumberExistsInProductTable(
                    $row['article_number'],
                    $context
                );
                if ($productNumberDetails > 0) {
                    $productArray['productNumber'] = bin2hex(random_bytes(16));
                } else {
                    $productArray['productNumber'] = $row['article_number'];
                }
            } else {
                $productArray['productNumber'] = bin2hex(random_bytes(16));
            }

            $productArray['price'] = [
                [
                    'currencyId' => $currencyId,
                    'gross' => $row['price_brutto'] === null ? 0 : $row['price_brutto'],
                    'net' => $row['price_netto'] === null ? 0 : $row['price_netto'],
                    'linked' => true,
                ],
            ];
            $productArray['stock'] = (int) $row['quantity_available'];
            $productArray['weight'] = $row['weight'];
            $productArray['width'] = $row['width'];
            $productArray['height'] = $row['height'];
            $productArray['media'] = $media_array;
            if (isset($media_array[0]['id']) && $media_array[0]['id']) {
                $productArray['coverId'] = $media_array[0]['id'];
            }

            // assign sales channel
            $saleChannelIds = $this->getSalesChannelId($context);
            $salesChannelArray = [];
            foreach ($saleChannelIds as $saleChannelId) {
                $salesChannelArray[] = [
                    'salesChannelId' => $saleChannelId,
                    'visibility' => 30,
                ];
            }

            if ($salesChannelArray) {
                $salesChannelArray = array_map(
                    'unserialize',
                    array_unique(array_map('serialize', $salesChannelArray))
                );
            }
            $productArray['visibilities'] = $salesChannelArray;

            // assign tags
            $tag_array = [];
            if ($tagIds) {
                foreach ($tagIds as $tagId) {
                    $tag_array[] = $tagId;
                }
            }
            if ($tag_array) {
                $tag_array = array_map(
                    'unserialize',
                    array_unique(array_map('serialize', $tag_array))
                );
            }
            $productArray['tags'] = $tag_array;

            // assign categories
            $categories = $this->getCategoryData($context, $row['main_category']);
            $category_array = [];
            if ($categories !== null) {
                $category_array[] = [
                    'id' => $categories->getId(),
                ];
            }

            $subCategories = $this->getAllCategoryData($row['p_id'], $conn, $context);
            if (isset($subCategories) && !empty($subCategories)) {
                foreach ($subCategories as $category){
                    $category_array[] = [
                        'id' => $category->getId(),
                    ];
                }
            }
            $category_array = array_map(
                "unserialize",
                array_unique(array_map("serialize", $category_array))
            );
            $productArray['categories'] = $category_array;

            $products[] = $productArray;
            $this->productsRepository->create($products, $context);
        }
    }

    // update product
    public function mainProductUpdate(
        $productDetail,
        array $row,
        Context $context,
        $conn
    ): void {
        $products = [];

        $languageDetails = $this->getLanguagesDetail($context);
        $productDataSql = 'SELECT * from product_data WHERE product_id = '.$row['p_id'];
        $productDataDetails = mysqli_query($conn, $productDataSql);
        $productArray = [];
        $media_array = [];
        if (mysqli_num_rows($productDataDetails) > 0) {
            while ($product = mysqli_fetch_assoc($productDataDetails)) {
                $mediaIds = [];
                foreach ($languageDetails as $_language) {
                    $mediaIdArray = [];
                    $coverImage = [];
                    $mediaImage = [];
                    $languageCode = $_language->getTranslationCode()->getCode();
                    $languageArray = explode('-', $languageCode);
                    if ($product['language'] === $languageArray[0]) {
                        if ($product['title'] !== '' && $product['title'] !== null) {
                            $productArray['name'][$languageCode] = $product['title'];
                        } else {
                            $productArray['name'][$languageCode] = 'dummy migration';
                        }
                        $productArray['description'][$languageCode] = $product['description'] === null ? '' : $product['description'];
                        $productArray['metaTitle'][$languageCode] = $product['seo_title'] === null ? '' : $product['seo_title'];
                        $productArray['metaDescription'][$languageCode] = $product['seo_description'] === null ? '' : $product['seo_description'];

                        $customFieldsData = [];
                        if ($product['additional_data'] !== '' && $product['additional_data'] !== null) {
                            $additionalData = json_decode($product['additional_data']);

                            foreach ($additionalData as $key => $value) {
                                $customFieldName = 'custom_' . $key;
                                $customFieldsData[$customFieldName] = $value;
                            }
                        }
                        $customFieldsData['custom_product_id'] = $product['product_id'];
                        $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                        $customFieldsData['custom_product_video_url'] = $product['video'];
                        $customFieldsData['custom_product_audio'] = $product['audio'];
                        $customFieldsData['custom_product_www'] = $product['www'];
                        $productArray['translations'][$languageCode]['customFields'] = $customFieldsData;

                        if ($product['image'] !== '') {
                            $coverImage['product_id'] = $product['product_id'];
                            $coverImage['image'] = $product['image'];

                            $coverMediaId = $this->addCoverImageToMedia(
                                $context,
                                $coverImage
                            );

                            if ($coverMediaId) {
                                $mediaIdArray['mediaId'] = $coverMediaId;
                                $mediaIdArray['position'] = 1;
                                $mediaIds[] = $mediaIdArray;
                            }
                        }

                        $mediaImage['product_id'] = $product['product_id'];
                        if ($product['image_1'] !== '') {
                            $mediaImage['image'] = $product['image_1'];
                            $mediaId = $this->addImageToMedia(
                                $context,
                                $mediaImage
                            );

                            if ($mediaId) {
                                $mediaIdArray['mediaId'] = $mediaId;
                                $mediaIdArray['position'] = 1;
                                $mediaIds[] = $mediaIdArray;
                            }
                        }

                        if ($product['image_2'] !== '') {
                            $mediaImage['image'] = $product['image_2'];
                            $mediaIdImage2 = $this->addImageToMedia(
                                $context,
                                $mediaImage
                            );

                            if ($mediaIdImage2) {
                                $mediaIdArray['mediaId'] = $mediaIdImage2;
                                $mediaIdArray['position'] = 1;
                                $mediaIds[] = $mediaIdArray;
                            }
                        }

                        if ($product['image_3'] !== '') {
                            $mediaImage['image'] = $product['image_3'];
                            $mediaIdImage3 = $this->addImageToMedia(
                                $context,
                                $mediaImage
                            );

                            if ($mediaIdImage3) {
                                $mediaIdArray['mediaId'] = $mediaIdImage3;
                                $mediaIdArray['position'] = 1;
                                $mediaIds[] = $mediaIdArray;
                            }
                        }
                        $productArray['media'] = $mediaIds ?? '';
                    }
                }
            }
            $j = 0;
            $prID = $productDetail->getId();
            // set media
            if (isset($productArray['media'])) {
                $this->removeProductMedia($prID, $context);
                foreach ($productArray['media'] as $mediaData) {
                    if ($mediaData['mediaId']) {
                        $media_array[$j]['id'] = Uuid::randomHex();
                        $media_array[$j]['mediaId'] = $mediaData['mediaId'];
                        $media_array[$j]['position'] = $mediaData['position'] ?? '999';
                        $j++;
                    }
                }
            }
            $media_array = array_map(
                'unserialize',
                array_unique(array_map('serialize', $media_array))
            );

            usort($media_array, function ($x, $y) {
                return $x['position'] <=> $y['position'];
            });
            $productArray['media'] = $media_array;

            $productArray['price'] = $this->getPrices($row, $context);
//            dd($productArray['price']);
            $productArray['stock'] = (int) $row['quantity_available'];
            $productArray['weight'] = $row['weight'];
            $productArray['width'] = $row['width'];
            $productArray['height'] = $row['height'];
            $productArray['id'] = $productDetail->getId();
            if (isset($media_array[0]['id']) && $media_array[0]['id']) {
                $productArray['coverId'] = $media_array[0]['id'];
            }

            // assign categories
            $categories = $this->getCategoryData(
                $context,
                $row['main_category']
            );
            $category_array = [];
            if ($categories !== null) {
                $category_array[] = [
                    'id' => $categories->getId(),
                ];
            }
            $subCategories = $this->getAllCategoryData($row['p_id'], $conn, $context);
            if (isset($subCategories) && !empty($subCategories)) {
                foreach ($subCategories as $category){
                    $category_array[] = [
                        'id' => $category->getId(),
                    ];
                }
            }
            $category_array = array_map(
                "unserialize",
                array_unique(array_map("serialize", $category_array))
            );
            $productArray['categories'] = $category_array;
            $prices = $this->getAdvancedPrices($row['p_id'], $conn, $context, $prID);
//            dd($row);
            if (! empty($prices)) {
                $productArray['prices'] = $prices;
            }
            $products[] = $productArray;
//            dd($productArray);
            $this->productsRepository->update($products, $context);
        }
    }

    // get Tax Details
    public function getTaxDetails(Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('position', 1));
        return $this->taxRepository->search($criteria, $context)->first();
    }

    // get Language detail
    public function getLanguagesDetail(Context $context): EntitySearchResult
    {
        $criteriaLanguage = new Criteria();
        $criteriaLanguage->addAssociation('translationCode');
        $criteriaLanguage->addSorting(new FieldSorting('createdAt', 'ASC'));
        return $this->languageRepository->search($criteriaLanguage, $context);
    }

    // get default language code
    public function getDefaultLanguageCode(Context $context): ?string
    {
        $criteriaLanguage = new Criteria();
        $criteriaLanguage->addAssociation('translationCode');
        $criteriaLanguage->addFilter(new EqualsFilter('id', $context->getLanguageId()));
        $defaultLanguage = $this->languageRepository->search(
            $criteriaLanguage,
            $context
        )->first();

        return $defaultLanguage->getTranslationCode()->getCode();
    }

    // check product in product repository using product id
    public function checkProductExistsInProductTable(
        Context $context,
        string $productId
    ): ?Entity {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.custom_product_id', $productId));
        return $this->productsRepository->search($criteria, $context)->first();
    }

    // Insert tag
    public function tagInsert(Context $context, string $tags): array
    {
        $tags = rtrim($tags, ' ');
        $tags = rtrim($tags, ',');
        $tagArray = explode(',', $tags);
        $tagIds = [];
        foreach ($tagArray as $tag) {
            $tag_array = [];
            $tagDetail = $this->searchTagsInTable($context, trim($tag));
            $tagId = '';
            if ($tagDetail !== null) {
                $tagIds[] = ['id' => $tagDetail->getId()];
            } else {
                $tagId = Uuid::randomHex();
                $tag_array['name'] = trim($tag);
                $tag_array['id'] = $tagId;
                $tagIds[] = ['id' => $tagId];
                $this->tagRepository->create([$tag_array], $context);
            }
        }
        return $tagIds;
    }

    // check tag in repository
    public function searchTagsInTable(Context $context, string $tag): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $tag));
        return $this->tagRepository->search($criteria, $context)->first();
    }

    // add cover image in Media
    public function addCoverImageToMedia(Context $context, array $row): ?string
    {
        $coverImageUrl = $this->baseURL.$row['product_id']. '/' . $row['image'];
        $mediaId = null;
        $fileNameParts = explode('.', $row['image']);

        $fileName = $fileNameParts[0];
        $fileExtension = $fileNameParts[1];

        if ($fileName && $fileExtension) {
            $filePath = tempnam(sys_get_temp_dir(), $fileName);

            @file_put_contents($filePath, @file_get_contents($coverImageUrl));
            //create media record from the image
            $mediaId = $this->createMediaFromFile(
                $filePath,
                $fileName,
                $fileExtension,
                $this->folderName,
                $context
            );
        }
        return $mediaId;
    }

    // add another image in media
    public function addImageToMedia(Context $context, array $row): ?string
    {
        if ($row['image'] === '') {
            return null;
        }
        $imageUrl = $this->baseURL.$row['product_id']. '/' . $row['image'];
        $mediaId = null;

        if (! @file_get_contents($imageUrl)) {
            $imageUrl = $this->baseURL.$row['product_id'].'/poster/'.$row['image'];
        }
        $fileNameParts = explode('.', $row['image']);

        $fileName = $fileNameParts[0];
        $fileExtension = $fileNameParts[1];

        if ($fileName && $fileExtension) {
            $filePath = tempnam(sys_get_temp_dir(), $fileName);
            @file_put_contents($filePath, @file_get_contents($imageUrl));
            //create media record from the image
            $mediaId = $this->createMediaFromFile($filePath, $fileName, $fileExtension, $this->folderName, $context);
        }
        return $mediaId;
    }

    // remove media from product media repository
    public function removeProductMedia(string $prID, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $prID));
        $productMediaObjects = $this->productMediaRepository->search(
            $criteria,
            $context
        );
        foreach ($productMediaObjects as $productMediaObject) {
            $this->productMediaRepository->delete(
                [['id' => $productMediaObject->getID()]],
                $context
            );
        }
        return $prID;
    }

    // create media from file
    /**
     * @param array $folderName
     */
    private function createMediaFromFile(
        string $filePath,
        string $fileName,
        string $fileExtension,
        array $folderName,
        Context $context
    ): ?string {
        $mediaId = null;

        //get additional info on the file
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        //create and save new media file to the Shopware's media library
        $mediaFile = new MediaFile(
            $filePath,
            $mimeType,
            $fileExtension,
            $fileSize
        );
        $languageKey = $this->getDefaultLanguageCode($context);
        $languageKeyArray = explode('-', $languageKey);

        try {
            $folderId = $this->createFolderInMedia(
                $folderName[$languageKeyArray[0]],
                $context
            );
            $mediaId = $this->createMediaId($folderId, $context);

            $this->fileSaver->persistFileToMedia(
                $mediaFile,
                $fileName,
                $mediaId,
                $context
            );
        } catch (DuplicatedMediaFileNameException | \Exception $e) {
            $mediaId = $this->mediaCleanup($mediaId, $context);

            if (isset($mediaId)) {
                $this->fileSaver->persistFileToMedia(
                    $mediaFile,
                    $fileName,
                    $mediaId,
                    $context
                );
            }
        }

        //find media in shopware media
        if (! isset($mediaId)) {
            $mediaId = $this->checkImageExist($fileName, $mimeType, $context);
            try {
                if (isset($mediaFile) && $fileName !== null && $mediaId !== null) {
                    $this->fileSaver->persistFileToMedia(
                        $mediaFile,
                        $fileName,
                        $mediaId,
                        $context
                    );
                }
            } catch (DuplicatedMediaFileNameException | \Exception $e) {
                echo $e->getMessage();
            }
        }
        return $mediaId;
    }

    // create folder in sw media
    private function createFolderInMedia(string $folderName, Context $context): ?string
    {
        $folderId = $this->checkFolderInMedia($folderName, $context);
        $criteria = new Criteria();
        $mediaThumbnailObject = $this->mediaThumbnailSize->searchIds(
            $criteria,
            $context
        )->getData();
        $mediaThumbnailArray = [];
        foreach ($mediaThumbnailObject as $media) {
            $mediaThumbnailArray[] = $media;
        }
        if (! $folderId) {
            $folderId = Uuid::randomHex();
            $this->mediaFolderRepository->upsert([
                [
                    'id' => $folderId,
                    'name' => $folderName,
                    'useParentConfiguration' => false,
                    'configuration' => [
                        'id' => Uuid::randomHex(),
                        'createThumbnails' => true,
                        'keepAspectRatio' => true,
                        'thumbnailQuality' => 80,
                        'mediaThumbnailSizes' => $mediaThumbnailArray,
                    ],
                ],
            ], $context);
        }
        return $folderId;
    }

    // create media id
    private function createMediaId(string $folderId, Context $context): ?string
    {
        $mediaId = Uuid::randomHex();
        $this->mediaRepository->create(
            [
                [
                    'id' => $mediaId,
                    'private' => false,
                    'mediaFolderId' => $folderId,
                ],
            ],
            $context
        );
        return $mediaId;
    }

    // remove media from media repository
    /**
     * @return null
     */
    private function mediaCleanup(string $mediaId, Context $context)
    {
        if ($mediaId) {
            $this->mediaRepository->delete([['id' => $mediaId]], $context);
        }
        return null;
    }

    // check image in media repository
    private function checkImageExist(
        string $fileName,
        string $mimeType,
        Context $context
    ): ?string {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('fileName', $fileName));
        $criteria->addFilter(new EqualsFilter('mimeType', $mimeType));
        $media_object = $this->mediaRepository->searchIds($criteria, $context);
        return $media_object->firstId();
    }

    // check folder in sw media
    private function checkFolderInMedia(
        string $folderName,
        Context $context
    ): ?string {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $folderName));
        $mediaFolderObject = $this->mediaFolderRepository->searchIds(
            $criteria,
            $context
        );
        return $mediaFolderObject->firstId();
    }

    // get All sales channel IDs
    private function getSalesChannelId(Context $context): array
    {
        $criteria = new Criteria();
        return $this->salesChannelRepository->searchIds(
            $criteria,
            $context
        )->getIds();
    }

    // get Category Data
    private function getCategoryData(Context $context, string $categoryId): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.custom_category_id',
                $categoryId
            )
        );
        return $this->categoryRepository->search($criteria, $context)->first();
    }

    // check product number in product repository
    private function checkProductNumberExistsInProductTable(
        string $productNumber,
        Context $context
    ): int {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'productNumber',
                $productNumber
            )
        );
        return $this->productsRepository->search(
            $criteria,
            $context
        )->getTotal();
    }

    private function getAdvancedPrices($product_id, $conn, Context $context, $currentProduct): array
    {
        $priceArray = [];
        $currencyId = $context->getCurrencyId();
        $ruleDetails = $this->getRuleId($context);
        $rule_id = $ruleDetails->getId();
        $priceSql = 'SELECT * FROM product_prices WHERE product_id = '.$product_id;
        $priceDetails = mysqli_query($conn, $priceSql);

        $productPrice = $this->checkProductPrice($currentProduct, $context);
        if ($productPrice) {
            $this->removeProductPrice($currentProduct, $context);
        }

        if (mysqli_num_rows($priceDetails) > 0) {
            $price_array = [];
            while ($price = mysqli_fetch_assoc($priceDetails)) {
                $price_array = [
                    'productId' => $currentProduct,
                    'quantityStart' => (int) $price['number_of_pieces'],
                    'ruleId' => $rule_id,
                    'price' => [
                        [
                            'currencyId' => $currencyId,
                            'gross' => $price['price_per_piece_brutto'] === null ? 0 : $price['price_per_piece_brutto'],
                            'net' => $price['price_per_piece_netto'] === null ? 0 : $price['price_per_piece_netto'],
                            'linked' => true,
                        ],
                    ],
                ];

                $this->productPriceRepository->create([$price_array], $context);
//                $priceArray[] = $price_array;
            }
        }
//        dd($priceArray);
        return $priceArray;
    }

    private function checkProductPrice($productId, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'productId',
                $productId
            )
        );
        return $this->productPriceRepository->search($criteria, $context)->getIds();
    }

    private function removeProductPrice($productId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $productPriceObjects = $this->productPriceRepository->search(
            $criteria,
            $context
        );
        foreach ($productPriceObjects as $productPriceObject) {
            $this->productPriceRepository->delete(
                [['id' => $productPriceObject->getID()]],
                $context
            );
        }
    }

    private function getRuleId(Context $context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'priority',
                1
            )
        );
        $criteria->addSorting(
            new FieldSorting('name', 'ASC')
        );

        return $this->ruleRepository->search($criteria, $context)->first();
    }

    private function getAllCategoryData($product_id, $conn, Context $context): array
    {
        $categoryArray = [];
        $categorySql = 'SELECT * FROM product2category WHERE product_id = '.$product_id;
        $categoryDetails = mysqli_query($conn, $categorySql);
        if (mysqli_num_rows($categoryDetails) > 0) {
            while ($category = mysqli_fetch_assoc($categoryDetails)) {
                $categoryDetail = $this->getCategoryData($context, $category['category_id']);
                $categoryArray[] = $categoryDetail;
            }
        }
        return $categoryArray;
    }

    private function getPrices($row, Context $context): array
    {
//        dd($row);
        $priceArray = [];
        $currencyId = $context->getCurrencyId();
        $price_array = [];
        if (($row['price_special_brutto'] === null || $row['price_special_brutto'] === '0.00') &&
            ($row['price_special_netto'] === null || $row['price_special_netto'] === '0.00')) {
            $price_array = [
                'currencyId' => $currencyId,
                'gross' => $row['price_brutto'] === null ? 0 : $row['price_brutto'],
                'net' => $row['price_netto'] === null ? 0 : $row['price_netto'],
                'linked' => true,
            ];
        } else {
            $price_array = [
                'currencyId' => $currencyId,
                'gross' => $row['price_special_brutto'] === null ? 0 : $row['price_special_brutto'],
                'net' => $row['price_special_netto'] === null ? 0 : $row['price_special_netto'],
                'linked' => true,
            ];
            $list_price = [
                'currencyId' => $currencyId,
                'gross' => $row['price_brutto'] === null ? 0 : $row['price_brutto'],
                'net' => $row['price_netto'] === null ? 0 : $row['price_netto'],
                'linked' => true,
            ];
            $price_array['listPrice'] = $list_price;
        }
        $priceArray[] = $price_array;
//        dd($priceArray);
        return $priceArray;
    }
}
