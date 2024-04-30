<?php

namespace App\Repository\Eloquent;


use App\Models\Film;
use App\Models\User;
use App\Models\Role;
use App\Repository\FilmRepositoryInterface;

class FilmRepository extends BaseRepository implements FilmRepositoryInterface
{

    /**
    * ExampleRepository constructor.
    *
    * @param Film $model
    */
   public function __construct(Film $model)
   {
       parent::__construct($model);
   }
}

?>