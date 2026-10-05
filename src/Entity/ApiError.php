<?php

namespace App\Entity;

class ApiError
{
    public const HTTP_UNAUTHORIZED = 'Unauthorized'; // 401
    public const HTTP_FORBBIDEN = 'Forbidden'; // 403 - sikeres autentikáció (authorized), de nincs jogosultsága a kért állományhoz

    public const UNPROCESSABLE_ENTITY = 'Unprocessable entity.';
    public const RESOURCE_NOT_FOUND = 'Resource not found.';

}
