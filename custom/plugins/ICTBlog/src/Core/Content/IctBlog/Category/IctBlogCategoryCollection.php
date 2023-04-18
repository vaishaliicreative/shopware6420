<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog\Category;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(IctBlogCategoryEntity $entity)
 * @method void                set(string $key, IctBlogCategoryEntity $entity)
 * @method IctBlogCategoryEntity[]    getIterator()
 * @method IctBlogCategoryEntity[]    getElements()
 * @method IctBlogCategoryEntity|null get(string $key)
 * @method IctBlogCategoryEntity|null first()
 * @method IctBlogCategoryEntity|null last()
 */
 #[Package('core')]
class IctBlogCategoryCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IctBlogCategoryEntity::class;
    }
}