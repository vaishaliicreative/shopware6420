<?php declare(strict_types=1);

namespace ICTShopProductFinder\Core\Content\ShopProductFinder;

use ICTShopProductFinder\Core\Content\ShopProductFinder\Aggregate\ShopProductFinderTranslation\ShopProductFinderTranslationDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Country\Aggregate\CountryState\CountryStateDefinition;
use Shopware\Core\System\Country\CountryDefinition;

class ShopProductFinderDefinition extends EntityDefinition
{
    public const ENTITY_NAME = "ict_shop_product_finder";
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

//    public function getCollectionClass(): string
//    {
//        return ShopFinderCollection::class;
//    }
//
//    public function getEntityClass(): string
//    {
//        return ShopFinderEntity::class;
//    }

    protected function defineFields(): FieldCollection
    {
        /*
         * IdField id
         * BoolField active
         * StringField name
         * StringField city
         * FkField country_id
         * FkField country_state_id
         * FkField product_id
         * FkField media_id
         * ManyToOneAssociation country to CountryDefinition
         * ManyToOneAssociation state to CountryDefinition
         * ManyToManyAssociation product to ProductDefinition
         * OneToOneAssociation image to MediaDefinition
        */
        return new FieldCollection([
            (new IdField('id','id'))->addFlags(new Required(),new PrimaryKey(), new ApiAware()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Inherited()),
            (new TranslatedField('name'))->addFlags(new ApiAware(),new Required()),
            (new TranslatedField('city'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('active','active')),
            (new FkField('country_id','countryId',CountryDefinition::class)),
            (new FkField('country_state_id','countryStateId',CountryStateDefinition::class)),
            (new FkField('product_id','productId',ProductDefinition::class)),
            (new FkField('media_id','mediaId',MediaDefinition::class)),

            (new ManyToOneAssociationField(
                'country',
                'country_id',
                CountryDefinition::class,
                'id',
                false
            ))->addFlags(new Required(), new ApiAware()),
            (new ManyToOneAssociationField(
                'countryState',
                'country_state_id',
                CountryStateDefinition::class,
                'id',
                false
            ))->addFlags( new ApiAware()),
            (new ManyToOneAssociationField(
                'product',
                'product_id',
                ProductDefinition::class,
                'id',
                false
            ))->addFlags(new ApiAware()),
            (new OneToOneAssociationField(
                'media',
                'media_id',
                'id',
                MediaDefinition::class,
                false
            ))->addFlags(new ApiAware()),
            new TranslationsAssociationField(
            ShopProductFinderTranslationDefinition::class,
            'ict_shop_product_finder_id'
            )
        ]);
    }
}
