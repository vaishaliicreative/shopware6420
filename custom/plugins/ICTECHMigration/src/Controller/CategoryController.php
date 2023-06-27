<?php

declare(strict_types=1);

namespace ICTECHMigration\Controller;

use mysqli;
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
class CategoryController extends AbstractController
{
    private SystemConfigService $systemConfigService;

    private EntityRepository $languageRepository;

    private EntityRepository $categoryRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository $languageRepository,
        EntityRepository $categoryRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/api/_action/migration/addcategory",name="api.custom.migration.category", methods={"POST"})
     */
    public function insertCategory(Context $context): Response
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

        $totalCategory = 0;
        $offSet = $this->systemConfigService
            ->get('ICTECHMigration.config.categoryCount');

        $categoryCountSql = 'SELECT COUNT(*) as total_categories
                            FROM product_category';
        $categoryCountDetails = mysqli_query($conn, $categoryCountSql);

        if (mysqli_num_rows($categoryCountDetails) > 0) {
            $row = mysqli_fetch_assoc($categoryCountDetails);

            $totalCategory = $row['total_categories'];
        }
        $responseArray['totalCategory'] = $totalCategory;

        $categorySql = 'SELECT * FROM product_category
                        ORDER BY pc_id ASC LIMIT 1 OFFSET '.$offSet;
        $categoryDetails = mysqli_query($conn, $categorySql);

        if (mysqli_num_rows($categoryDetails) > 0) {
            while ($row = mysqli_fetch_assoc($categoryDetails)) {
                $this->mainCategoryUpsert($row, $context, $conn);
                $currentCount = $offSet + 1;
                $this->systemConfigService
                    ->set('ICTECHMigration.config.categoryCount', $currentCount);
            }
        }
        if ($offSet < $totalCategory) {
            $responseArray['type'] = 'Pending';
            $responseArray['importCategoryCount'] = $offSet + 1;
            $responseArray['message'] = 'Category remaining';
        } elseif ($offSet > $totalCategory) {
            $responseArray['type'] = 'Success';
            $responseArray['message'] = 'Category Already Imported';
        } else {
            $this->systemConfigService
                ->set('ICTECHMigration.config.categoryCount', 0);
            $responseArray['type'] = 'Success';
            $responseArray['importCategoryCount'] = $offSet + 1;
            $responseArray['message'] = 'Category Imported';
        }
        return new JsonResponse($responseArray);
    }

    public function mainCategoryUpsert(
        array $row,
        Context $context,
        $conn
    ): void {
        $categoryDetail = $this->checkCategoryExistsInCategoryTable(
            $context,
            $row['pc_id']
        );
        if ($categoryDetail === null) {
            $categoryId = Uuid::randomHex();
        } else {
            $categoryId = $categoryDetail->getId();
        }
        $categories = [];
        $categoryArray = [];
        $parentData = [];
        $categoryDataSql = 'SELECT * from translations_categories WHERE pc_id = '.$row['pc_id'];
        $categoryDataDetails = mysqli_query($conn, $categoryDataSql);

        $categoryStringArray = str_split($row['number'], 2);
        if ($categoryStringArray[0] !== '00') {
            $parentData = $this->getFirstLevelParentId($context);
        }

        if ($categoryStringArray[1] !== '00') {
            $coreParentId = $categoryStringArray[0].'000000';
            $parentData = $this->getSecondLevelParentId($context, $coreParentId, $conn);
        }

        if ($categoryStringArray[2] !== '00') {
            $coreParentId = $categoryStringArray[0].$categoryStringArray[1].'0000';
            $parentData = $this->getSecondLevelParentId($context, $coreParentId, $conn);
        }

        if ($categoryStringArray[3] !== '00') {
            $coreParentId = $categoryStringArray[0].$categoryStringArray[1].$categoryStringArray[2].'00';
            $parentData = $this->getSecondLevelParentId($context, $coreParentId, $conn);
        }

        $parentId = $parentData->getId();
        if (mysqli_num_rows($categoryDataDetails) > 0) {
            while ($category = mysqli_fetch_assoc($categoryDataDetails)) {
                $categoryArray['name']['en-GB'] = $category['english'] === null ? '' : $category['english'];
                $customFieldsData['custom_category_id'] = $row['pc_id'];
                $categoryArray['translations']['en-GB']['customFields'] = $customFieldsData;

                $categoryArray['name']['de-DE'] = $category['german'] === null ? '' : $category['german'];
                $customFieldsData['custom_category_id'] = $row['pc_id'];
                $categoryArray['translations']['de-DE']['customFields'] = $customFieldsData;

                $categoryArray['name']['es-ES'] = $category['spanish'] === null ? '' : $category['spanish'];
                $customFieldsData['custom_category_id'] = $row['pc_id'];
                $categoryArray['translations']['es-ES']['customFields'] = $customFieldsData;

                $categoryArray['name']['fr-FR'] = $category['french'] === null ? '' : $category['french'];
                $customFieldsData['custom_category_id'] = $row['pc_id'];
                $categoryArray['translations']['fr-FR']['customFields'] = $customFieldsData;

                $categoryArray['name']['it-IT'] = $category['italian'] === null ? '' : $category['italian'];
                $customFieldsData['custom_category_id'] = $row['pc_id'];
                $categoryArray['translations']['it-IT']['customFields'] = $customFieldsData;
            }
            $categoryArray['parentId'] = $parentId;
            $categoryArray['id'] = $categoryId;

            $categories[] = $categoryArray;

            $this->categoryRepository->upsert($categories, $context);
        }
    }

    public function checkCategoryExistsInCategoryTable(
        Context $context,
        string $categoryId
    ): ?Entity {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.custom_category_id',
                $categoryId
            )
        );
        return $this->categoryRepository->search($criteria, $context)->first();
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
        $criteriaLanguage->addFilter(
            new EqualsFilter(
                'id',
                $context->getLanguageId()
            )
        );
        $defaultLanguage = $this->languageRepository->search(
            $criteriaLanguage,
            $context
        )->first();

        return $defaultLanguage->getTranslationCode()->getCode();
    }

    //get default parent id
    public function getFirstLevelParentId(Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('level', '1'));
        return $this->categoryRepository->search($criteria, $context)->first();
    }

    public function getParentId(Context $context, $parentId): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.custom_category_id',
                $parentId
            )
        );
        return $this->categoryRepository->search($criteria, $context)->first();
    }

    private function getSecondLevelParentId(Context $context, $parentId, $conn)
    {
        $parentData = [];
        $categorySql = 'SELECT * FROM product_category WHERE number = '.$parentId;
        $categoryDetails = mysqli_query($conn, $categorySql);

        if (mysqli_num_rows($categoryDetails) > 0) {
            while ($row = mysqli_fetch_assoc($categoryDetails)) {
                $parentData = $this->getParentId($context, $row['pc_id']);
            }
        }
        return $parentData;
    }
}
