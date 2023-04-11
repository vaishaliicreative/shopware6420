<?php declare(strict_types=1);

namespace ICTShopFinder\Core\Content\Aggregate\ShopFinderTranslation;


use ICTShopFinder\Core\Content\ShopFinder\ShopFinderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;

class ShopFinderTranslationDefinition extends EntityTranslationDefinition
{

    public const ENTITY_NAME = 'ict_shop_finder_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ShopFinderTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return ShopFinderDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new StringField('name','name'))->addFlags(new Required()),
                (new StringField('street','street'))->addFlags(new Required()),
                (new StringField('city','city')),

            ]
        );
    }
}
