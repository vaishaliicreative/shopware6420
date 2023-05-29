<?php

declare(strict_types=1);

namespace ICTECHMigration\Util\Lifecycle\Custom;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class InstallCustomField
{
    public const CUSTOM_FIELD_NAME = 'custom_category_has_migration';
    public const CUSTOM_FIELD_SET_NAME = 'custom_category';

    /** * @var EntityRepository */
    private EntityRepository $customFieldSetRepository;

    /** * @var EntityRepository */
    private EntityRepository $customFieldRepository;

    public function __construct(EntityRepository $customFieldSetRepository, EntityRepository $customFieldRepository)
    {
        $this->customFieldSetRepository = $customFieldSetRepository;
        $this->customFieldRepository = $customFieldRepository;
    }

    public function install(Context $context): void
    {
        $dataExist = $this->checkCustomFieldExist($context);

        if ($dataExist !== 0) {
            return;
        }

        $this->installCustomFields($context);
    }

    public function unInstall(Context $context): void
    {
        $this->deleteCustomField($context);
    }

    private function checkCustomFieldExist(Context $context): int
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_NAME));

        return $this->customFieldRepository->searchIds($criteria, $context)->getTotal();
    }

    private function installCustomFields(Context $context): void
    {
        $customField = [
            [
                'id' => \md5(self::CUSTOM_FIELD_SET_NAME),
                'name' => self::CUSTOM_FIELD_SET_NAME,
                'config' => [
                    'label' => [
                        'en-GB' => 'Custom category',
                        'de-DE' => 'Benutzerdefinierte Kategorie',
                    ],
                ],
                'customFields' => [
                    [
                        'id' => \md5(self::CUSTOM_FIELD_NAME),
                        'name' => self::CUSTOM_FIELD_NAME,
                        'type' => CustomFieldTypes::TEXT,
                        'config' => [
                            'label' => [
                                'en-GB' => 'Custom category has migration',
                                'de-DE' => 'Benutzerdefinierte Kategorie hat Migration',
                            ],
                            'customFieldPosition' => 10,
                        ],
                    ],
                    [
                        'name' => 'custom_category_id',
                        'type' => CustomFieldTypes::TEXT,
                        'config' => [
                            'label' => [
                                'en-GB' => 'Custom category id',
                                'de-DE' => 'Benutzerdefinierte Kategorie-ID',
                            ],
                            'customFieldPosition' => 11,
                        ],
                    ],
                ],
                'relations' => [
                    [
                        'entityName' => 'category',
                    ],
                ],
            ],
        ];

        $this->customFieldSetRepository->create(
            $customField,
            $context,
        );
    }

    private function deleteCustomField(Context $context): void
    {
        $this->customFieldRepository->delete([
            ['id' => \md5(self::CUSTOM_FIELD_NAME)],
        ], $context);
        $this->customFieldSetRepository->delete([
            ['id' => \md5(self::CUSTOM_FIELD_SET_NAME)],
        ], $context);
    }
}
