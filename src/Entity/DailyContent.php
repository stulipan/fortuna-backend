<?php

namespace App\Entity;

use App\Repository\DailyContentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DailyContentRepository::class)
 */
class DailyContent
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity=Quote::class, inversedBy="dailyContents")
     */
    private $quote;

    /**
     * @ORM\OneToOne(targetEntity=HoroscopeFinal::class, inversedBy="dailyContent", cascade={"persist", "remove"})
     */
    private $horoscope;

    /**
     * @ORM\OneToOne(targetEntity=HoroscopeFinal::class, cascade={"persist", "remove"})
     */
    private $horoscopeAddendum;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuote(): ?DailyQuote
    {
        return $this->quote;
    }

    public function setQuote(?DailyQuote $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    public function getHoroscope(): ?HoroscopeFinal
    {
        return $this->horoscope;
    }

    public function setHoroscope(?HoroscopeFinal $horoscope): self
    {
        $this->horoscope = $horoscope;

        return $this;
    }

    public function getHoroscopeAddendum(): ?HoroscopeFinal
    {
        return $this->horoscopeAddendum;
    }

    public function setHoroscopeAddendum(?HoroscopeFinal $horoscopeAddendum): self
    {
        $this->horoscopeAddendum = $horoscopeAddendum;

        return $this;
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

}
