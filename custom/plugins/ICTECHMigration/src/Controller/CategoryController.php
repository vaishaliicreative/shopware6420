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
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function insertCategory(Request $request): Response
    {
        $responseArray = [];
        $context = Context::createDefaultContext();

        $servername = $this->systemConfigService->get('ICTECHMigration.config.databaseHost');
        $username = $this->systemConfigService->get('ICTECHMigration.config.databaseUser');
        $password = $this->systemConfigService->get('ICTECHMigration.config.databasePassword');
        $database = 'usrdb_amanwyeh5';

        $conn = new mysqli($servername, $username, $password, $database);

        $offSet = $request->request->get('offSet');
        $totalCategory = 0;

        $categoryCountSql = 'SELECT COUNT(*) as total_categories FROM product_category';
        $categoryCountDetails = mysqli_query($conn, $categoryCountSql);

        if (mysqli_num_rows($categoryCountDetails) > 0) {
            $row = mysqli_fetch_assoc($categoryCountDetails);

            $totalCategory = $row['total_categories'];
        }
        $responseArray['totalCategory'] = $totalCategory;

        $categorySql = 'SELECT * FROM product_category LIMIT 1 OFFSET '.$offSet;
        $categoryDetails = mysqli_query($conn, $categorySql);

        if (mysqli_num_rows($categoryDetails) > 0) {
            while ($row = mysqli_fetch_assoc($categoryDetails)) {
                $categoryDetail = $this->checkCategoryExistsInCategoryTable($context, $row['pc_id']);

                if ($categoryDetail === null) {
                    $this->mainCategoryInsert($row, $context, $conn);
                } else {
                    $this->mainCategoryUpdate($categoryDetail, $row, $context, $conn);
                }
            }
        }
        if ($offSet < $totalCategory) {
            $responseArray['type'] = 'Pending';
            $responseArray['importCategoryCount'] = $offSet + 1;
            $responseArray['message'] = 'Category remaining';
        } else {
            $responseArray['type'] = 'Success';
            $responseArray['importCategoryCount'] = $offSet + 1;
            $responseArray['message'] = 'Category Imported';
        }

        return new JsonResponse($responseArray);
    }

    public function mainCategoryInsert(array $row, Context $context, $conn): void
    {
        $categories = [];
        $categoryArray = [];
        $languageDetails = $this->getLanguagesDetail($context);
        $defaultLanguageCode = $this->getDefaultLanguageCode($context);
        $categoryDataSql = 'SELECT * from product_category_data where category_id = '.$row['pc_id'];
        $categoryDataDetails = mysqli_query($conn, $categoryDataSql);

        if ($row['referto_pc_id'] === '0') {
            $parentData = $this->getFirstLevelParentId($context);
        } else {
            $parentData = $this->getParentId($context, $row['referto_pc_id']);
        }
        $parentId = $parentData->getId();
        if (mysqli_num_rows($categoryDataDetails) > 0) {
            while ($category = mysqli_fetch_assoc($categoryDataDetails)) {
                foreach ($languageDetails as $_language) {
                    $customFieldsData = [];
                    $languageCode = $_language->getTranslationCode()->getCode();
                    $languageArray = explode('-', $languageCode);
                    if ($category['language'] === $languageArray[0]) {
                        $categoryArray['name'][$languageCode] = $category['title'] === null ? '' : $category['title'];
                        $categoryArray['description'][$languageCode] = $category['description'] === null ? '' : $category['description'];

                        $customFieldsData['custom_category_id'] = $row['pc_id'];
                        $categoryArray['translations'][$languageCode]['customFields'] = $customFieldsData;
                    }

                    if (! isset($categoryArray['name'][$defaultLanguageCode])) {
                        $categoryArray['name'][$languageCode] = $category['title'] === null ? '' : $category['title'];
                        $categoryArray['description'][$languageCode] = $category['description'] === null ? '' : $category['description'];

                        $customFieldsData['custom_category_id'] = $row['pc_id'];
                        $categoryArray['translations'][$languageCode]['customFields'] = $customFieldsData;
                    }
                }
            }
            $categoryArray['parentId'] = $parentId;

            $categories[] = $categoryArray;

            $this->categoryRepository->create($categories, $context);
        }
    }

    public function mainCategoryUpdate($categoryDetail, array $row, Context $context, $conn): void
    {
        $categories = [];
        $categoryArray = [];
        $languageDetails = $this->getLanguagesDetail($context);
        $categoryDataSql = 'SELECT * from product_category_data where category_id = '.$row['pc_id'];
        $categoryDataDetails = mysqli_query($conn, $categoryDataSql);

        if ($row['referto_pc_id'] === '0') {
            $parentData = $this->getFirstLevelParentId($context);
        } else {
            $parentData = $this->getParentId($context, $row['referto_pc_id']);
        }
        $parentId = $parentData->getId();
        if (mysqli_num_rows($categoryDataDetails) > 0) {
            while ($category = mysqli_fetch_assoc($categoryDataDetails)) {
                $customFieldsData = [];
                foreach ($languageDetails as $_language) {
                    $languageCode = $_language->getTranslationCode()->getCode();
                    $languageArray = explode('-', $languageCode);
                    if ($category['language'] === $languageArray[0]) {
                        $categoryArray['name'][$languageCode] = $category['title'] === null ? '' : $category['title'];
                        $categoryArray['description'][$languageCode] = $category['description'] === null ? '' : $category['description'];

                        $customFieldsData['custom_category_id'] = $row['pc_id'];
                        $categoryArray['translations'][$languageCode]['customFields'] = $customFieldsData;
                    }
                }
            }
            $categoryArray['parentId'] = $parentId;
            $categoryArray['id'] = $categoryDetail->getId();
            $categories[] = $categoryArray;

            $this->categoryRepository->update($categories, $context);
        }
    }

    public function checkCategoryExistsInCategoryTable(Context $context, string $categoryId)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.custom_category_id', $categoryId));
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
    public function getDefaultLanguageCode(Context $context)
    {
        $criteriaLanguage = new Criteria();
        $criteriaLanguage->addAssociation('translationCode');
        $criteriaLanguage->addFilter(new EqualsFilter('id', $context->getLanguageId()));
        $defaultLanguage = $this->languageRepository->search($criteriaLanguage, $context)->first();

        return $defaultLanguage->getTranslationCode()->getCode();
    }

    //get default parent id
    public function getFirstLevelParentId(Context $context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('level', '1'));
        return $this->categoryRepository->search($criteria, $context)->first();
    }

    public function getParentId(Context $context, $parentId)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.custom_category_id', $parentId));
        return $this->categoryRepository->search($criteria, $context)->first();
    }
}
