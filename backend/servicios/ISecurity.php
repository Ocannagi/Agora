<?php

interface ISecurity
{
    /**
     * Constata que el token de seguridad es válido y no ha expirado.
     * Si el token es válido, devuelve un ClaimDTO con los datos del usuario.
     * @param array|null $tipoUsurio
     * Los tipos de usuario que pueden acceder a este recurso.
     * Si es null, no se valida el tipo de usuario.
     * @return ClaimDTO
     * Los datos del usuario.
     * @throws Exception
     * Si el token no es válido o ha expirado, lanza una excepción.
     * Si el tipo de usuario no es válido, lanza una excepción.
     */
    public function requireLogin(?array $tipoUsurio): ClaimDTO;
    public function tokenGenerator(array $data): string;
    public function deleteTokensExpirados(?mysqli $unLink = NULL): void;
    public function hashPassword(string $password): string;
    public function verifyPassword(string $password, string $hash): bool;
}