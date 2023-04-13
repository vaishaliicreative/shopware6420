<?php declare(strict_types=1);

namespace ICTShopProductFinder\Core\Content\ShopProductFinder\Aggregate\ShopProductFinderTranslation;

use ICTShopProductFinder\Core\Content\ShopProductFinder\ShopProductFinderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ShopProductFinderTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'ict_shop_product_finder_translation';
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getParentDefinitionClass(): string
    {
        return ShopProductFinderDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name','name'))->addFlags(new Required()),
            (new StringField('city','city'))->addFlags(new Required())
        ]);
    }
}
