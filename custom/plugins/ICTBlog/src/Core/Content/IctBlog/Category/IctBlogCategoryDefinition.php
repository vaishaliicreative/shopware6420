<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog\Category;

use ICTBlog\Core\Content\IctBlog\Aggregate\Category\IctBlogCategoryTranslationDefinition;
use ICTBlog\Core\Content\IctBlog\IctBlogDefinition;
use ICTBlog\Core\Content\IctBlogCategoryMappingDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class IctBlogCategoryDefinition extends EntityDefinition
{
    public const ENTITY_NAME = "ict_blog_category";

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return IctBlogCategoryCollection::class;
    }

    public function getEntityClass(): string
    {
        return IctBlogCategoryEntity::class;
    }

    /**
     * IdField id
     * StringField name
     * ManyToManyAssociation blogs to BlogCategoryMappingDefinition
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id','id'))->addFlags(new Required(),new PrimaryKey(), new ApiAware()),
            (new TranslatedField('name'))->addFlags(new ApiAware()),
            (new StringField('not_translated_field', 'notTranslatedField'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(
                IctBlogCategoryTranslationDefinition::class,
                'ict_blog_category_id',
            )),
            new ManyToManyAssociationField(
                'ictBlogs',
                IctBlogDefinition::class,
                IctBlogCategoryMappingDefinition::class,
                'blog_category_id',
                'blog_id',
                'id',
                'id'
            )

        ]);
    }
}
