<?php declare(strict_types=1);

namespace ICTTask\Core\Content\IctTask\Aggregate\IctTaskTranslation;

use ICTTask\Core\Content\IctTask\IctTaskDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class IctTaskTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'ict_task_translation';
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return IctTaskTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return IctTaskDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name','name'))->addFlags(new Required()),
            (new StringField('city','city'))->addFlags(new Required())
        ]);
    }
}
