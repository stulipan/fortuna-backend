<?php

namespace App\Security;

use App\Entity\ApiError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class ApiAccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $data = [
            'error' => ApiError::HTTP_FORBBIDEN,
            'message' => 'Sikeres autentikáció (authorized), DE nincs jogosultságod a kért állományhoz.'
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }
}
