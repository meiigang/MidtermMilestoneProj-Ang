<?php
declare(strict_types=1);

class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function register(string $name, string $email, string $password): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
        );
        $statement->execute([
            'name' => $name,
            'email' => strtolower($email),
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email');
        $statement->execute(['email' => strtolower($email)]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        return $user !== null && password_verify($password, (string) $user['password']) ? $user : null;
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, email, created_at FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return $user === false ? null : $user;
    }
}
