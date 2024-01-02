<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 * @UniqueEntity("name", message="Ilyen címke 'name' már létezik!")
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"tags", "horoscopeText"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"tags", "horoscopeText"})
     */
    private $name;

    /**
     * @var ArrayCollection|null
     * @ORM\ManyToMany(targetEntity=HoroscopeText::class, mappedBy="tags")
     */
    private $horoscopeTexts;

    public function __construct()
    {
        $this->horoscopeTexts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, HoroscopeText>
     */
    public function getHoroscopeTexts(): Collection
    {
        return $this->horoscopeTexts;
    }

    public function addHoroscopeText(HoroscopeText $horoscopeText): self
    {
        if (!$this->horoscopeTexts->contains($horoscopeText)) {
            $this->horoscopeTexts[] = $horoscopeText;
            $horoscopeText->addTag($this);
        }

        return $this;
    }

    public function removeHoroscopeText(HoroscopeText $horoscopeText): self
    {
        if ($this->horoscopeTexts->removeElement($horoscopeText)) {
            $horoscopeText->removeTag($this);
        }

        return $this;
    }
}
