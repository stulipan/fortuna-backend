<?php

namespace App\Entity;

use App\Repository\HoroscopeTextPublishedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=HoroscopeTextPublishedRepository::class)
 */
class HoroscopeTextPublished
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
     * @ORM\ManyToOne(targetEntity=HoroscopeText::class, inversedBy="horoscopeTextsPublished")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     *
     * @Groups({"horoscopeTextPublished"})
     */
    private $horoscopeText;

    /**
     * @ORM\ManyToOne(targetEntity=AstrologicalSign::class, fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     *
     * @Groups({"horoscopeText", "horoscopeTextPublished"})
     */
    private $astrologicalSign;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     *
     * @Groups({"horoscopeText", "horoscopeTextPublished"})
     */
    private $publishDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"horoscopeText", "horoscopeTextPublished"})
     */
    private $note;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHoroscopeText(): ?HoroscopeText
    {
        return $this->horoscopeText;
    }

    public function setHoroscopeText(?HoroscopeText $horoscopeText): self
    {
        $this->horoscopeText = $horoscopeText;

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

    public function getPublishDate(): ?\DateTimeInterface
    {
        return $this->publishDate;
    }

    public function setPublishDate(\DateTimeInterface $publishDate): self
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }
}
