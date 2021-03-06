<?php

namespace model;

use entity\Entity;
use Exception;
use PDO;

class Model
{
    protected $db;
    protected $host = 'localhost';
    protected $login = 'root';
    protected $dbname = 'boutique';
    protected $password = '';
    protected $table;

    public function __construct()
    {
        try {
            $db = new PDO('mysql:host=localhost;dbname=boutique', $this->login, $this->password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        $this->db = $db;
        $this->table = $this->getTableName();
    }

    public function getAll($table = NULL)
    {
        $table = $table != NULL ? $table : $this->table;
        $SQL = "SELECT * FROM $table";
        return $this->fetchAll($SQL);
    }

    public function getBy(int $idValue, string $idKey, $table = NULL)
    {
        $table = $table != NULL ? $table : $this->table;
        $SQL = "SELECT * FROM $table WHERE $idKey = $idValue";
        return $this->fetch($SQL);
    }

    public function getAllBy(int $idValue, string $idKey, $table = NULL)
    {
        $table = $table != NULL ? $table : $this->table;
        $SQL = "SELECT * FROM $table WHERE $idKey = $idValue";
        return $this->fetchAll($SQL);
    }

    public function fetch(string $SQL)
    {
        $sth = $this->db->query($SQL);
        $sth->setFetchMode(PDO::FETCH_CLASS, Entity::class);
        return $sth->fetch();
    }

    public function fetchAll(string $SQL)
    {
        $sth = $this->db->query($SQL);
        $sth->setFetchMode(PDO::FETCH_CLASS, Entity::class);
        return $sth->fetchAll();
    }

    public function add($data, $table = NULL)
    {
        $table = $table != NULL ? $table : $this->table;
        $SQL = "INSERT INTO $table (";
        $SQL_P2 = '';
        $argNb = count($data);
        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $SQL .= " $key";
            $SQL_P2 .= " :$key";

            if ($argNb != $i) {
                $SQL .= ',';
                $SQL_P2 .= ',';
            }
        }
        $SQL .= ') VALUES (' . $SQL_P2 . ');';
        $sth = $this->db->prepare($SQL);

        foreach ($data as $key => $value) {
            $sth->bindParam(":$key", $data[$key]);
        }
        $sth->execute();
        return $this->db->lastInsertId();
    }

    public function edit(array $data, $table = NULL)
    {
        $table = $table != NULL ? $table : $this->table;

        reset($data);
        $id_key = key($data);
        $argNb = count($data);
        $i = 0;

        $SQL = "UPDATE $table SET ";
        foreach ($data as $key => $value) {
            $i++;

            $SQL .= "$key = :$key ";
            if ($argNb != $i) {
                $SQL .= ',';
            }
        }
        $SQL .= "WHERE $id_key = :$id_key;";

        $sth = $this->db->prepare($SQL);

        foreach ($data as $key => &$value) {
            if (preg_match('/^0/', $value)) {
                $value = $value;
            } else if (preg_match('/^[\\d]{1,}$/', $value)) {
                $value = intval($value, 10);
            }
            $sth->bindParam(":$key", $value);
        }

        $sth->execute();
    }

    public function delete(array $data, $table = NULL)
    {
        $table = $table != NULL ? $table : $this->table;

        reset($data);
        $id = key($data);
        $this->db->query("DELETE FROM $table WHERE $id = {$data[$id]}");
    }

    public function getTableName()
    {
        $className = get_class($this);
        $pos = strpos($className, 'model\\') + 6;
        $table = '';
        for ($i = $pos; $i < strlen($className); $i++) {
            $table .= strtolower($className[$i]);
        }
        return ucfirst(str_replace('model', '', $table));
    }

    public function getConfArray($table = 'Configuration')
    {
        $SQL = "SELECT * FROM $table";
        $results = $this->fetchAll($SQL);
        $confArray = [];
        foreach ($results as $result) {
            $confArray[$result->name] = $result;
        }
        return $confArray;
    }
}
