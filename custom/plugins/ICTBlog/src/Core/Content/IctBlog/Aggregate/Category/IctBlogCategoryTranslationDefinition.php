<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog\Aggregate\Category;

use ICTBlog\Core\Content\IctBlog\Category\IctBlogCategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class IctBlogCategoryTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'ict_blog_category_translation';
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return IctBlogCategoryTranslationEntity::class;
    }
    public function getParentDefinitionClass(): string
    {
        return IctBlogCategoryDefinition::class;
    }

    /**
     * StringField name
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name','name'))
        ]);
    }
}
