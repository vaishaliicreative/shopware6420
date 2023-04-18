<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog\Aggregate\Category;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use ICTBlog\Core\Content\IctBlog\Category\IctBlogCategoryEntity;
use Shopware\Core\System\Language\LanguageEntity;

class IctBlogCategoryTranslationEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt;

    /**
     * @var string
     */
    protected $ictBlogCategoryId;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var IctBlogCategoryEntity|null
     */
    protected $ictBlogCategory;

    /**
     * @var LanguageEntity|null
     */
    protected $language;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
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

    public function getIctBlogCategoryId(): string
    {
        return $this->ictBlogCategoryId;
    }

    public function setIctBlogCategoryId(string $ictBlogCategoryId): void
    {
        $this->ictBlogCategoryId = $ictBlogCategoryId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getIctBlogCategory(): ?IctBlogCategoryEntity
    {
        return $this->ictBlogCategory;
    }

    public function setIctBlogCategory(?IctBlogCategoryEntity $ictBlogCategory): void
    {
        $this->ictBlogCategory = $ictBlogCategory;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): void
    {
        $this->language = $language;
    }
}
