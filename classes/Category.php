<?php
declare(strict_types=1);

class Category
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    }

    public function exists(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT id FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch() !== false;
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();
        return $category === false ? null : $category;
    }
}
