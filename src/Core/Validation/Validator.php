<?php

declare(strict_types=1);

namespace App\Core\Validation;

use App\Core\Database\Database;

class Validator
{
    private array $errors = [];
    private array $data = [];
    private array $rules = [];

    public function __construct(
        private readonly Database $db
    ) {}

    public function validate(array $data, array $rules): array
    {
        $this->errors = [];
        $this->data = $data;
        $this->rules = $rules;

        foreach ($rules as $field => $ruleString) {
            $this->validateField($field, $ruleString);
        }

        return $this->errors;
    }

    private function validateField(string $field, string $ruleString): void
    {
        $rules = explode('|', $ruleString);
        $value = $this->data[$field] ?? null;

        foreach ($rules as $rule) {
            if (str_contains($rule, ':')) {
                [$ruleName, $parameter] = explode(':', $rule, 2);
            } else {
                $ruleName = $rule;
                $parameter = null;
            }

            $method = 'validate' . str_replace('_', '', ucwords($ruleName, '_'));

            if (method_exists($this, $method)) {
                $error = $this->{$method}($field, $value, $parameter);
                if ($error) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
        }
    }

    private function validateRequired(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return "The {$field} field is required.";
        }
        return null;
    }

    private function validateString(string $field, mixed $value): ?string
    {
        if ($value !== null && !is_string($value)) {
            return "The {$field} field must be a string.";
        }
        return null;
    }

    private function validateInteger(string $field, mixed $value): ?string
    {
        if ($value !== null && !is_numeric($value)) {
            return "The {$field} field must be an integer.";
        }
        return null;
    }

    private function validateEmail(string $field, mixed $value): ?string
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "The {$field} field must be a valid email address.";
        }
        return null;
    }

    private function validateMin(string $field, mixed $value, ?string $parameter): ?string
    {
        if ($value === null) return null;

        $min = (int) $parameter;

        if (is_string($value) && strlen($value) < $min) {
            return "The {$field} field must be at least {$min} characters.";
        }

        if (is_numeric($value) && $value < $min) {
            return "The {$field} field must be at least {$min}.";
        }

        return null;
    }

    private function validateMax(string $field, mixed $value, ?string $parameter): ?string
    {
        if ($value === null) return null;

        $max = (int) $parameter;

        if (is_string($value) && strlen($value) > $max) {
            return "The {$field} field must not exceed {$max} characters.";
        }

        if (is_numeric($value) && $value > $max) {
            return "The {$field} field must not exceed {$max}.";
        }

        return null;
    }

    private function validateIn(string $field, mixed $value, ?string $parameter): ?string
    {
        if ($value === null) return null;

        $allowed = explode(',', $parameter);

        if (!in_array($value, $allowed)) {
            return "The {$field} field must be one of: " . implode(', ', $allowed);
        }

        return null;
    }

    private function validateUnique(string $field, mixed $value, ?string $parameter): ?string
    {
        if ($value === null) return null;

        $parts = explode(',', $parameter);
        $table = $parts[0];
        $column = $parts[1] ?? $field;

        $exists = $this->db->fetchOne(
            "SELECT 1 FROM {$table} WHERE {$column} = :value",
            ['value' => $value]
        );

        if ($exists) {
            return "The {$field} has already been taken.";
        }

        return null;
    }

    private function validateConfirmed(string $field, mixed $value): ?string
    {
        $confirmationField = $field . '_confirmation';
        $confirmationValue = $this->data[$confirmationField] ?? null;

        if ($value !== $confirmationValue) {
            return "The {$field} confirmation does not match.";
        }

        return null;
    }

    private function validateRegex(string $field, mixed $value, ?string $parameter): ?string
    {
        if ($value === null) return null;

        if (!preg_match($parameter, $value)) {
            return "The {$field} field format is invalid.";
        }

        return null;
    }

    private function validateUrl(string $field, mixed $value): ?string
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
            return "The {$field} field must be a valid URL.";
        }
        return null;
    }

    private function validateNullable(string $field, mixed $value): ?string
    {
        // This just marks the field as nullable, no validation needed
        return null;
    }

    private function validateBoolean(string $field, mixed $value): ?string
    {
        if ($value !== null && !in_array($value, [true, false, 0, 1, '0', '1'], true)) {
            return "The {$field} field must be true or false.";
        }
        return null;
    }
}