<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog;

use ICTBlog\Core\Content\IctBlog\Aggregate\IctBlogTranslationDefinition;
use ICTBlog\Core\Content\IctBlog\Category\IctBlogCategoryDefinition;
use ICTBlog\Core\Content\IctBlogCategoryMappingDefinition;
use ICTBlog\Core\Content\IctBlogProductMappingDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class IctBlogDefinition extends EntityDefinition
{
    public const ENTITY_NAME = "ict_blog";


    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return IctBlogCollection::class;
    }

    public function getEntityClass(): string
    {
        return IctBlogEntity::class;
    }

    /**
     * IdField id
     * StringField name
     * StringField description
     * DateField release_date
     * BoolField active
     * StringField author
     * ManyToManyAssociation categories to BlogCategoryMappingDefinition
     * ManyToManyAssociation products to BlogProductMappingDefinition
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id','id'))->addFlags(new Required(),new PrimaryKey(),new ApiAware()),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new Required()),
            (new TranslatedField('description'))->addFlags(new ApiAware()),
            (new DateField('release_date','releaseDate'))->addFlags(new ApiAware()),
            (new BoolField('active','active')),
            (new StringField('author','author'))->addFlags(new Required(), new ApiAware()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Inherited()),
            (new ManyToManyAssociationField(
                'ictBlogCategories',
                IctBlogCategoryDefinition::class,
                IctBlogCategoryMappingDefinition::class,
                'blog_id',
                'blog_category_id',
            )),
            (new ManyToManyAssociationField(
                'products',
                ProductDefinition::class,
                IctBlogProductMappingDefinition::class,
                'blog_id',
                'product_id'
            )),
            (new TranslationsAssociationField(
                IctBlogTranslationDefinition::class,
                'ict_blog_id',
                'translations',
                'id'
            ))
        ]);
    }
}
