<?php

namespace App\Entity;

use App\Repository\AstrologicalSignRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AstrologicalSignRepository::class)
 */
class AstrologicalSign
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"horoscopeList", "bundled", "horoscopeText", "astrologicalSign", "horoscopeTextPublished"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @Groups({"horoscopeList", "bundled", "horoscopeText", "astrologicalSign", "horoscopeTextPublished"})
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     *
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     *
     */
    private $endDate;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @Groups({"horoscopeList", "bundled", "horoscopeText", "astrologicalSign", "horoscopeTextPublished"})
     */
    private $slug;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
