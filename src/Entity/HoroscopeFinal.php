<?php

namespace App\Entity;

use App\Repository\HoroscopeFinalRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=HoroscopeFinalRepository::class)
 */
class HoroscopeFinal
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
     *
     * @Groups({"horoscopeList"})
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @Groups({"horoscopeList"})
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity=DailyContent::class, mappedBy="horoscope", cascade={"persist", "remove"})
     */
    private $dailyContent;

    /**
     * @ORM\ManyToOne(targetEntity=AstrologicalSign::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"horoscopeList"})
     */
    private $astrologicalSign;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"horoscopeList", "bundled", "bundledFinal"})
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=2)
     *
     * @Groups({"horoscopeList"})
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDailyContent(): ?DailyContent
    {
        return $this->dailyContent;
    }

    public function setDailyContent(?DailyContent $dailyContent): self
    {
        // unset the owning side of the relation if necessary
        if ($dailyContent === null && $this->dailyContent !== null) {
            $this->dailyContent->setHoroscope(null);
        }

        // set the owning side of the relation if necessary
        if ($dailyContent !== null && $dailyContent->getHoroscope() !== $this) {
            $dailyContent->setHoroscope($this);
        }

        $this->dailyContent = $dailyContent;

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

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): self
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
