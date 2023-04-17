<?php declare(strict_types=1);

namespace ICTTask\Core\Content\Extension\Language;

use ICTTask\Core\Content\IctTask\Aggregate\IctTaskTranslation\IctTaskTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

class LanguageExtension extends EntityExtension
{

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'ictTaskTranId',
                IctTaskTranslationDefinition::class,
                'ict_task_id','id')
        );
    }
    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }
}
