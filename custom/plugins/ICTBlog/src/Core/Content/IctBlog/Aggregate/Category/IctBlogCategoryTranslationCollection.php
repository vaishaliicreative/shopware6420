<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog\Aggregate\Category;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(IctBlogCategoryTranslationEntity $entity)
 * @method void                set(string $key, IctBlogCategoryTranslationEntity $entity)
 * @method IctBlogCategoryTranslationEntity[]    getIterator()
 * @method IctBlogCategoryTranslationEntity[]    getElements()
 * @method IctBlogCategoryTranslationEntity|null get(string $key)
 * @method IctBlogCategoryTranslationEntity|null first()
 * @method IctBlogCategoryTranslationEntity|null last()
 */
 #[Package('core')]
class IctBlogCategoryTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IctBlogCategoryTranslationEntity::class;
    }
}