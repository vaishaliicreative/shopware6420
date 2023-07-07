<?php

declare(strict_types=1);

namespace ICTECHPropertyMigration\Controller;

use mysqli;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class ProductVariantController extends AbstractController
{
    private SystemConfigService $systemConfigService;
    private EntityRepository $productsRepository;
    private EntityRepository $propertyGroupOptionRepository;
    private EntityRepository $productConfiguratorSettingRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository $productsRepository,
        EntityRepository $propertyGroupOptionRepository,
        EntityRepository $productConfiguratorSettingRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->productsRepository = $productsRepository;
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
        $this->productConfiguratorSettingRepository = $productConfiguratorSettingRepository;
    }

    /**
     * @Route("/api/_action/migration/migratevariant",name="api.custom.migration.migratevariant", methods={"POST"})
     */
    public function importProductVariant(Context $context): Response
    {
        $responseArray = [];
        $conn = (new CommonMySQLController($this->systemConfigService))->getMySqlConnection();

        $offSet = $this->systemConfigService
            ->get('ICTECHPropertyMigration.config.variantCount');

        $totalVariant = $this->getVariantTotalCount($conn);
        $responseArray['totalVariant'] = $totalVariant;

        $productVariantSql = 'SELECT * FROM s_plugin_mofa_product_options_article 
         ORDER BY articleDetailId ASC LIMIT 1 OFFSET '.$offSet;
        $productVariantDetails = mysqli_query($conn, $productVariantSql);

        if (mysqli_num_rows($productVariantDetails) > 0) {
            while ($row = mysqli_fetch_assoc($productVariantDetails)) {
                $this->productVariantUpsert($row, $context, $conn);
                $currentCount = $offSet + 1;
                $this->systemConfigService
                    ->set(
                        'ICTECHPropertyMigration.config.variantCount',
                        $currentCount
                    );
            }
        }
        if ($offSet < $totalVariant) {
            $responseArray['type'] = 'Pending';
            $responseArray['importVariantCount'] = $offSet + 1;
            $responseArray['message'] = 'Variant remaining';
        } elseif ($offSet > $totalVariant) {
            $responseArray['type'] = 'Success';
            $responseArray['message'] = 'Variant Already migrated';
        } else {
            $this->systemConfigService
                ->set('ICTECHPropertyMigration.config.variantCount', 0);
            $responseArray['type'] = 'Success';
            $responseArray['importVariantCount'] = $offSet + 1;
            $responseArray['message'] = 'Variant migrated';
        }
        return new JsonResponse($responseArray);
    }

    // get total count of Variant
    public function getVariantTotalCount(mysqli $conn): int
    {
        $totalVariant = 0;
        $variantCountSql = 'SELECT COUNT(*) as total_product_variant
                FROM s_plugin_mofa_product_options_article';
        $variantCountDetails = mysqli_query($conn, $variantCountSql);

        if (mysqli_num_rows($variantCountDetails) > 0) {
            $row = mysqli_fetch_assoc($variantCountDetails);

            $totalVariant = (int) $row['total_product_variant'];
        }
        return $totalVariant;
    }

    // add/update variant product in product repository
    public function productVariantUpsert(
        array $row,
        Context $context,
        mysqli $conn
    ): void {
        $variantDetail = $this->checkVariantExistsInVariantTable(
            $context,
            $row['id']
        );

        if ($variantDetail === null) {
            $variantId = Uuid::randomHex();
        } else {
            $variantId = $variantDetail->getId();
        }

        $variantDataDetails = $this->getVariantDetailsFromCore(
            $conn,
            $row['articleDetailId']
        );
        $customFieldsData = [];
        if (count($variantDataDetails)) {
            $parentData = $this->getParentProductData(
                $context,
                $variantDataDetails['ordernumber']
            );
            if ($parentData !== null) {
                $parentId = $parentData->getId();

                $variants = $this->getVariantData($context, $row['OptionID']);

                $variantArray = [];
                $variant_array = [];
                if ($variants !== null) {
                    $variant_array[] = [
                        'id' => $variants->getId(),
                    ];
                }
                $productNumber = $parentData->getProductNumber().'.'.rand(1, 999);
                $customFieldsData['custom_product_id'] = $row['id'];
                $variantArray['customFields'] = $customFieldsData;
                $variantArray['productNumber'] = $productNumber;
                $variantArray['stock'] = 0;
                $variantArray['parentId'] = $parentId;
                $variantArray['id'] = $variantId;
                $variantArray['options'] = $variant_array;

                $this->productsRepository->upsert([$variantArray], $context);

                $configuratorSetting = [];
                $configuratorSetting['productId'] = $parentId;
                $configuratorSetting['optionId'] = $variants->getId();

                $configuratorSettingData = $this->getconfiguratorSettingData(
                    $context,
                    $configuratorSetting
                );

                if ($configuratorSettingData !== null) {
                    $configuratorSettingId = $configuratorSettingData->getId();
                } else {
                    $configuratorSettingId = Uuid::randomHex();
                }

                $configuratorSetting['id'] = $configuratorSettingId;

                $this->productConfiguratorSettingRepository->upsert(
                    [$configuratorSetting],
                    $context
                );
            }
        }
    }

    // check variant in product repository
    public function checkVariantExistsInVariantTable(
        Context $context,
        string $productId
    ): ?Entity {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.custom_product_id',
                $productId
            )
        );
        return $this->productsRepository->search(
            $criteria,
            $context
        )->first();
    }

    // get variant details from SW5 table
    public function getVariantDetailsFromCore(
        mysqli $conn,
        string $articleDetailId
    ): array {
        $variantDataDetailsArray = [];
        $variantDataSql = 'SELECT * from
             s_articles_details
             WHERE 	id = '.$articleDetailId;
        $variantDataDetails = mysqli_query($conn, $variantDataSql);
        if (mysqli_num_rows($variantDataDetails) > 0) {
            while ($property = mysqli_fetch_assoc($variantDataDetails)) {
                $variantDataDetailsArray = $property;
            }
        }
        return $variantDataDetailsArray;
    }

    // Get parent ID from product repository
    public function getParentProductData(
        Context $context,
        string $productNumber
    ): ?Entity {
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
        )->first();
    }

    //get option ID from Property group option repository
    public function getVariantData(
        Context $context,
        string $optionId
    ): ?Entity {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.custom_property_group_option_id',
                $optionId
            )
        );
        return $this->propertyGroupOptionRepository->search(
            $criteria,
            $context
        )->first();
    }

    // get product configuration setting data from productConfiguratorSetting repository
    public function getconfiguratorSettingData(
        Context $context,
        array $configuratorSetting
    ): ?Entity {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'productId',
                $configuratorSetting['productId']
            )
        );
        $criteria->addFilter(
            new EqualsFilter(
                'optionId',
                $configuratorSetting['optionId']
            )
        );

        return $this->productConfiguratorSettingRepository->search(
            $criteria,
            $context
        )->first();
    }
}
