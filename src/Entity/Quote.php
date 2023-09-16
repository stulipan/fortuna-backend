<?php

namespace App\Entity;

use App\Repository\QuoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuoteRepository::class)
 */
class Quote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $quote;

    /**
     * @ORM\Column(type="text")
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=DailyContent::class, mappedBy="quote")
     */
    private $dailyContents;

    public function __construct()
    {
        $this->dailyContents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(string $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, DailyContent>
     */
    public function getDailyContents(): Collection
    {
        return $this->dailyContents;
    }

    public function addDailyContent(DailyContent $dailyContent): self
    {
        if (!$this->dailyContents->contains($dailyContent)) {
            $this->dailyContents[] = $dailyContent;
            $dailyContent->setQuote($this);
        }

        return $this;
    }

    public function removeDailyContent(DailyContent $dailyContent): self
    {
        if ($this->dailyContents->removeElement($dailyContent)) {
            // set the owning side to null (unless already changed)
            if ($dailyContent->getQuote() === $this) {
                $dailyContent->setQuote(null);
            }
        }

        return $this;
    }
}
