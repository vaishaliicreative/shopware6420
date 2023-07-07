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
class PropertyGroupController extends AbstractController
{
    private SystemConfigService $systemConfigService;
    private EntityRepository $languageRepository;
    private EntityRepository $propertyGroupRepository;

    private array $displayType = [
        'Dropdown' => 'select',
        'Radiobutton' => 'media',
    ];

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository $languageRepository,
        EntityRepository $propertyGroupRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->languageRepository = $languageRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
    }

    /**
     * @Route("/api/_action/migration/migratepropertygroup",name="api.custom.migration.migratepropertygroup", methods={"POST"})
     */
    public function importPropertyGroup(Context $context): Response
    {
        $responseArray = [];

        $conn = (new CommonMySQLController($this->systemConfigService))->getMySqlConnection();

        $offSet = $this->systemConfigService
            ->get('ICTECHPropertyMigration.config.propertyGroupCount');

        $totalPropertyGroup = $this->getPropertyGroupTotalCount($conn);
        $responseArray['totalPropertyGroup'] = $totalPropertyGroup;

        $propertyGroupSql = 'SELECT * FROM s_plugin_mofa_product_options_groups 
         ORDER BY id ASC LIMIT 1 OFFSET '.$offSet;
        $propertyGroupDetails = mysqli_query($conn, $propertyGroupSql);

        if (mysqli_num_rows($propertyGroupDetails) > 0) {
            while ($row = mysqli_fetch_assoc($propertyGroupDetails)) {
                $this->propertyGroupUpsert($row, $context, $conn);
                $currentCount = $offSet + 1;
                $this->systemConfigService
                    ->set(
                        'ICTECHPropertyMigration.config.propertyGroupCount',
                        $currentCount
                    );
            }
        }

        if ($offSet < $totalPropertyGroup) {
            $responseArray['type'] = 'Pending';
            $responseArray['importPropertyGroupCount'] = $offSet + 1;
            $responseArray['message'] = 'Property Group remaining';
        } elseif ($offSet > $totalPropertyGroup) {
            $responseArray['type'] = 'Success';
            $responseArray['message'] = 'Property Group Already migrated';
        } else {
            $this->systemConfigService
                ->set('ICTECHPropertyMigration.config.propertyGroupCount', 0);
            $responseArray['type'] = 'Success';
            $responseArray['importPropertyGroupCount'] = $offSet + 1;
            $responseArray['message'] = 'Property Group migrated';
        }
        return new JsonResponse($responseArray);
    }

    // get total count of Property Group
    public function getPropertyGroupTotalCount(mysqli $conn): int
    {
        $totalPropertyGroup = 0;
        $propertyGroupCountSql = 'SELECT COUNT(*) as total_property_groups
                FROM s_plugin_mofa_product_options_groups';
        $propertyGroupCountDetails = mysqli_query($conn, $propertyGroupCountSql);

        if (mysqli_num_rows($propertyGroupCountDetails) > 0) {
            $row = mysqli_fetch_assoc($propertyGroupCountDetails);

            $totalPropertyGroup = (int) $row['total_property_groups'];
        }
        return $totalPropertyGroup;
    }

    // add/update property group data in product group repository
    /**
     * @param array $row
     */
    public function propertyGroupUpsert(
        array $row,
        Context $context,
        mysqli $conn
    ): void {
        $propertyGroupDetail = $this->checkPropertyExistsInPropertyTable(
            $context,
            $row['id']
        );

        if ($propertyGroupDetail === null) {
            $propertyGroupId = Uuid::randomHex();
        } else {
            $propertyGroupId = $propertyGroupDetail->getId();
        }
        $propertyDataDetails = $this->getPropertyGroupDetailsFromCore($conn, $row['id']);

        $languageDetails = (new LanguageController($this->languageRepository))
            ->getLanguagesDetail($context);
        $defaultLanguageCode = (new LanguageController($this->languageRepository))
            ->getDefaultLanguageCode($context);
        $propertyGroupArray = [];
        if (count($propertyDataDetails)) {
            foreach ($propertyDataDetails as $_property) {
                foreach ($languageDetails as $_language) {
                    $customFieldsData = [];
                    $languageCode = $_language->getTranslationCode()->getCode();
                    $propertyLan = str_replace('_', '-', $_property['locale']);
                    if ($propertyLan === $languageCode) {
                        if ($_property['Name'] !== '' && $_property['Name'] !== null) {
                            $propertyGroupArray['name'][$languageCode] = $_property['Name'];
                        } else {
                            $propertyGroupArray['name'][$languageCode] = 'dummy migration';
                        }

                        if ($_property['ShortDescription'] !== null) {
                            $propertyGroupArray['description'][$languageCode] = $_property['ShortDescription'];
                        } else {
                            $propertyGroupArray['description'][$languageCode] = $_property['LongDescription'];
                        }
                        $customFieldsData['custom_property_group_id'] = $_property['GroupID'];
                        $propertyGroupArray['translations'][$languageCode]['customFields'] = $customFieldsData;
                    }
                }
                if ($propertyGroupDetail === null) {
                    if (! isset($propertyGroupArray['name'][$defaultLanguageCode])) {
                        if ($_property['Name'] !== '' && $_property['Name'] !== null) {
                            $propertyGroupArray['name'][$defaultLanguageCode] = $_property['Name'];
                        } else {
                            $propertyGroupArray['name'][$defaultLanguageCode] = 'dummy migration';
                        }

                        if ($_property['ShortDescription'] !== null) {
                            $propertyGroupArray['description'][$defaultLanguageCode] = $_property['ShortDescription'];
                        } else {
                            $propertyGroupArray['description'][$defaultLanguageCode] = $_property['LongDescription'];
                        }
                        $customFieldsData['custom_property_group_id'] = $_property['GroupID'];
                        $propertyGroupArray['translations'][$defaultLanguageCode]['customFields'] = $customFieldsData;
                    }
                }
            }
            $propertyGroupArray['displayType'] = $this->displayType[$row['FieldTyp']];
            if ($row['filterable'] === '1') {
                $propertyGroupArray['filterable'] = true;
            } else {
                $propertyGroupArray['filterable'] = false;
            }
            $propertyGroupArray['id'] = $propertyGroupId;
            $this->propertyGroupRepository->upsert(
                [$propertyGroupArray],
                $context
            );
        }
    }

    // check Property group in Property group reposity
    public function checkPropertyExistsInPropertyTable(
        Context $context,
        string $propertyGroupId
    ): ?Entity {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.custom_property_group_id',
                $propertyGroupId
            )
        );
        return $this->propertyGroupRepository->search(
            $criteria,
            $context
        )->first();
    }

    // get Details of property group from SW5 table
    /**
     * @return array
     */
    public function getPropertyGroupDetailsFromCore(
        mysqli $conn,
        string $groupId
    ): array {
        $propertyDataDetailsArray = [];
        $propertyDataSql = 'SELECT spmpogd.*, scl.locale from
             s_plugin_mofa_product_options_groups_desc spmpogd
             INNER JOIN s_core_locales scl
             ON scl.id=spmpogd.LocaleId
             WHERE spmpogd.GroupID = '.$groupId;
        $propertyDataDetails = mysqli_query($conn, $propertyDataSql);
        if (mysqli_num_rows($propertyDataDetails) > 0) {
            while ($property = mysqli_fetch_assoc($propertyDataDetails)) {
                $propertyDataDetailsArray[] = $property;
            }
        }
        return $propertyDataDetailsArray;
    }
}
