<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\Extension;

use ICTBlog\Core\Content\IctBlog\IctBlogDefinition;
use ICTBlog\Core\Content\IctBlogProductMappingDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new ManyToManyAssociationField(
                'blogs',
                IctBlogDefinition::class,
                IctBlogProductMappingDefinition::class,
                'product_id',
                'blog_id',
                'id',
                'id'
            )
        );
    }
}
