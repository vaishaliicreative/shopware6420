<?php declare(strict_types=1);

namespace ICTTask\Core\Content\IctTask;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(IctTaskEntity $entity)
 * @method void                set(string $key, IctTaskEntity $entity)
 * @method IctTaskEntity[]    getIterator()
 * @method IctTaskEntity[]    getElements()
 * @method IctTaskEntity|null get(string $key)
 * @method IctTaskEntity|null first()
 * @method IctTaskEntity|null last()
 */
 #[Package('core')]
class IctTaskCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IctTaskEntity::class;
    }
}