<?php

namespace App\Repository\Eloquent;

use App\Repository\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;


class BaseRepository implements RepositoryInterface
{
    protected $model;

    public function __construct(Model $model)     
    {         
        $this->model = $model;
    }

    /**
    * @param array $attributes
    *
    * @return Model
    */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }
 
    /**
    * @param $id
    * @return Model
    */
    public function getById($id): ?Model
    {
        return $this->model->findOrFail($id);
    }

    public function getAll(int $perPage = 10)
    {
        return $this->model->paginate($perPage);
    }

    public function delete(int $id)
    {
        $modelToDelete = $this->model->findOrFail($id);
        $modelToDelete->delete();
    }
}

?>