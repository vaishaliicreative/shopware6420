<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\Extension;

use ICTBlog\Core\Content\IctBlog\Aggregate\Category\IctBlogCategoryTranslationDefinition;
use ICTBlog\Core\Content\IctBlog\Aggregate\IctBlogTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

class LanguageExtension extends EntityExtension
{

    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'ictBlogTranId',
                IctBlogTranslationDefinition::class,
                'ict_blog_id',
                'id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                'ictBlogCatTranId',
                IctBlogCategoryTranslationDefinition::class,
                'ict_blog_category_id',
                'id'
            )
        );
    }
}
