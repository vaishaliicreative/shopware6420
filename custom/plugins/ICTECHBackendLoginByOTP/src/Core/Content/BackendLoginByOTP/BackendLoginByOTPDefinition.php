<?php declare(strict_types=1);

namespace ICTECHBackendLoginByOTP\Core\Content\BackendLoginByOTP;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\User\UserDefinition;

class BackendLoginByOTPDefinition extends EntityDefinition
{

    public const ENTITY_NAME = 'backend_login_by_otp';

    /**
     * @return string
     */
    public function getEntityName(): string
    {

        return self::ENTITY_NAME;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return BackendLoginByOtpEntity::class;
    }

    /**
     * @return string
     */
    public function getCollectionClass(): string
    {
        return BackendLoginByOtpCollection::class;
    }

    /**
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id','id'))->addFlags(new Required(),new PrimaryKey()),
            (new FkField('user_id','userId',UserDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new StringField('otp','otp'))->addFlags(new ApiAware()),
            new ManyToOneAssociationField(
                'user',
                'user_id',
                UserDefinition::class,
                'id',
                false
            )
        ]);
    }
}
