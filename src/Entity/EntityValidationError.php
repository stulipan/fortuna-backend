<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */

class EntityValidationError implements \JsonSerializable
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $property;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $message;

    public function __construct(string $property, string $message)
    {
        $this->property = $property;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'property'      => $this->getProperty(),
            'message'           => $this->getMessage(),
        ];
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @param string $property
     */
    public function setProperty(string $property)
    {
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

}
