<?php

interface ISecurity
{
    public function requireLogin(?array $tipoUsurio): ClaimDTO;
    public function tokenGenerator(array $data): string;
    public function deleteTokensExpirados(?mysqli $unLink = NULL): void;
}