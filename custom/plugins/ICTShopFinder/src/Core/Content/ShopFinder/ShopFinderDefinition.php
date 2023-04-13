<?php
declare(strict_types=1);

namespace ICTShopFinder\Core\Content\ShopFinder;

use ICTShopFinder\Core\Content\ShopFinder\Aggregate\ShopFinderTranslation\ShopFinderTranslationDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\System\Country\CountryDefinition;

class ShopFinderDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ict_shop_finder';
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
         return ShopFinderCollection::class;
    }

    public function getEntityClass(): string
    {
        return ShopFinderEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        /*
         * IdField id
         * BoolField active
         * StringField name
         * StringField description
         * StringField street
         * StringField post_code
         * StringField city
         * StringField url
         * StringField telephone
         * StringField open_times
         * FkField country_id
         * ManyToOneAssociation country to CountryDefinition
         *
         * required: name street post_code city
        */
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey(),new ApiAware()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Inherited()),
            (new StringField('not_translated_field', 'notTranslatedField'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active')),
            (new TranslatedField('name'))->addFlags(new ApiAware(),new Required()),
            (new LongTextField('description', 'description')),
            (new TranslatedField('street'))->addFlags(new ApiAware(),new Required()),
            (new StringField('post_code','postCode'))->addFlags(new Required()),
            (new TranslatedField('city'))->addFlags(new ApiAware(),new Required()),
            (new StringField('url','url')),
            (new StringField('telephone','telephone')),
            (new LongTextField('open_times','openTimes')),
            (new FkField('country_id','countryId', CountryDefinition::class)),
            (new TranslationsAssociationField(
                ShopFinderTranslationDefinition::class,
                'ict_shop_finder_id'
            ))->addFlags(new ApiAware(), new Required()),
            new ManyToOneAssociationField(
                'countryId',
                'country_id',
                CountryDefinition::class,
                'id',
                false
            )

        ]);
    }
}
