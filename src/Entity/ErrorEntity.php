<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */

class ErrorEntity implements \JsonSerializable
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $propertyName;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $message;

    public function __construct(string $propertyName, string $message)
    {
        $this->propertyName = $propertyName;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'propertyName'      => $this->getPropertyName(),
            'message'           => $this->getMessage(),
        ];
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @param string $propertyName
     */
    public function setPropertyName(string $propertyName)
    {
        $this->propertyName = $propertyName;
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
