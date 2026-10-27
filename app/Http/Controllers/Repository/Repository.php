<?php

namespace App\Http\Controllers\Repository;

use App\Http\Controllers\Traits\ApiResponse;
use BenjiMeugi\Contracts\IModel;
use BenjiMeugi\Parser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Repository implements IRepository
{
    use ApiResponse;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @param string|null $model
     */
    public function __construct($model = null)
    {
        if ($model instanceof Model) {
            $this->model = $model;
        } elseif ($model) {
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
        $query = is_string($request->input('query_')) ? json_decode($request->input('query_')) : $request->input('query_');
        return (new Parser())->parse_filters((array)$query, $this->model);
    }


    /**
     * @param Request $request
     * @param int $id
     */
    public function show($request, $id, $resource = null)
    {
        $query = is_string($request->input('query_')) ? json_decode($request->input('query_')) : $request->input('query_');
        $data = (new Parser())->parse_filters((array)$query, $this->model)->where($this->model->getKeyName(), $id)->first();
        return $this->respondOk(is_null($resource) ? $data : $resource::call($data, $request->all()));
    }

    /**
     * Store resource
     *
     * @param Request $request
     */
    public function store($request)
    {
        return (new Parser())->store($this->getRequestInput($request), $this->model);
    }

    /**
     * Update resource
     *
     * @param Request $request
     * @param int $id
     */
    public function update($request, $id)
    {
        return (new Parser())->update($this->getRequestInput($request), $this->model, $id);
    }

    /**
     * Delete ressource
     * @param Request $request
     * @param int|array $id
     */
    public function delete($request, $id)
    {
        if ($this->model::find($id) === null) {
            return $this->respondNotFound($id);
        }

        return (new Parser())->delete($request->all(), $this->model, $id);
    }


    /**
     *
     * @param Request $request
     * @param array $rules
     * @return true|array
     */
    public function check($request, $rules = [], $id = null)
    {
        $requestInput = $this->getRequestInput($request);
        $requestInput = is_null($id) ? $requestInput : array_merge($requestInput, [$this->model->getKeyName() => $id]);

        $rules = $this->getRules($rules, $id);

        if ($id !== null) {
            $rules[$this->model->getKeyName()] = [
                'exists:' . $this->model->getTable() . ',' . $this->model->getKeyName(),
            ];
        }

        $validator = Validator::make($requestInput, $rules);
        if ($validator->fails()) {
            return $this->respondBadRequest($validator->errors()->messages());
        }
        return true;
    }

    private function getRequestInput($request): array
    {
        if (is_array($request)) {
            return $request;
        }

        $input = $request->all();
        if ($input !== []) {
            return $input;
        }

        $requestInput = $request->request->all();
        if ($requestInput !== []) {
            return $requestInput;
        }

        return $_POST !== [] ? $_POST : $request->json()->all();
    }

    /**
     * @param array $rules
     * @param int|null $id
     * @return array
     */
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
