<?php

namespace App\Models;

use App\Services\Database;
use Envms\FluentPDO\Query;
use PDOStatement;

class JobangebotModel implements ModelInterface
{
    private Query $db;

    public function __construct()
    {
        $this->db = (new Database())->getFluent();
    }

    public function findAll(): bool|array
    {
        return $this->db->from('jobangebote')->fetchAll();
    }

    public function findById($id): mixed
    {
        return $this->db->from('jobangebote')->where('id', $id)->fetch();
    }

    public function create($data): bool|int
    {
        return $this->db->insertInto('jobangebote', $data)->execute();
    }

    public function update($id, $data): bool|int|PDOStatement
    {
        return $this->db->update('jobangebote', $data, $id)->execute();
    }

    public function delete($id): bool
    {
        return $this->db->deleteFrom('jobangebote')->where('id', $id)->execute();
    }
}