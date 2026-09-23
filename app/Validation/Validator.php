<?php
declare(strict_types=1);

namespace App\Validation;

/**
 * Validation Utilities
 * Milestone 3: Foundation
 */
class Validator
{
    public static function required(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }

    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function slug(string $slug): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $slug) === 1;
    }

    public static function url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function length(string $value, int $min, int $max): bool
    {
        $len = mb_strlen($value, 'UTF-8');
        return $len >= $min && $len <= $max;
    }

    public static function minLength(string $value, int $min): bool
    {
        return mb_strlen($value, 'UTF-8') >= $min;
    }

    public static function maxLength(string $value, int $max): bool
    {
        return mb_strlen($value, 'UTF-8') <= $max;
    }

    public static function inArray($value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    // Contact form specific
    public static function validateContact(array $data): array
    {
        $errors = [];

        if (!self::required($data['name'] ?? null) || !self::length($data['name'], 2, 100)) {
            $errors['name'] = 'Name is required (2-100 chars)';
        }

        if (!self::required($data['email'] ?? null) || !self::email($data['email'])) {
            $errors['email'] = 'Valid email is required';
        }

        if (!self::required($data['subject'] ?? null) || !self::length($data['subject'], 2, 255)) {
            $errors['subject'] = 'Subject is required (2-255 chars)';
        }

        if (!self::required($data['message'] ?? null) || !self::length($data['message'], 10, 5000)) {
            $errors['message'] = 'Message is required (10-5000 chars)';
        }

        // Honeypot should be empty
        if (!empty($data['honeypot_field'] ?? $data['website'] ?? '')) {
            $errors['honeypot'] = 'Bot detected';
        }

        return $errors;
    }
}
