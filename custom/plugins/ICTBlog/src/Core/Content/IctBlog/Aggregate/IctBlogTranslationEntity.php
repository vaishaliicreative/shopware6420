<?php declare(strict_types=1);

namespace ICTBlog\Core\Content\IctBlog\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use ICTBlog\Core\Content\IctBlog\IctBlogEntity;
use Shopware\Core\System\Language\LanguageEntity;

class IctBlogTranslationEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

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
    protected $ictBlogId;

    /**
     * @var string
     */
    protected $languageId;

    /**
     * @var IctBlogEntity|null
     */
    protected $ictBlog;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCreatedAt(): ?\DateTimeInterface
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

    public function getIctBlogId(): string
    {
        return $this->ictBlogId;
    }

    public function setIctBlogId(string $ictBlogId): void
    {
        $this->ictBlogId = $ictBlogId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getIctBlog(): ?IctBlogEntity
    {
        return $this->ictBlog;
    }

    public function setIctBlog(?IctBlogEntity $ictBlog): void
    {
        $this->ictBlog = $ictBlog;
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
