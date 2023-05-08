<?php declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\Core\Content\Extension;

use ICTECHBackendLoginByOTP\Core\Content\BackendLoginByOTP\BackendLoginByOTPDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\User\UserDefinition;

class UserExtension extends EntityExtension
{

    /**
     * @return string
     */
    public function getDefinitionClass(): string
    {
        return UserDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'userIds',
                BackendLoginByOTPDefinition::class,
                'user_id',
                'id'
            )
        );
    }
}
