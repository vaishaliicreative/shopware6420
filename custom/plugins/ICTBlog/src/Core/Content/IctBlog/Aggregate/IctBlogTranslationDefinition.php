<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog\Aggregate;

use ICTBlog\Core\Content\IctBlog\IctBlogDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class IctBlogTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = "ict_blog_translation";
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getParentDefinitionClass(): string
    {
        return IctBlogDefinition::class;
    }

    public function getEntityClass(): string
    {
        return IctBlogTranslationEntity::class;
    }

    /**
     * StringField name
     * LongTextField description
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name','name'))->addFlags(new Required()),
            (new LongTextField('description','description')),
        ]);
    }
}
