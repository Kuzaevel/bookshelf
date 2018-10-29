<?php
/**
 * Created by PhpStorm.
 * User: cool
 * Date: 29.10.18
 * Time: 12:53
 */

namespace App;

use \PDO;
use Slim\Container;


class Model
{
    protected $database;
    protected static $connect;

    public function __construct(Container $c)
    {
        self::setConnect($c->get('database'));
        if(empty($this->db)) {
            $this->database = static::$connect;
        }
    }

    public static function setConnect($database) {
        if(empty(static::$connect)) {
            static::$connect = $database;
        }
    }

    public function getListBooks () {
        $query = $this->database->query("SELECT * FROM books");
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    public function addBook (array $formData) {
        $sql = "INSERT INTO books (name,  author, genre, year) 
                           VALUES (:name,:author,:genre,:year)";
        $query = $this->database->prepare($sql);
        $query->bindParam("name", $formData["name"]);
        $query->bindParam("author", $formData["author"]);
        $query->bindParam("genre", $formData["genre"]);
        $query->bindParam("year", $formData["year"]);
        $query->execute();
        return true;
    }

    public function removeBook (int $id) {
        $sql = "DELETE FROM books WHERE id=:id";
        $query = $this->database->prepare($sql);
        $query->bindParam("id", $id);
        $query->execute();
        return true;
    }

    public function getOneBook (int $id) {
        $sql = "SELECT * FROM books where id=:id";
        $query = $this->database->prepare($sql);
        $query->bindParam("id", $id);
        $query->execute();
        return $query->fetch(PDO::FETCH_OBJ);
    }

    public function updateOneBook (array $formData) {
        $sql = ("UPDATE books SET name=:name, 
                                  author=:author,
                                  genre=:genre, 
                                  year=:year 
                 where id=:id");
        $query = $this->database->prepare($sql);
        $query->bindParam("id", $formData["id"]);
        $query->bindParam("name", $formData["name"]);
        $query->bindParam("author", $formData["author"]);
        $query->bindParam("genre", $formData["genre"]);
        $query->bindParam("year", $formData["year"]);
        $query->execute();
        return true;
    }

}