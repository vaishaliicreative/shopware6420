<?php declare(strict_types=1);

namespace ICTTask\Core\Content\IctTask\Aggregate\IctTaskTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(IctTaskTranslationEntity $entity)
 * @method void                set(string $key, IctTaskTranslationEntity $entity)
 * @method IctTaskTranslationEntity[]    getIterator()
 * @method IctTaskTranslationEntity[]    getElements()
 * @method IctTaskTranslationEntity|null get(string $key)
 * @method IctTaskTranslationEntity|null first()
 * @method IctTaskTranslationEntity|null last()
 */
 #[Package('core')]
class IctTaskTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IctTaskTranslationEntity::class;
    }
}