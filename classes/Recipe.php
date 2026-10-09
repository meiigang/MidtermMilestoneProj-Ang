<?php
declare(strict_types=1);

class Recipe
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, int $categoryId, string $title, string $description, string $instructions, array $ingredients): int
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO recipes (user_id, category_id, title, description, instructions)
                 VALUES (:user_id, :category_id, :title, :description, :instructions)'
            );
            $statement->execute([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $description,
                'instructions' => $instructions,
            ]);
            $recipeId = (int) $this->pdo->lastInsertId();
            $this->replaceIngredients($recipeId, $ingredients);
            $this->pdo->commit();
            return $recipeId;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function update(int $id, int $userId, int $categoryId, string $title, string $description, string $instructions, array $ingredients): bool
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'UPDATE recipes SET category_id = :category_id, title = :title, description = :description,
                 instructions = :instructions, is_edited = 1, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND user_id = :user_id'
            );
            $statement->execute([
                'id' => $id,
                'user_id' => $userId,
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $description,
                'instructions' => $instructions,
            ]);
            if ($statement->rowCount() === 0 && !$this->owns($id, $userId)) {
                $this->pdo->rollBack();
                return false;
            }
            $this->replaceIngredients($id, $ingredients);
            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id, int $userId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM recipes WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $id, 'user_id' => $userId]);
        return $statement->rowCount() > 0;
    }

    public function owns(int $id, int $userId): bool
    {
        $statement = $this->pdo->prepare('SELECT id FROM recipes WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $id, 'user_id' => $userId]);
        return $statement->fetch() !== false;
    }

    public function find(int $id, ?int $userId = null): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT recipes.*, users.name AS author_name, categories.name AS category_name,
                    (SELECT COUNT(*) FROM comments WHERE comments.recipe_id = recipes.id) AS comment_count,
                    ' . ($userId === null ? '0' : '(SELECT COUNT(*) FROM favorites WHERE favorites.recipe_id = recipes.id AND favorites.user_id = :viewer_id)') . ' AS is_favorited
             FROM recipes
             JOIN users ON users.id = recipes.user_id
             JOIN categories ON categories.id = recipes.category_id
             WHERE recipes.id = :id'
        );
        $parameters = ['id' => $id];
        if ($userId !== null) {
            $parameters['viewer_id'] = $userId;
        }
        $statement->execute($parameters);
        $recipe = $statement->fetch();
        if ($recipe === false) {
            return null;
        }
        $recipe['ingredients'] = $this->ingredients($id);
        return $recipe;
    }

    public function all(string $search = '', ?int $categoryId = null, ?int $userId = null): array
    {
        $where = [];
        $parameters = [];
        if ($search !== '') {
            $where[] = '(recipes.title LIKE :search OR recipes.description LIKE :search)';
            $parameters['search'] = '%' . $search . '%';
        }
        if ($categoryId !== null && $categoryId > 0) {
            $where[] = 'recipes.category_id = :category_id';
            $parameters['category_id'] = $categoryId;
        }
        $favoriteSelect = $userId === null ? '0' : '(SELECT COUNT(*) FROM favorites WHERE favorites.recipe_id = recipes.id AND favorites.user_id = :viewer_id)';
        if ($userId !== null) {
            $parameters['viewer_id'] = $userId;
        }
        $sql = 'SELECT recipes.*, users.name AS author_name, categories.name AS category_name,
                       (SELECT COUNT(*) FROM comments WHERE comments.recipe_id = recipes.id) AS comment_count,
                       ' . $favoriteSelect . ' AS is_favorited
                FROM recipes JOIN users ON users.id = recipes.user_id JOIN categories ON categories.id = recipes.category_id';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY recipes.created_at DESC, recipes.id DESC';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function popular(int $limit = 3, ?int $userId = null): array
    {
        $limit = max(1, min($limit, 3));
        $favoriteSelect = $userId === null ? '0' : '(SELECT COUNT(*) FROM favorites WHERE favorites.recipe_id = recipes.id AND favorites.user_id = :viewer_id)';
        $sql = 'SELECT recipes.*, users.name AS author_name, categories.name AS category_name,
                       COUNT(comments.id) AS comment_count, ' . $favoriteSelect . ' AS is_favorited
                FROM recipes JOIN users ON users.id = recipes.user_id JOIN categories ON categories.id = recipes.category_id
                LEFT JOIN comments ON comments.recipe_id = recipes.id
                GROUP BY recipes.id ORDER BY comment_count DESC, recipes.created_at DESC LIMIT ' . $limit;
        $statement = $this->pdo->prepare($sql);
        if ($userId === null) {
            $statement->execute();
        } else {
            $statement->execute(['viewer_id' => $userId]);
        }
        return $statement->fetchAll();
    }

    public function favoritesFor(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT recipes.*, users.name AS author_name, categories.name AS category_name,
                    (SELECT COUNT(*) FROM comments WHERE comments.recipe_id = recipes.id) AS comment_count,
                    1 AS is_favorited
             FROM favorites JOIN recipes ON recipes.id = favorites.recipe_id
             JOIN users ON users.id = recipes.user_id JOIN categories ON categories.id = recipes.category_id
             WHERE favorites.user_id = :user_id ORDER BY favorites.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    private function ingredients(int $recipeId): array
    {
        $statement = $this->pdo->prepare('SELECT ingredient_name FROM ingredients WHERE recipe_id = :recipe_id ORDER BY sort_order, id');
        $statement->execute(['recipe_id' => $recipeId]);
        return array_column($statement->fetchAll(), 'ingredient_name');
    }

    private function replaceIngredients(int $recipeId, array $ingredients): void
    {
        $delete = $this->pdo->prepare('DELETE FROM ingredients WHERE recipe_id = :recipe_id');
        $delete->execute(['recipe_id' => $recipeId]);
        $insert = $this->pdo->prepare(
            'INSERT INTO ingredients (recipe_id, ingredient_name, sort_order) VALUES (:recipe_id, :ingredient_name, :sort_order)'
        );
        foreach (array_values($ingredients) as $index => $ingredient) {
            $insert->execute([
                'recipe_id' => $recipeId,
                'ingredient_name' => $ingredient,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
