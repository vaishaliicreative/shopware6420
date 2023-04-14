<?php declare(strict_types=1);

namespace ICTTask\Core\Content\Extension\Media;

use ICTTask\Core\Content\IctTask\IctTaskDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class MediaExtension extends EntityExtension
{

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField('media','id','media_id',IctTaskDefinition::class,false)
        );
    }

    public function getDefinitionClass(): string
    {
        return MediaDefinition::class;
    }
}
