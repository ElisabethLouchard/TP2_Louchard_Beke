<?php

namespace App\Repository;

interface RepositoryInterface
{
    public function create(array $content);
    public function getAll(int $perPage = 10);
    public function getById(int $id);
    //public function update(int $id, array $content);
    public function delete(int $id);
}

?>