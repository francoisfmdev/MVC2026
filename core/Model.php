<?php

declare(strict_types=1);

namespace Core;

    /**
     * Classe de base des modèles : une classe PHP = une table SQL.
     *
     * Pas de mapping objet-relationnel : all() / find() renvoient des tableaux.
     * Hors conteneur DI, usage statique : User::all(), User::table()->where(...).
     * Pour du SQL entièrement écrit à la main : query($sql, $params).
     */
abstract class Model
{
    protected static string $table;

    public static function tableName(): string
    {
        return static::$table;
    }

    /** QueryBuilder déjà ciblé sur la table du modèle. */
    public static function table(): QueryBuilder
    {
        return new QueryBuilder(Database::connection(), static::$table);
    }

    /** @return list<array<string, mixed>> */
    public static function all(): array
    {
        return static::table()->get();
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        return static::table()->where('id', '=', $id)->first();
    }

    /**
     * SQL brut, bindings préparés. Le SQL reste entièrement visible.
     *
     * @param array<int|string, mixed> $params
     * @return list<array<string, mixed>>
     */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
