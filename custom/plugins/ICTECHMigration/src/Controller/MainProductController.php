<?php

declare(strict_types=1);

namespace ICTECHMigration\Controller;

use mysqli;
use Shopware\Core\Content\Media\Exception\DuplicatedMediaFileNameException;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaService;
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
    private FileSaver $fileSaver;
    private EntityRepository $mediaRepository;
    private MediaService $mediaService;
    private EntityRepository $productMediaRepository;
    private EntityRepository $snippetRepository;
    private EntityRepository $snippetSetRepository;
    private EntityRepository $mediaThumbnailSize;
    private EntityRepository $mediaFolderRepository;
    private EntityRepository $salesChannelRepository;
    private EntityRepository $categoryRepository;

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
        EntityRepository $snippetRepository,
        EntityRepository $snippetSetRepository,
        MediaService $mediaService,
        FileSaver $fileSaver,
        EntityRepository $mediaThumbnailSize,
        EntityRepository $mediaFolderRepository,
        EntityRepository $salesChannelRepository,
        EntityRepository $categoryRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->productsRepository = $productsRepository;
        $this->taxRepository = $taxRepository;
        $this->tagRepository = $tagRepository;
        $this->mediaRepository = $mediaRepository;
        $this->productMediaRepository = $productMediaRepository;
        $this->snippetRepository = $snippetRepository;
        $this->snippetSetRepository = $snippetSetRepository;
        $this->mediaService = $mediaService;
        $this->fileSaver = $fileSaver;
        $this->mediaThumbnailSize = $mediaThumbnailSize;
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/api/_action/migration/mainproduct",name="api.custom.migration.mainproduct", methods={"POST"})
     */
    public function mainProduct(Request $request): Response
    {
        $responseArray = [];
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
            while ($row = mysqli_fetch_assoc($productDetails)) {
                $productDetail = $this->checkProductExistsInProductTable($context, $row['p_id']);

                if ($productDetail === null) {
                    $this->mainProductInsert($row, $context, $conn);
                } else {
                    $this->mainProductUpdate($productDetail, $row, $context, $conn);
                }
            }
        }
        if ($offSet < $totalProduct) {
            $responseArray['type'] = 'Pending';
            $responseArray['importProduct'] = $offSet + 1;
            $responseArray['message'] = 'Product remaining';
        } else {
            $responseArray['type'] = 'Success';
            $responseArray['importProduct'] = $offSet + 1;
            $responseArray['message'] = 'Main Product Imported';
        }
        return new JsonResponse($responseArray);
    }

    // product insert
    public function mainProductInsert(array $row, Context $context, $conn): void
    {
        $products = [];
        $currencyId = $context->getCurrencyId();
        $taxDetails = $this->getTaxDetails($context);
        $languageDetails = $this->getLanguagesDetail($context);
        $defaultLanguageCode = $this->getDefaultLanguageCode($context);
        $productDataSql = 'SELECT * from product_data where product_id = '.$row['p_id'];
        $productDataDetails = mysqli_query($conn, $productDataSql);
        $productArray = [];
        $media_array = [];
        $tag_array = [];
        if (mysqli_num_rows($productDataDetails) > 0) {
            $tagIds = '';
            $mediaIds = [];
            while ($product = mysqli_fetch_assoc($productDataDetails)) {
                foreach ($languageDetails as $_language) {
                    $coverImage = [];
                    $mediaIdArray = [];
                    $mediaImage = [];
                    $languageCode = $_language->getTranslationCode()->getCode();
                    $languageArray = explode('-', $languageCode);
                    if ($product['language'] === $languageArray[0]) {
                        $productArray['name'][$languageCode] = $product['title'] === null ? '' : $product['title'];
                        $productArray['description'][$languageCode] = $product['description'] === null ? '' : $product['description'];
                        $productArray['metaTitle'][$languageCode] = $product['seo_title'] === null ? '' : $product['seo_title'];
                        $productArray['metaDescription'][$languageCode] = $product['seo_description'] === null ? '' : $product['seo_description'];

                        $additionalData = json_decode($product['additional_data']);
                        $customFieldsData = [];
                        foreach ($additionalData as $key => $value) {
                            $customFieldName = 'custom_' . $key;
                            $customFieldsData[$customFieldName] = $value;
                        }
                        $customFieldsData['custom_product_id'] = $product['product_id'];
                        $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                        $customFieldsData['custom_product_video_url'] = $product['video'];
                        $customFieldsData['custom_product_audio'] = $product['audio'];
                        $customFieldsData['custom_product_www'] = $product['www'];
                        $productArray['translations'][$languageCode]['customFields'] = $customFieldsData;

                        // get Media Id
                        $coverImage['product_id'] = $product['product_id'];
                        $coverImage['image'] = $product['image'];

                        $coverMediaId = $this->addCoverImageToMedia($context, $coverImage);

                        if ($coverMediaId) {
                            $mediaIdArray['mediaId'] = $coverMediaId;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }

                        $mediaImage['product_id'] = $product['product_id'];
                        $mediaImage['image'] = $product['image_1'];
                        $mediaId = $this->addImageToMedia($context, $mediaImage);

                        if ($mediaId) {
                            $mediaIdArray['mediaId'] = $mediaId;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }

                        $mediaImage['image'] = $product['image_2'];
                        $mediaIdImage2 = $this->addImageToMedia($context, $mediaImage);

                        if ($mediaIdImage2) {
                            $mediaIdArray['mediaId'] = $mediaIdImage2;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }

                        $mediaImage['image'] = $product['image_3'];
                        $mediaIdImage3 = $this->addImageToMedia($context, $mediaImage);

                        if ($mediaIdImage3) {
                            $mediaIdArray['mediaId'] = $mediaIdImage3;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }

                        $productArray['media'] = $mediaIds ?? '';
                    }
                }
                if (! isset($productArray['name'][$defaultLanguageCode])) {
                    $productArray['name'][$defaultLanguageCode] = $product['title'] === null ? '' : $product['title'];
                    $productArray['description'][$defaultLanguageCode] = $product['description'] === null ? '' : $product['description'];
                    $productArray['metaTitle'][$defaultLanguageCode] = $product['seo_title'] === null ? '' : $product['seo_title'];
                    $productArray['metaDescription'][$defaultLanguageCode] = $product['seo_description'] === null ? '' : $product['seo_description'];

                    $additionalData = json_decode($product['additional_data']);
                    $customFieldsData = [];
                    foreach ($additionalData as $key => $value) {
                        // read all image tags into an array
                        preg_match_all('/<img[^>]+>/i',$value, $imgTags);

                        for ($i = 0; $i < count($imgTags[0]); $i++) {
                            // get the source string
                            preg_match('/src="([^"]+)/i',$imgTags[0][$i], $imgage);

                            // remove opening 'src=' tag, can`t get the regex right
                            $origImageSrc[] = str_ireplace( 'src="', '',  $imgage[0]);
                        }

                        $customFieldName = 'custom_' . $key;
                        $customFieldsData[$customFieldName] = $value;
                    }
                    print_r($origImageSrc);
                    exit;
                    $customFieldsData['custom_product_id'] = $product['product_id'];
                    $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                    $customFieldsData['custom_product_video_url'] = $product['video'];
                    $customFieldsData['custom_product_audio'] = $product['audio'];
                    $customFieldsData['custom_product_www'] = $product['www'];
                    $productArray['translations'][$defaultLanguageCode]['customFields'] = $customFieldsData;
                }
                // get Tag ID
                if ($product['tags'] !== null && $product['tags'] !== '') {
                    $tagIds = $this->tagInsert($context, $product['tags']);
                }
            }
            $i = 0;
            if ($productArray['media']) {
                foreach ($productArray['media'] as $mediaData) {
                    if ($mediaData['mediaId']) {
                        $media_array[$i]['id'] = Uuid::randomHex();
                        $media_array[$i]['mediaId'] = $mediaData['mediaId'];
                        $media_array[$i]['position'] = $mediaData['position'] ?? '999';
                        $i++;
                    }
                }
            }
            $media_array = array_map('unserialize', array_unique(array_map('serialize', $media_array)));

            usort($media_array, function ($x, $y) {
                return $x['position'] <=> $y['position'];
            });

            $productArray['taxId'] = $taxDetails->getId();
            $productArray['productNumber'] = $row['article_number'] === '' ? bin2hex(random_bytes(16)) : $row['article_number'];
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
                $salesChannelArray = array_map('unserialize', array_unique(array_map('serialize', $salesChannelArray)));
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
                $tag_array = array_map('unserialize', array_unique(array_map('serialize', $tag_array)));
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
            $productArray['categories'] = $category_array;

            $products[] = $productArray;

            $this->productsRepository->create($products, $context);
        }
    }

    // update product
    public function mainProductUpdate($productDetail, array $row, Context $context, $conn): void
    {
        $products = [];
        $currencyId = $context->getCurrencyId();
        $languageDetails = $this->getLanguagesDetail($context);
        $productDataSql = 'SELECT * from product_data where product_id = '.$row['p_id'];
        $productDataDetails = mysqli_query($conn, $productDataSql);
        $productArray = [];
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
                        $productArray['name'][$languageCode] = $product['title'] === null ? '' : $product['title'];
                        $productArray['description'][$languageCode] = $product['description'] === null ? '' : $product['description'];
                        $productArray['metaTitle'][$languageCode] = $product['seo_title'] === null ? '' : $product['seo_title'];
                        $productArray['metaDescription'][$languageCode] = $product['seo_description'] === null ? '' : $product['seo_description'];

                        $additionalData = json_decode($product['additional_data']);
                        $customFieldsData = [];
                        foreach ($additionalData as $key => $value) {
                            $customFieldName = 'custom_' . $key;
                            $customFieldsData[$customFieldName] = $value;
                        }
                        $customFieldsData['custom_product_id'] = $product['product_id'];
                        $customFieldsData['custom_product_data_id'] = $product['pd_id'];
                        $customFieldsData['custom_product_video_url'] = $product['video'];
                        $customFieldsData['custom_product_audio'] = $product['audio'];
                        $customFieldsData['custom_product_www'] = $product['www'];
                        $productArray['translations'][$languageCode]['customFields'] = $customFieldsData;

                        $coverImage['product_id'] = $product['product_id'];
                        $coverImage['image'] = $product['image'];

                        $coverMediaId = $this->addCoverImageToMedia($context, $coverImage);

                        if ($coverMediaId) {
                            $mediaIdArray['mediaId'] = $coverMediaId;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }

                        $mediaImage['product_id'] = $product['product_id'];
                        $mediaImage['image'] = $product['image_1'];
                        $mediaId = $this->addImageToMedia($context, $mediaImage);

                        if ($mediaId) {
                            $mediaIdArray['mediaId'] = $mediaId;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }

                        $mediaImage['image'] = $product['image_2'];
                        $mediaIdImage2 = $this->addImageToMedia($context, $mediaImage);

                        if ($mediaIdImage2) {
                            $mediaIdArray['mediaId'] = $mediaIdImage2;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }

                        $mediaImage['image'] = $product['image_3'];
                        $mediaIdImage3 = $this->addImageToMedia($context, $mediaImage);

                        if ($mediaIdImage3) {
                            $mediaIdArray['mediaId'] = $mediaIdImage3;
                            $mediaIdArray['position'] = 1;
                            $mediaIds[] = $mediaIdArray;
                        }
                        $productArray['media'] = $mediaIds ?? '';
                    }
                }
            }
            $j = 0;
            $prID = $productDetail->getId();
            // set media
            if ($productArray['media']) {
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
            $media_array = array_map('unserialize', array_unique(array_map('serialize', $media_array)));

            usort($media_array, function ($x, $y) {
                return $x['position'] <=> $y['position'];
            });
            $productArray['media'] = $media_array;

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
            $productArray['id'] = $productDetail->getId();
            if (isset($media_array[0]['id']) && $media_array[0]['id']) {
                $productArray['coverId'] = $media_array[0]['id'];
            }

            // assign categories
            $categories = $this->getCategoryData($context, $row['main_category']);
            $category_array = [];
            if ($categories !== null) {
                $category_array[] = [
                    'id' => $categories->getId(),
                ];
            }
            $productArray['categories'] = $category_array;
//            dd($productArray);
            $products[] = $productArray;
            $this->productsRepository->update($products, $context);
        }
    }

    // get Tax Details
    public function getTaxDetails(Context $context)
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
    public function getDefaultLanguageCode(Context $context)
    {
        $criteriaLanguage = new Criteria();
        $criteriaLanguage->addAssociation('translationCode');
        $criteriaLanguage->addFilter(new EqualsFilter('id', $context->getLanguageId()));
        $defaultLanguage = $this->languageRepository->search($criteriaLanguage, $context)->first();

        return $defaultLanguage->getTranslationCode()->getCode();
    }

    // check product in product repository
    public function checkProductExistsInProductTranslationsTable(Context $context, string $productDataId, string $productId): int
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.custom_product_id', $productId));
        $criteria->addFilter(new EqualsFilter('customFields.custom_product_data_id', $productDataId));

        $productDetails = $this->productsRepository->search($criteria, $context);

        return $productDetails->getTotal();
    }

    // check product in product repository using product id
    public function checkProductExistsInProductTable(Context $context, string $productId)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.custom_product_id', $productId));
        return $this->productsRepository->search($criteria, $context)->first();
    }

    // Insert tag
    public function tagInsert(Context $context, string $tags): array
    {
        $tags = rtrim($tags, ',');
        $tagArray = explode(',', $tags);
        $tagIds = [];
        foreach ($tagArray as $tag) {
            $tag_array = [];
            $tagDetail = $this->searchTagsInTable($context, trim($tag));
            $tagId = '';
            if ($tagDetail !== null) {
//                $tag_array['name'] = trim($tag);
                $tag_array['tagId'] = $tagDetail->getId();
                $tagIds[] = $tag_array;
            } else {
                $tagId = Uuid::randomHex();
                $tag_array['name'] = trim($tag);
                $tag_array['tagId'] = $tagId;
                $tagIds[] = $tag_array;
            }
        }
        return $tagIds;
    }

    // check tag in repository

    /**
     * @return mixed|null
     */
    public function searchTagsInTable(Context $context, string $tag)
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
            //copy the file from the URL to the newly created local temporary file
            $filePath = tempnam(sys_get_temp_dir(), $fileName);

            @file_put_contents($filePath, @file_get_contents($coverImageUrl));
            //create media record from the image
            $mediaId = $this->createMediaFromFile($filePath, $fileName, $fileExtension, $this->folderName, $context);
        }
        return $mediaId;
    }

    // add another image in media
    public function addImageToMedia(Context $context, array $row): ?string
    {
        if ($row['image'] === '') {
            return null;
        }
        $imageUrl = $this->baseURL.$row['product_id']. '/poster/' . $row['image'];
        $mediaId = null;

        $fileNameParts = explode('.', $row['image']);

        $fileName = $fileNameParts[0];
        $fileExtension = $fileNameParts[1];

        if ($fileName && $fileExtension) {
            //copy the file from the URL to the newly created local temporary file
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
        $productMediaObjects = $this->productMediaRepository->search($criteria, $context);
        foreach ($productMediaObjects as $productMediaObject) {
            $this->productMediaRepository->delete([['id' => $productMediaObject->getID()]], $context);
        }
        return $prID;
    }

    // create media from file
    /**
     * @param array $folderName
     */
    private function createMediaFromFile(string $filePath, string $fileName, string $fileExtension, array $folderName, Context $context): ?string
    {
        $mediaId = null;

        //get additional info on the file
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        //create and save new media file to the Shopware's media library
        $mediaFile = new MediaFile($filePath, $mimeType, $fileExtension, $fileSize);
        $languageKey = $this->getDefaultLanguageCode($context);
        $languageKeyArray = explode('-', $languageKey);

        try {
            $folderId = $this->createFolderInMedia($folderName[$languageKeyArray[0]], $context);
            $mediaId = $this->createMediaId($folderId, $context);

            $this->fileSaver->persistFileToMedia($mediaFile, $fileName, $mediaId, $context);
        } catch (DuplicatedMediaFileNameException | \Exception $e) {
            /*echo($e->getMessage());*/
            $mediaId = $this->mediaCleanup($mediaId, $context);

            if (! empty($mediaId)) {
                $this->fileSaver->persistFileToMedia($mediaFile, $fileName, $mediaId, $context);
            }
        }

        //find media in shopware media
        if (empty($mediaId)) {
            $mediaId = $this->checkImageExist($fileName, $mimeType, $context);
            try {
                if (! empty($mediaFile) && $fileName !== null && $mediaId !== null) {
                    $this->fileSaver->persistFileToMedia($mediaFile, $fileName, $mediaId, $context);
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
        $mediaThumbnailObject = $this->mediaThumbnailSize->searchIds($criteria, $context)->getData();
        $mediaThumbnailArray = [];
        foreach ($mediaThumbnailObject as $media) {
            $mediaThumbnailArray[] = $media;
        }
        if (! $folderId) {
            $folderId = Uuid::randomHex();
//            $mediaId = $this->mediaFolderRepository->upsert([
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
    private function checkImageExist(string $fileName, string $mimeType, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('fileName', $fileName));
        $criteria->addFilter(new EqualsFilter('mimeType', $mimeType));
        $media_object = $this->mediaRepository->searchIds($criteria, $context);
        return $media_object->firstId();
    }

    // check folder in sw media
    private function checkFolderInMedia(string $folderName, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $folderName));
        $mediaFolderObject = $this->mediaFolderRepository->searchIds($criteria, $context);
        return $mediaFolderObject->firstId();
    }

    // get All sales channel IDs
    private function getSalesChannelId(Context $context): array
    {
        $criteria = new Criteria();
        return $this->salesChannelRepository->searchIds($criteria, $context)->getIds();
    }

    // get Category Data

    /**
     * @return mixed|null
     */
    private function getCategoryData(Context $context, string $categoryId)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.custom_category_id', $categoryId));
        return $this->categoryRepository->search($criteria, $context)->first();
    }
}
