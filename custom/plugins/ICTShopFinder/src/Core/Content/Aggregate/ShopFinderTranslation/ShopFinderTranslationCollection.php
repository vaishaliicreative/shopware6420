<?php declare(strict_types=1);

namespace ICTShopFinder\Core\Content\Aggregate\ShopFinderTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void               add(ShopFinderTranslationEntity $entity)
 * @method void               set(string $key, ShopFinderTranslationEntity $entity)
 * @method ShopFinderTranslationEntity[]    getIterator()
 * @method ShopFinderTranslationEntity[]    getElements()
 * @method ShopFinderTranslationEntity|null get(string $key)
 * @method ShopFinderTranslationEntity|null first()
 * @method ShopFinderTranslationEntity|null last()
 */
class ShopFinderTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ShopFinderTranslationEntity::class;
    }
}
