<?php

namespace App\Service;

class PasswordValidator
{
    /**
     * Minimum password length for admin and moderator
     */
    private const MIN_LENGTH = 12;

    /**
     * Validate password strength for admin and moderator accounts
     *
     * @param string $password
     * @return array Returns ['valid' => bool, 'errors' => string[]]
     */
    public function validatePassword(string $password): array
    {
        $errors = [];

        // Check minimum length
        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = sprintf('Le mot de passe doit contenir au moins %d caractères', self::MIN_LENGTH);
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule';
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule';
        }

        // Check for at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }

        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*()_+-=[]{};\':"|,.<>/? ou similaire)';
        }

        // Check for common weak passwords (case-insensitive)
        $weakPasswords = ['password', 'admin', 'moderator', '123456', 'qwerty', 'letmein', 'welcome', 'monkey'];
        if (in_array(strtolower($password), $weakPasswords)) {
            $errors[] = 'Ce mot de passe est trop commun et facile à deviner';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get password requirements as a human-readable string
     */
    public function getRequirements(): string
    {
        return sprintf(
            'Le mot de passe doit contenir au moins %d caractères, incluant au moins une majuscule, une minuscule, un chiffre et un caractère spécial',
            self::MIN_LENGTH
        );
    }
}

