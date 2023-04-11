<?php declare(strict_types=1);

namespace ICTShopFinder\Core\Content\Aggregate\ShopFinderTranslation;

use ICTShopFinder\Core\Content\ShopFinder\ShopFinderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class ShopFinderTranslationEntity extends TranslationEntity
{
    /**
     * @var string|null
     */
    protected ?string $shopFinderId;

    /**
     * @var string|null
     */
    protected ?string $name;

    /**
     * @var string|null
     */
    protected ?string $street;

    /**
     * @var string|null
     */
    protected ?string $city;

    /**
     * @var ShopFinderEntity
     */
    protected ShopFinderEntity $shopFinderEntity;

    /**
     * @return string
     */
    public function getShopFinderId(): string
    {
        return $this->shopFinderId;
    }

    /**
     * @param string $shopFinderId
     */
    public function setShopFinderId(?string $shopFinderId): void
    {
        $this->shopFinderId = $shopFinderId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ShopFinderEntity
     */
    public function getShopFinderEntity(): ShopFinderEntity
    {
        return $this->shopFinderEntity;
    }

    /**
     * @param ShopFinderEntity $shopFinderEntity
     */
    public function setShopFinderEntity(ShopFinderEntity $shopFinderEntity): void
    {
        $this->shopFinderEntity = $shopFinderEntity;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     */
    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

}
