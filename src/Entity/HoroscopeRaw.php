<?php

namespace App\Entity;

use App\Repository\HoroscopeRawRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=HoroscopeRawRepository::class)
 */
class HoroscopeRaw
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"horoscopeList", "bundled"})
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull()
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=AstrologicalSign::class)
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $astrologicalSign;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"horoscopeList", "bundled"})
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=2)
     *
     */
    private $locale;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAstrologicalSign(): ?AstrologicalSign
    {
        return $this->astrologicalSign;
    }

    public function setAstrologicalSign(?AstrologicalSign $astrologicalSign): self
    {
        $this->astrologicalSign = $astrologicalSign;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
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
}
