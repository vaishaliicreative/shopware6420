<?php

declare(strict_types=1);

namespace ICTECHPropertyMigration;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class ICTECHPropertyMigration extends Plugin
{
    public const CUSTOM_FIELD_SET_NAME_FOR_PROPERTY = 'custom_property_group';

    public const CUSTOM_FIELD_SET_NAME_FOR_OPTION = 'custom_property_group_option';

    public const CUSTOM_FIELD_SET_NAME_FOR_PRODUCT = 'custom_product';
    private array $customPropertyGroupFieldArray = [
        'name' => self::CUSTOM_FIELD_SET_NAME_FOR_PROPERTY,
        'config' => [
            'label' => [
                'de-DE' => 'Benutzerdefinierte Felder für Eigenschaftsgruppe',
                'en-GB' => 'Custom Fields For Property Group',
                'de-CH' => 'Benutzerdefinierte Felder für Eigenschaftsgruppe',
            ],
        ],
        'customFields' => [
            [
                'name' => 'custom_property_group_id',
                'type' => CustomFieldTypes::TEXT,
                'config' => [
                    'label' => [
                        'de-DE' => 'Eigenschaftsgruppen-ID',
                        'de-CH' => 'Eigenschaftsgruppen-ID',
                        'en-GB' => 'Property Group ID',
                    ],
                    'disabled' => 'disabled',
                    'customFieldPosition' => 1,
                ],
            ],
        ],
        'relations' => [
            [
                'entityName' => 'property_group',
            ],
        ],
    ];

    private array $customPropertyOptionFieldArray = [
        'name' => self::CUSTOM_FIELD_SET_NAME_FOR_OPTION,
        'config' => [
            'label' => [
                'de-DE' => 'Benutzerdefinierte Felder für Eigenschaftsoption',
                'de-CH' => 'Benutzerdefinierte Felder für Eigenschaftsoption',
                'en-GB' => 'Custom Fields For Property Option',
            ],
        ],
        'customFields' => [
            [
                'name' => 'custom_property_group_option_id',
                'type' => CustomFieldTypes::TEXT,
                'config' => [
                    'label' => [
                        'de-DE' => 'Eigenschaftsoptions-ID',
                        'de-CH' => 'Eigenschaftsoptions-ID',
                        'en-GB' => 'Property Option ID',
                    ],
                    'disabled' => 'disabled',
                    'customFieldPosition' => 1,
                ],
            ],
        ],
        'relations' => [
            [
                'entityName' => 'property_group_option',
            ],
        ],
    ];

    private array $customProductFieldArray = [
        'name' => self::CUSTOM_FIELD_SET_NAME_FOR_PRODUCT,
        'config' => [
            'label' => [
                'de-DE' => 'Benutzerdefinierte Felder für Produkt',
                'de-CH' => 'Benutzerdefinierte Felder für Produkt',
                'en-GB' => 'Custom Fields For Product',
            ],
        ],
        'customFields' => [
            [
                'name' => 'custom_product_id',
                'type' => CustomFieldTypes::TEXT,
                'config' => [
                    'label' => [
                        'de-DE' => 'Produkt-ID',
                        'de-CH' => 'Produkt-ID',
                        'en-GB' => 'Product Id',
                    ],
                    'disabled' => 'disabled',
                    'customFieldPosition' => 1,
                ],
            ],
        ],
        'relations' => [
            [
                'entityName' => 'product',
            ],
        ],
    ];

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        $dataExist = $this->checkCustomFieldExist(
            $installContext,
            self::CUSTOM_FIELD_SET_NAME_FOR_OPTION
        );

        if ($dataExist !== 0) {
            return;
        }
        $this->addCustomFields(
            $installContext,
            $this->customPropertyGroupFieldArray
        );

        $customOptionData = $this->checkCustomFieldExist(
            $installContext,
            self::CUSTOM_FIELD_SET_NAME_FOR_OPTION
        );

        if ($customOptionData !== 0) {
            return;
        }
        $this->addCustomFields(
            $installContext,
            $this->customPropertyOptionFieldArray
        );

        $customProductData = $this->checkCustomFieldExist(
            $installContext,
            self::CUSTOM_FIELD_SET_NAME_FOR_PRODUCT
        );

        if ($customProductData !== 0) {
            return;
        }
        $this->addCustomFields(
            $installContext,
            $this->customProductFieldArray
        );
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        if ($uninstallContext->keepUserData()) {
            return;
        }
        $this->deleteCustomFields(
            $uninstallContext,
            self::CUSTOM_FIELD_SET_NAME_FOR_PROPERTY
        );
        $this->deleteCustomFields(
            $uninstallContext,
            self::CUSTOM_FIELD_SET_NAME_FOR_OPTION
        );
        $this->deleteCustomFields(
            $uninstallContext,
            self::CUSTOM_FIELD_SET_NAME_FOR_PRODUCT
        );
    }
    public function addCustomFields(
        InstallContext $installContext,
        array $customFieldArray
    ): void {
        $customFieldSetRepository = $this->container
            ->get('custom_field_set.repository');
        $customFieldSetRepository->create(
            [$customFieldArray],
            $installContext->getContext()
        );
    }

    public function deleteCustomFields(
        UninstallContext $uninstallContext,
        string $name
    ): void {
        $customFieldSetRepository = $this->container
            ->get('custom_field_set.repository');
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'name',
                $name
            )
        );
        $result = $customFieldSetRepository->searchIds(
            $criteria,
            $uninstallContext->getContext()
        );

        if ($result->getTotal() > 0) {
            $data = $result->getDataOfId($result->firstId());
            $customFieldSetRepository->delete(
                [$data],
                $uninstallContext->getContext()
            );
        }
    }

    private function checkCustomFieldExist(
        InstallContext $installContext,
        string $name
    ): int {
        $criteria = new Criteria();
        $criteria->addFilter(
            new ContainsFilter(
                'name',
                $name
            )
        );
        $customFieldRepository = $this->container
            ->get('custom_field.repository');
        return $customFieldRepository->searchIds(
            $criteria,
            $installContext->getContext()
        )->getTotal();
    }
}
