<?php declare(strict_types=1);

namespace ICTBlog\Core\Content;

use ICTBlog\Core\Content\IctBlog\Category\IctBlogCategoryDefinition;
use ICTBlog\Core\Content\IctBlog\IctBlogDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class IctBlogCategoryMappingDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = "blog_category";
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('blog_category_id','blogCategoryId',IctBlogCategoryDefinition::class))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('blog_id','blogId',IctBlogDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField(
                'blogCategory',
                'blog_category_id',
                IctBlogCategoryDefinition::class,
                'id',
                false
            ),
            new ManyToOneAssociationField(
                'blog',
                'blog_id',
                IctBlogCategoryDefinition::class,
                'id',
                false
            ),

        ]);
    }
}
