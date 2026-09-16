<?php

declare(strict_types=1);

namespace Core;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * Objet de transfert (entrée formulaire / JSON ou sortie API).
 * Ce n'est pas un modèle : pas de SQL ici.
 *
 * fromArray() hydrate les propriétés publiques par réflexion et vérifie
 * les types PHP (int, string…). Champ requis manquant → ValidationException.
 */
abstract class DTO
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        $class = new ReflectionClass(static::class);
        $instance = $class->newInstanceWithoutConstructor();
        $errors = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $type = $property->getType();
            $present = array_key_exists($name, $data);

            if (!$present) {
                if ($property->hasDefaultValue()) {
                    continue;
                }
                if ($type !== null && $type->allowsNull()) {
                    $property->setValue($instance, null);
                    continue;
                }
                $errors[$name] = "Le champ {$name} est requis.";
                continue;
            }

            // Formulaires HTML : tout arrive en string → on coerce vers int/float/bool.
            try {
                $property->setValue($instance, self::coerce($data[$name], $type));
            } catch (\InvalidArgumentException $e) {
                $errors[$name] = $e->getMessage();
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $instance;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $class = new ReflectionClass($this);
        $data = [];
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $data[$property->getName()] = $property->getValue($this);
        }

        return $data;
    }

    private static function coerce(mixed $value, mixed $type): mixed
    {
        if (!$type instanceof ReflectionNamedType) {
            return $value;
        }

        $name = $type->getName();
        if ($value === null) {
            if ($type->allowsNull()) {
                return null;
            }
            throw new \InvalidArgumentException("La valeur ne peut pas être nulle ({$name} attendu).");
        }

        return match ($name) {
            'string' => is_scalar($value) ? (string) $value : throw new \InvalidArgumentException('Une chaîne est attendue.'),
            'int' => is_numeric($value) ? (int) $value : throw new \InvalidArgumentException('Un entier est attendu.'),
            'float' => is_numeric($value) ? (float) $value : throw new \InvalidArgumentException('Un nombre est attendu.'),
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ?? throw new \InvalidArgumentException('Un booléen est attendu.'),
            'array' => is_array($value) ? $value : throw new \InvalidArgumentException('Un tableau est attendu.'),
            default => $value,
        };
    }
}
