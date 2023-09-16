<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class HoroscopeBundled
{

    /**
     * @var \DateTime
     * @Assert\NotNull()
     *
     * @Groups({"horoscopeList", "bundled"})
     */
    private $date;

    /**
     * @var HoroscopeFinal
     *
     * @Groups({"horoscopeList", "bundled"})
     */
    private $base;

    /**
     * @var HoroscopeFinal|null
     *
     * @Groups({"horoscopeList", "bundled"})
     */
    private $addendum;

    /**
     * @var HoroscopeRaw
     *
     * @Groups({"horoscopeList", "bundled"})
     */
    private $raw;

    /**
     * @var AstrologicalSign
     *
     * @Groups({"horoscopeList", "bundled"})
     */
    private $astrologicalSign;

    /**
     * @Groups({"horoscopeList", "bundled"})
     */
    private $locale;



    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return HoroscopeFinal
     */
    public function getBase(): HoroscopeFinal
    {
        return $this->base;
    }

    /**
     * @param HoroscopeFinal $base
     */
    public function setBase(HoroscopeFinal $base): void
    {
        $this->base = $base;
    }

    /**
     * @return HoroscopeFinal|null
     */
    public function getAddendum(): ?HoroscopeFinal
    {
        return $this->addendum;
    }

    /**
     * @param HoroscopeFinal|null $addendum
     */
    public function setAddendum(?HoroscopeFinal $addendum): void
    {
        $this->addendum = $addendum;
    }

    /**
     * @return HoroscopeRaw
     */
    public function getRaw(): HoroscopeRaw
    {
        return $this->raw;
    }

    /**
     * @param HoroscopeRaw $raw
     */
    public function setRaw(HoroscopeRaw $raw): void
    {
        $this->raw = $raw;
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
