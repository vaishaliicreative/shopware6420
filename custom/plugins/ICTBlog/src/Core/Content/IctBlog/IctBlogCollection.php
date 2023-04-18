<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(IctBlogEntity $entity)
 * @method void                set(string $key, IctBlogEntity $entity)
 * @method IctBlogEntity[]    getIterator()
 * @method IctBlogEntity[]    getElements()
 * @method IctBlogEntity|null get(string $key)
 * @method IctBlogEntity|null first()
 * @method IctBlogEntity|null last()
 */
 #[Package('core')]
class IctBlogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IctBlogEntity::class;
    }
}