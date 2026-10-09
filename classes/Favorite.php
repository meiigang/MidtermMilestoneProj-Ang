<?php
declare(strict_types=1);

class Favorite
{
    public function __construct(private PDO $pdo)
    {
    }

    public function add(int $userId, int $recipeId): void
    {
        $statement = $this->pdo->prepare(
            'INSERT IGNORE INTO favorites (user_id, recipe_id) VALUES (:user_id, :recipe_id)'
        );
        $statement->execute(['user_id' => $userId, 'recipe_id' => $recipeId]);
    }

    public function remove(int $userId, int $recipeId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id');
        $statement->execute(['user_id' => $userId, 'recipe_id' => $recipeId]);
    }

    public function toggle(int $userId, int $recipeId): bool
    {
        if ($this->isFavorited($userId, $recipeId)) {
            $this->remove($userId, $recipeId);
            return false;
        }
        $this->add($userId, $recipeId);
        return true;
    }

    public function isFavorited(int $userId, int $recipeId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id');
        $statement->execute(['user_id' => $userId, 'recipe_id' => $recipeId]);
        return $statement->fetch() !== false;
    }
}
