<?php declare(strict_types=1);

namespace ICTECHMigration;

use ICTECHMigration\Util\Lifecycle\Custom\InstallCustomField;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class ICTECHMigration extends Plugin
{
    public const CUSTOM_FIELD_SET_NAME_FOR_PRODUCT = 'custom_product';
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        /** @var EntityRepository $customFieldRepository */
        $customFieldRepository = $this->container->get('custom_field.repository');

        (new InstallCustomField($customFieldSetRepository, $customFieldRepository))->install($installContext->getContext());

        $this->addCustomFieldsForProduct($installContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        /** @var EntityRepository $customFieldRepository */
        $customFieldRepository = $this->container->get('custom_field.repository');

        (new InstallCustomField($customFieldSetRepository, $customFieldRepository))->unInstall($uninstallContext->getContext());

        $this->deleteCustomFieldsForProduct($uninstallContext);
    }

    public function addCustomFieldsForProduct(InstallContext $installContext)
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $customFieldSetRepository->create([[
            'name' => self::CUSTOM_FIELD_SET_NAME_FOR_PRODUCT,
            'config' => [
                'label' => [
                    'de-DE' => 'Custom Fields For Product',
                    'en-GB' => 'Custom Fields For Product',
                ]
            ],
            'customFields' => [
                [
                    'name' => 'custom_product_video_url',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Video (URL)',
                            'en-GB' => 'Video (URL)',
                        ],
                        'customFieldPosition' => 1,
                    ],
                ],
                [
                    'name' => 'custom_product_audio',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Audio (URL)',
                            'en-GB' => 'Audio (URL)',
                        ],
                        'customFieldPosition' => 2,
                    ],
                ],
                [
                    'name' => 'custom_product_www',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'WWW',
                            'en-GB' => 'WWW',
                        ],
                        'customFieldPosition' => 3,
                    ],
                ],
                [
                    'name' => 'custom_product_advantage',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'label' => [
                            'de-DE' => 'Product Advantage',
                            'en-GB' => 'Product Advantage',
                        ],
                        'customFieldPosition' => 4,
                    ],
                ],
                [
                    'name' => 'custom_product_protection_area',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Protection Area',
                            'en-GB' => 'Product Protection Area',
                        ],
                        'customFieldPosition' => 5,
                    ],
                ],
                [
                    'name' => 'custom_product_target_group',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Target Group',
                            'en-GB' => 'Product Target Group',
                        ],
                        'customFieldPosition' => 6,
                    ],
                ],
                [
                    'name' => 'custom_product_intended_use',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Intended Use',
                            'en-GB' => 'Product Intended Use',
                        ],
                        'customFieldPosition' => 7,
                    ],
                ],
                [
                    'name' => 'custom_product_birds',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Birds',
                            'en-GB' => 'Product Birds',
                        ],
                        'customFieldPosition' => 8,
                    ],
                ],
                [
                    'name' => 'custom_product_guarantee',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Guarantee',
                            'en-GB' => 'Product Guarantee',
                        ],
                        'customFieldPosition' => 9,
                    ],
                ],
                [
                    'name' => 'custom_product_operating_manual',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'label' => [
                            'de-DE' => 'Product Operating manual',
                            'en-GB' => 'Product Operating manual',
                        ],
                        'customFieldPosition' => 10,
                    ],
                ],
                [
                    'name' => 'custom_product_warnings',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Warnings',
                            'en-GB' => 'Product Warnings',
                        ],
                        'customFieldPosition' => 11,
                    ],
                ],
                [
                    'name' => 'custom_product_included',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Included',
                            'en-GB' => 'Product Included',
                        ],
                        'customFieldPosition' => 12,
                    ],
                ],
                [
                    'name' => 'custom_product_not_included',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'label' => [
                            'de-DE' => 'Product Not Included',
                            'en-GB' => 'Product Not Included',
                        ],
                        'customFieldPosition' => 13,
                    ],
                ],
                [
                    'name' => 'custom_product_summary',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'label' => [
                            'de-DE' => 'Product Summary',
                            'en-GB' => 'Product Summary',
                        ],
                        'customFieldPosition' => 14,
                    ],
                ],
                [
                    'name' => 'custom_product_link_to_info',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product link to info',
                            'en-GB' => 'Product link to info',
                        ],
                        'customFieldPosition' => 15,
                    ],
                ],
                [
                    'name' => 'custom_product_link_to_info_text',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'label' => [
                            'de-DE' => 'Product link to info text',
                            'en-GB' => 'Product link to info text',
                        ],
                        'customFieldPosition' => 16,
                    ],
                ],
                [
                    'name' => 'custom_product_pdf',
                    'type' => CustomFieldTypes::MEDIA,
                    'config' => [
                        'componentName' => 'sw-media-field',
                        'label' => [
                            'de-DE' => 'Product pdf',
                            'en-GB' => 'Product pdf',
                        ],
                        'customFieldPosition' => 17,
                    ],
                ],
                [
                    'name' => 'custom_product_pdf1',
                    'type' => CustomFieldTypes::MEDIA,
                    'config' => [
                        'componentName' => 'sw-media-field',
                        'label' => [
                            'de-DE' => 'Product pdf1',
                            'en-GB' => 'Product pdf1',
                        ],
                        'customFieldPosition' => 18,
                    ],
                ],
                [
                    'name' => 'custom_product_video',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Video',
                            'en-GB' => 'Product Video',
                        ],
                        'customFieldPosition' => 19,
                    ],
                ],
                [
                    'name' => 'custom_product_video_img',
                    'type' => CustomFieldTypes::MEDIA,
                    'config' => [
                        'componentName' => 'sw-media-field',
                        'label' => [
                            'de-DE' => 'Product Video Image',
                            'en-GB' => 'Product Video Image',
                        ],
                        'customFieldPosition' => 20,
                    ],
                ],
                [
                    'name' => 'custom_product_video_text',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'label' => [
                            'de-DE' => 'Product Video Text',
                            'en-GB' => 'Product Video Text',
                        ],
                        'customFieldPosition' => 21,
                    ],
                ],
                [
                    'name' => 'custom_product_video2',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Video2',
                            'en-GB' => 'Product Video2',
                        ],
                        'customFieldPosition' => 22,
                    ],
                ],
                [
                    'name' => 'custom_product_video2_img',
                    'type' => CustomFieldTypes::MEDIA,
                    'config' => [
                        'componentName' => 'sw-media-field',
                        'label' => [
                            'de-DE' => 'Product Video Image',
                            'en-GB' => 'Product Video Image',
                        ],
                        'customFieldPosition' => 23,
                    ],
                ],
                [
                    'name' => 'custom_product_video2_text',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'label' => [
                            'de-DE' => 'Product Video Text',
                            'en-GB' => 'Product Video Text',
                        ],
                        'customFieldPosition' => 24,
                    ],
                ],
                [
                    'name' => 'custom_product_video3',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Video2',
                            'en-GB' => 'Product Video2',
                        ],
                        'customFieldPosition' => 25,
                    ],
                ],
                [
                    'name' => 'custom_product_video3_img',
                    'type' => CustomFieldTypes::MEDIA,
                    'config' => [
                        'componentName' => 'sw-media-field',
                        'label' => [
                            'de-DE' => 'Product Video Image',
                            'en-GB' => 'Product Video Image',
                        ],
                        'customFieldPosition' => 26,
                    ],
                ],
                [
                    'name' => 'custom_product_video3_text',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',
                        'label' => [
                            'de-DE' => 'Product Video Text',
                            'en-GB' => 'Product Video Text',
                        ],
                        'customFieldPosition' => 27,
                    ],
                ],
                [
                    'name' => 'custom_product_producer_link',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Producer Link',
                            'en-GB' => 'Product Producer Link',
                        ],
                        'customFieldPosition' => 28,
                    ],
                ],
                [
                    'name' => 'custom_product_gallery_id',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product Gallery id',
                            'en-GB' => 'Product Gallery id',
                        ],
                        'customFieldPosition' => 29,
                    ],
                ],
                [
                    'name' => 'custom_product_in_header',
                    'type' => CustomFieldTypes::SWITCH,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product In header',
                            'en-GB' => 'Product In header',
                        ],
                        'customFieldPosition' => 30,
                    ],
                ],
                [
                    'name' => 'custom_product_in_promotion',
                    'type' => CustomFieldTypes::SWITCH,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product In promotion',
                            'en-GB' => 'Product In promotion',
                        ],
                        'customFieldPosition' => 31,
                    ],
                ],
                [
                    'name' => 'custom_product_against_animals',
                    'type' => CustomFieldTypes::SWITCH,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product against animals',
                            'en-GB' => 'Product against animals',
                        ],
                        'customFieldPosition' => 32,
                    ],
                ],
                [
                    'name' => 'custom_product_against_birds',
                    'type' => CustomFieldTypes::SWITCH,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product against birds',
                            'en-GB' => 'Product against birds',
                        ],
                        'customFieldPosition' => 33,
                    ],
                ],
                [
                    'name' => 'custom_product_soundlevel_calculator',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product sound level calculator',
                            'en-GB' => 'Product sound level calculator',
                        ],
                        'customFieldPosition' => 34,
                    ],
                ],
                [
                    'name' => 'custom_product_faq_anker',
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'de-DE' => 'Product faq anker',
                            'en-GB' => 'Product faq anker',
                        ],
                        'customFieldPosition' => 35,
                    ],
                ],
            ],
            'relations' => [
                ['entityName' => 'product']
            ]
        ]], $installContext->getContext());
    }

    public function deleteCustomFieldsForProduct(UninstallContext $uninstallContext)
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', self::CUSTOM_FIELD_SET_NAME_FOR_PRODUCT));
        $result = $customFieldSetRepository->searchIds($criteria, $uninstallContext->getContext());

        if ($result->getTotal() > 0 ) {
            $data = $result->getDataOfId($result->firstId());
            $customFieldSetRepository->delete([$data], $uninstallContext->getContext());
        }
    }
}
