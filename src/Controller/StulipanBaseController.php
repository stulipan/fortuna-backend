<?php

namespace App\Controller;

use App\Entity\ApiError;
use App\Entity\Enums;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class StulipanBaseController extends AbstractController
{
    public function jsonErrorResponse(array $errorResponse = [], ?int $status)
    {
        if (!isset($errorResponse['error'])) {
            throw new \InvalidArgumentException('HIBA: Invalid error response format. Field "error" is missing.');
        }
        if (!isset($errorResponse['message'])) {
            throw new \InvalidArgumentException('HIBA: Invalid error response format. Field "message" is missing.');
        }

        return new JsonResponse($errorResponse, $status, []);
    }

    public function validateDateString(string $dateString = null, string $format = Enums::DATE_FORMAT)
    {
        if (!$dateString) {
            return $this->jsonErrorResponse([
                'error' => ApiError::UNPROCESSABLE_ENTITY,
                'message' => 'Hiányzik a dateString.',
            ], 422);
        }

        $date = \DateTime::createFromFormat($format, $dateString);

        if ($date === false) {
            return $this->jsonErrorResponse([
                'error' => ApiError::UNPROCESSABLE_ENTITY,
                'message' => sprintf('Érvénytelen dateString, hibás lehet a dátum formátuma (%s).', $dateString),
            ], 422);
        }
        return;
    }

    /**
     * @param string|null $dateString
     * @param string $format
     * @return \DateTime|null
     */
    public function createDateFromFormat(string $dateString = null, string $format = Enums::DATE_FORMAT)
    {
        if (!$dateString) {
            return null;
        }

        $date = \DateTime::createFromFormat($format, $dateString);
        if ($date === false) {
            return null;
        }

        if ($date instanceof \DateTime) {
            $date->setTime(0, 0, 0);
        }
        return $date;
    }
}
