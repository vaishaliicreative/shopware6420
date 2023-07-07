<?php

declare(strict_types=1);

namespace ICTECHPropertyMigration\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class LanguageController
{
    private EntityRepository $languageRepository;

    public function __construct(
        EntityRepository $languageRepository
    ) {
        $this->languageRepository = $languageRepository;
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
}
