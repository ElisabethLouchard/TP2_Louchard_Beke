<?php

namespace App\Repository\Eloquent;


use App\Models\Film;
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

   public function getAverageRental($films)
   {
      return round($films->avg('rental_rate'), 2);
   }
}

?>