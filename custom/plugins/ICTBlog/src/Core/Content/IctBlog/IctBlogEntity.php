<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use ICTBlog\Core\Content\IctBlog\Category\IctBlogCategoryCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class IctBlogEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var \DateTimeInterface|null
     */
    protected $releaseDate;

    /**
     * @var bool|null
     */
    protected $active;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string|null
     */
    protected $notTranslatedField;

    /**
     * @var array|null
     */
    protected $categoryIds;

    /**
     * @var array|null
     */
    protected $productIds;

    /**
     * @var IctBlogCategoryCollection|null
     */
    protected $ictBlogCategories;

    /**
     * @var ProductCollection|null
     */
    protected $products;

    /**
     * @var EntityCollection|null
     */
    protected $translations;

    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt;

    /**
     * @var array|null
     */
    protected $translated;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): void
    {
        $this->releaseDate = $releaseDate;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getNotTranslatedField(): ?string
    {
        return $this->notTranslatedField;
    }

    public function setNotTranslatedField(?string $notTranslatedField): void
    {
        $this->notTranslatedField = $notTranslatedField;
    }

    public function getCategoryIds(): ?array
    {
        return $this->categoryIds;
    }

    public function setCategoryIds(?array $categoryIds): void
    {
        $this->categoryIds = $categoryIds;
    }

    public function getProductIds(): ?array
    {
        return $this->productIds;
    }

    public function setProductIds(?array $productIds): void
    {
        $this->productIds = $productIds;
    }

    public function getIctBlogCategories(): ?IctBlogCategoryCollection
    {
        return $this->ictBlogCategories;
    }

    public function setIctBlogCategories(IctBlogCategoryCollection $ictBlogCategories): void
    {
        $this->ictBlogCategories = $ictBlogCategories;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }

    public function getTranslations(): ?EntityCollection
    {
        return $this->translations;
    }

    public function setTranslations(EntityCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getTranslated(): array
    {
        return $this->translated;
    }

    public function setTranslated(?array $translated): void
    {
        $this->translated = $translated;
    }
}
