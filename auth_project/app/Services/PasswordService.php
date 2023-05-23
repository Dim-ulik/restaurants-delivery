<?php

namespace App\Services;

class PasswordService
{
    public static function codePassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function checkPassword($hashedPassword, $password): bool
    {
        return (password_verify($password, $hashedPassword));
    }
}
