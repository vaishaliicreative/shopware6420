<?php declare(strict_types=1);

namespace ICTShopFinder\Core\Content\ShopFinder\Aggregate\ShopFinderTranslation;

use ICTShopFinder\Core\Content\ShopFinder\ShopFinderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class ShopFinderTranslationEntity extends TranslationEntity
{
    /**
     * @var string|null
     */
    protected ?string $ictShopFinderId;

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
    protected ShopFinderEntity $ictShopFinder;

    /**
     * @return string
     */
    public function getIctShopFinderId(): string
    {
        return $this->ictShopFinderId;
    }

    /**
     * @param string|null $ictShopFinderId
     */
    public function setIctShopFinderId(?string $ictShopFinderId): void
    {
        $this->ictShopFinderId = $ictShopFinderId;
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

    /**
     * @return ShopFinderEntity
     */
    public function getIctShopFinder(): ShopFinderEntity
    {
        return $this->ictShopFinder;
    }

    /**
     * @param ShopFinderEntity $ictShopFinder
     */
    public function setIctShopFinder(ShopFinderEntity $ictShopFinder): void
    {
        $this->ictShopFinder = $ictShopFinder;
    }
}
