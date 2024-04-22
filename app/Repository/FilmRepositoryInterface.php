<?php

namespace App\Repository;

use App\Repository\RepositoryInterface;


interface FilmRepositoryInterface extends RepositoryInterface
{
    public function create(array $content);
}

?>