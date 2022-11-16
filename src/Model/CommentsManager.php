<?php

namespace App\Model;

class CommentsManager extends AbstractManager
{
    public const TABLE = 'comments';

    public function getUserComment(int $id)
    {
        $statement = $this->pdo->prepare('SELECT comments.content, comments.id, u.name, comments.users_id FROM '.self::TABLE.' INNER JOIN users u ON comments.users_id = u.id INNER JOIN articles a ON comments.articles_id = a.id WHERE a.id = :id ORDER BY comments.date DESC  ');
        $statement->bindValue(':id', $id, \PDO::PARAM_STR);

        $statement->execute();

        return $statement->fetchAll();
    }

    public function addComment($userComment, $articlesId, $usersId): int
    {
        $statement = $this->pdo->prepare('INSERT INTO comments (content, date, articles_id, users_id) VALUES (:userComment, now(), :articles_id, :users_id)');
        $statement->bindValue(':userComment', $userComment, \PDO::PARAM_STR);
        $statement->bindValue(':articles_id', $articlesId, \PDO::PARAM_INT);
        $statement->bindValue(':users_id', $usersId, \PDO::PARAM_INT);


        $statement->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function commentByArticle($userComment): int
    {
        $statement = $this->pdo->prepare('SELECT * FROM '.self::TABLE.' INNER JOIN users u ON comments.users_id = u.id ');
        $statement->bindValue(':userComment', $userComment, \PDO::PARAM_STR);
        $statement->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function modifyComment($modifiedComment, $id)
    {
        $statement = $this->pdo->prepare('UPDATE comments SET content = :content, date = now()
                   WHERE id=:id');
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->bindValue(':content', $modifiedComment, \PDO::PARAM_STR);

        $statement->execute();
    }

}
