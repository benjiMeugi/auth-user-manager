<?php

namespace App\Http\Controllers\Repository;

use App\Http\Controllers\Traits\ApiResponse;
use App\Http\Controllers\Traits\ModelRequest;
use App\Models\Contracts\IModel;
use BenjiMeugi\Parser;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Repository implements IRepository
{
    use ApiResponse, ModelRequest;

    /**
     * @var Model
     */
    private $model;

    /**
     * @param string|null $model
     */
    public function __construct($model = null)
    {
        if ($model) {
            $this->model = new $model;
        }
    }

    /**
     * Set new instance of model
     * 
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = new $model;
        return $this;
    }

    /**
     * get cuurent model instance
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * get current model rules
     */
    public function rules($multiple = false)
    {
        return $this->model->rules($multiple);
    }

    /**
     * get current model update_rules
     */
    public function update_rules()
    {
        return $this->model->update_rules();
    }

    /**
     * apply request query filters
     * 
     * ordering order=asc/desc by=column
     * 
     * where_column 
     * 
     * whereLike_column / whereLike_relation__column
     * 
     * orWhere_column
     * 
     * orWhereLike_column
     * 
     * with_relation or with_relation1__relation2
     * 
     * withCount_relation
     *  
     * @param Request $request
     * @return Builder $query
     */
    public function parse_filters(Request $request)
    {
        $query = is_string($request->get('query_')) ? json_decode($request->get('query_')) : $request->get('query_');
        return (new Parser())->parse_filters((array)$query, $this->model);
    }


    /**
     * @param Request $request
     * @param int $id
     */
    public function show($request, $id, $resource = null)
    {
        $query = is_string($request->get('query_')) ? json_decode($request->get('query_')) : $request->get('query_');
        $data = (new Parser())->parse_filters((array)$query, $this->model)->where($this->model->getKeyName(), $id)->first();
        return $this->respondOk(is_null($resource) ? $data : $resource::call($data, $request->all()));
    }

    /**
     * Store resource
     * 
     * @param Request|array $request
     */
    public function store($request)
    {
        return (new Parser())->store($request->all(), $this->model);
    }

    /**
     * Update resource
     *  
     * @param Request $request
     * @param int $id
     */
    public function update($request, $id)
    {
        return (new Parser())->update($request->all(), $this->model, $id);
    }

    /**
     * Delete ressource
     * 
     * @param int|array $id
     */
    public function delete($id, $request)
    {
        $data = (new Parser())->delete($request, $this->model, $id);
        if ($data instanceof Exception) {
            return $this->respondError($data);
        } else {
            return $this->respondOk($data);
        }
    }


    /**
     * 
     * @param Request $request
     * @param array $rules
     * @return true|array
     */
    public function check($request, $rules = [], $id = null)
    {
        $requestInput = is_array($request) ? $request : $request->all();
        $requestInput = is_null($id) ? $requestInput : array_merge($requestInput, [$this->model->getKeyName() => $id]);

        $rules = $this->getRules($rules, $id);

        // if (empty($rules)) {
        //     $rules = $this->repository->rules();
        // }

        $validator = Validator::make($requestInput, $rules);
        if ($validator->fails()) {
            return $this->respondBadRequest($validator->errors()->messages());
        }
        return true;
    }

    public function getRules($rules, $id)
    {
        $response = [];
        foreach ($rules as $key => $rule) {
            $rule = is_array($rule) ? $rule : explode('|', $rule);
            if (in_array(IModel::IGNORE_RULE, $rule)) {
                $rule = array_merge(
                    $rule,
                    [Rule::unique($this->model->getTable(), $key)->ignore($id)]
                );

                unset($rule[array_search(IModel::IGNORE_RULE, $rule)]);
            }
            $response[$key] = $rule;
        }
        return $response;
    }
}
