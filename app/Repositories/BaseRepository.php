<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository implements BaseInterface
{

    protected $model;

    public function __construct($model)
    {
        $this->model = $model->newQuery();
    }

    public function newQuery($model)
    {
        $this->model = $model->newQuery();
    }

    public function all()
    {
        return $this->model->all();
    }

    public function lists($column, $key = null)
    {
        return $this->model->pluck($column, $key);
    }

    public function paginate($limit = null, $columns = ['*'])
    {
        $limit = is_null($limit) ? config('setting.paginate') : $limit;

        return $this->model->paginate($limit, $columns);
    }

    public function find($id, $columns = ['*'])
    {
        try {
            return $this->model->findOrFail($id, $columns);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Model Not Found", 1);
        }

    }

    public function whereIn($column, $values)
    {
        $values = is_array($values) ? $values : [$values];
        $this->model->whereIn($column, $values);

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        $this->model->orWhere($column, $operator, $value);

        return $this;
    }

    public function where($conditions, $operator = null, $value = null)
    {
        $this->model->where($conditions, $operator, $value);

        return $this;
    }

    public function create($input)
    {
        return $this->model->insertGetId($input);
    }

    public function firstOrCreate($input)
    {
        return $this->model->firstOrCreate($input);
    }

    public function multiCreate($input)
    {
        return $this->model->insert($input);
    }

    public function update($id, $input)
    {
        $model = $this->model->findOrFail($id);
        $model->fill($input);
        $model->save();

        return $model;
    }

    public function uploadImage($file, $path)
    {
        if (!$file) {
            return null;
        }

        $fileName = uniqid(rand(), true) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($path), $fileName);

        return $fileName;
    }

    public function uploadImageUrl($url, $path)
    {
        if (!$url) {
            return null;
        }

        try {
            $name = explode('.', basename($url));
            $ext = end($name);
            $ext = in_array($ext, ['jpg', 'png', 'gif']) ? $ext : 'gif';
            $name = uniqid(rand()) . '.' . $ext;
            $upload = file_put_contents(ltrim($path, '/') . $name, file_get_contents($url));

            return $name;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function multiUpdate($column, $value, $input)
    {
        $value = is_array($value) ? $value : [$value];

        return $this->model->whereIn($column, $value)->update($input);
    }

    public function delete($ids)
    {
        if (empty($ids)) {
            return true;
        }

        $ids = is_array($ids) ? $ids : [$ids];

        return $this->model->whereIn('id', $ids)->delete();
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->model, $method], $args);
    }
    
}
