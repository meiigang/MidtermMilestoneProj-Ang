<?php
declare(strict_types=1);

class Comment
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $recipeId, int $userId, string $content): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO comments (recipe_id, user_id, content) VALUES (:recipe_id, :user_id, :content)'
        );
        $statement->execute(['recipe_id' => $recipeId, 'user_id' => $userId, 'content' => $content]);
        return (int) $this->pdo->lastInsertId();
    }

    public function forRecipe(int $recipeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT comments.*, users.name AS author_name FROM comments JOIN users ON users.id = comments.user_id
             WHERE comments.recipe_id = :recipe_id ORDER BY comments.created_at ASC, comments.id ASC'
        );
        $statement->execute(['recipe_id' => $recipeId]);
        return $statement->fetchAll();
    }

    public function update(int $id, int $userId, string $content): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE comments SET content = :content, is_edited = 1, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute(['id' => $id, 'user_id' => $userId, 'content' => $content]);
        return $statement->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM comments WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $id, 'user_id' => $userId]);
        return $statement->rowCount() > 0;
    }

    public function belongsToUser(int $id, int $userId): bool
    {
        $statement = $this->pdo->prepare('SELECT id FROM comments WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $id, 'user_id' => $userId]);
        return $statement->fetch() !== false;
    }
}
