<?php declare(strict_types=1);

namespace ICTBlog\Core\Content;

use ICTBlog\Core\Content\IctBlog\IctBlogDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class IctBlogProductMappingDefinition extends MappingEntityDefinition
{

    public const ENTITY_NAME = "ict_blog_product_map";
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('blog_id','blogId',IctBlogDefinition::class,'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id','productId',ProductDefinition::class,'id'))->addFlags(new PrimaryKey(),new Required()),
            (new ReferenceVersionField(ProductDefinition::class,'product_version_id'))->addFlags(new ApiAware(), new Inherited()),
            new ManyToOneAssociationField(
                'blog',
                'blog_id',
                IctBlogDefinition::class,
                'id',
                false
            ),
            new ManyToOneAssociationField(
                'product',
                'product_id',
                ProductDefinition::class,
                'id',
                false
            )

        ]);


    }
}
