<?php

declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\Core\Content\BackendLoginByOTP;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @method void                add(BackendLoginByOtpEntity $entity)
 * @method void                set(string $key, BackendLoginByOtpEntity $entity)
 * @method BackendLoginByOtpEntity[]    getIterator()
 * @method BackendLoginByOtpEntity[]    getElements()
 * @method BackendLoginByOtpEntity|null get(string $key)
 * @method BackendLoginByOtpEntity|null first()
 * @method BackendLoginByOtpEntity|null last()
 */
#[Package('core')]
class BackendLoginByOtpCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return BackendLoginByOtpEntity::class;
    }
}
