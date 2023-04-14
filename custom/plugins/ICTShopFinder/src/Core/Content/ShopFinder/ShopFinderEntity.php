<?php
declare(strict_types=1);
namespace ICTShopFinder\Core\Content\ShopFinder;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Country\CountryEntity;

class ShopFinderEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var bool
     */
    protected bool $active;

    /**
     * @var string|null
     */
    protected ?string $name;

    /**
     * @var string|null
     */
    protected ?string $description;

    /**
     * @var string|null
     */
    protected ?string $street;

    /**
     * @var string|null
     */
    protected ?string $postCode;

    /**
     * @var string|null
     */
    protected ?string $city;

    /**
     * @var string|null
     */
    protected ?string $url;

    /**
     * @var string|null
     */
    protected ?string $telephone;

    /**
     * @var string|null
     */
    protected ?string $openTimes;

    /**
     * @var CountryEntity | null
     */
    protected CountryEntity $countryId;

    /**
     * @var string | null
     */
    protected string  $notTranslatedField;

    /**
     * @var string | null
     */
    protected string $translations;

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
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
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
    public function getPostCode(): ?string
    {
        return $this->postCode;
    }

    /**
     * @param string|null $postCode
     */
    public function setPostCode(?string $postCode): void
    {
        $this->postCode = $postCode;
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
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string|null
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * @param string|null $telephone
     */
    public function setTelephone(?string $telephone): void
    {
        $this->telephone = $telephone;
    }

    /**
     * @return string|null
     */
    public function getOpenTimes(): ?string
    {
        return $this->openTimes;
    }

    /**
     * @param string|null $openTimes
     */
    public function setOpenTimes(?string $openTimes): void
    {
        $this->openTimes = $openTimes;
    }

    /**
     * @return CountryEntity
     */
    public function getCountryId(): CountryEntity
    {
        return $this->countryId;
    }

    /**
     * @param CountryEntity $countryId
     */
    public function setCountryId(CountryEntity $countryId): void
    {
        $this->countryId = $countryId;
    }

    /**
     * @return string
     */
    public function getNotTranslatedField(): ?string
    {
        return $this->notTranslatedField;
    }

    /**
     * @param string|null $notTranslatedField
     */
    public function setNotTranslatedField(?string $notTranslatedField): void
    {
        $this->notTranslatedField = $notTranslatedField;
    }

    /**
     * @return string
     */
    public function getTranslations(): ?string
    {
        return $this->translations;
    }

    /**
     * @param string $translations
     */
    public function setTranslations(string $translations): void
    {
        $this->translations = $translations;
    }


}
