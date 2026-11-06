<?php

namespace App\Http\Controllers\Repository;

use Illuminate\Http\Request;

interface IRepository
{
    /**
     * Définit l'instance du modèle Eloquent manipulé par le repository.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return $this
     */
    public function setModel($model);

    /**
     * Retourne l'instance courante du modèle Eloquent.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel();

    /**
     * Retourne les règles de validation pour la création.
     *
     * @param  bool  $multiple
     * @return array
     */
    public function rules($multiple = false);

    /**
     * Retourne les règles de validation pour la mise à jour.
     *
     * @return array
     */
    public function update_rules();

    /**
     * Applique les filtres de requête (query_) via BenjiMeugi\Parser.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function parse_filters(Request $request);

    /**
     * Récupère une ressource unique par son identifiant.
     *
     * @param  Request  $request
     * @param  int|string  $id
     * @param  mixed  $resource
     */
    public function show($request, $id, $resource = null);

    /**
     * Crée une nouvelle ressource (et ses relations HasOne/HasMany).
     *
     * @param  Request|array  $request
     */
    public function store($request);

    /**
     * Met à jour une ressource existante par son identifiant.
     *
     * @param  Request|array  $request
     * @param  int|string  $id
     */
    public function update($request, $id);

    /**
     * Supprime une ressource existante.
     *
     * @param  mixed  $request
     * @param  mixed  $id
     */
    public function delete($request, $id);

    /**
     * Valide les données de la requête selon les règles du modèle.
     *
     * @param  Request|array  $request
     * @param  array  $rules
     * @param  int|string|null  $id
     * @return true|\Illuminate\Http\JsonResponse|array
     */
    public function check($request, $rules = [], $id = null);

    /**
     * Transforme les règles du modèle (notamment IModel::IGNORE_RULE).
     *
     * @param  array  $rules
     * @param  int|string|null  $id
     * @return array
     */
    public function getRules($rules, $id);
}
