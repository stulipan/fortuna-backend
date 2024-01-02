<?php

namespace App\Entity;

use App\Repository\HoroscopeTextRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=HoroscopeTextRepository::class)
 */
class HoroscopeText
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"horoscopeText", "horoscopeTextPublished"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2)
     * @Assert\NotNull()
     *
     * @Groups({"horoscopeText"})
     */
    private $locale;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull()
     *
     * @Groups({"horoscopeText", "horoscopeTextPublished"})
     */
    private $base;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"horoscopeText", "horoscopeTextPublished"})
     */
    private $addendum;

    /**
     * @ORM\OneToMany(targetEntity=HoroscopeTextPublished::class, mappedBy="horoscopeText", cascade={"remove"})
     * @ORM\OrderBy({"publishDate" = "DESC"})
     *
     * @Groups({"horoscopeText"})
     */
    private $horoscopeTextsPublished;


    /**
     * @var ArrayCollection|null   // @ var Collection<int, Tag>|null
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="horoscopeTexts")
     *
     * @Groups({"horoscopeText"})
     *
     */
    private $tags;

    public function __construct()
    {
        $this->horoscopeTextsPublished = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getBase(): ?string
    {
        return $this->base;
    }

    public function setBase(string $base): self
    {
        $this->base = $base;

        return $this;
    }

    public function getAddendum(): ?string
    {
        return $this->addendum;
    }

    public function setAddendum(?string $addendum): self
    {
        $this->addendum = $addendum;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addHoroscopeText($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeHoroscopeText($this); // This ensures the relationship is bidirectional.
        }

        return $this;
    }

    /**
     * @return Collection<int, HoroscopeTextPublished>
     */
    public function getHoroscopeTextsPublished(): Collection
    {
        return $this->horoscopeTextsPublished;
    }

    public function addHoroscopeTextPublished(HoroscopeTextPublished $horoscopeTextsPublished): self
    {
        if (!$this->horoscopeTextsPublished->contains($horoscopeTextsPublished)) {
            $this->horoscopeTextsPublished[] = $horoscopeTextsPublished;
            $horoscopeTextsPublished->setHoroscopeText($this);
        }

        return $this;
    }

    public function removeHoroscopeTextPublished(HoroscopeTextPublished $horoscopeTextsPublished): self
    {
        if ($this->horoscopeTextsPublished->removeElement($horoscopeTextsPublished)) {
            // set the owning side to null (unless already changed)
            if ($horoscopeTextsPublished->getHoroscopeText() === $this) {
                $horoscopeTextsPublished->setHoroscopeText(null);
            }
        }

        return $this;
    }
}
