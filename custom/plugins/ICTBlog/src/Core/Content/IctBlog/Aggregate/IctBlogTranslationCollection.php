<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(IctBlogTranslationEntity $entity)
 * @method void                set(string $key, IctBlogTranslationEntity $entity)
 * @method IctBlogTranslationEntity[]    getIterator()
 * @method IctBlogTranslationEntity[]    getElements()
 * @method IctBlogTranslationEntity|null get(string $key)
 * @method IctBlogTranslationEntity|null first()
 * @method IctBlogTranslationEntity|null last()
 */
 #[Package('core')]
class IctBlogTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IctBlogTranslationEntity::class;
    }
}