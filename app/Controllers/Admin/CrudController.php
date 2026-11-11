<?php

namespace App\Controllers\Admin;

use App\Core\{Messages, Model, Validator};
use App\Utils\{ControllerUtils, PersistenciaUtils, StringUtils};

/**
 * CRUD genérico dos cadastros (raças, cores, vacinas...).
 * A filha só informa título, campos e colunas da listagem.
 */
abstract class CrudController extends AdminController
{
    /** @return string */
    abstract protected function getRoute();

    /** @return string */
    abstract protected function getTitle();

    /** @return string */
    abstract protected function getSingular();

    /** @return array  [['name','label','type'(text|select),'required','max','options','empty']] */
    abstract protected function getFields();

    /** @return array  [['field','label']] */
    abstract protected function getColumns();

    /**
     * @return void
     */
    public function index()
    {
        $this->adminView('admin/crud/index', $this->getViewData() + [
            'rows' => $this->Persistence->getAllModel(),
            'columns' => $this->getColumns(),
        ]);
    }

    /**
     * @return void
     */
    public function create()
    {
        $this->renderForm($this->Model, []);
    }

    /**
     * @return void
     */
    public function store()
    {
        $this->verifyCsrf();
        $this->persist($this->Model);
    }

    /**
     * @param int $id
     * @return void
     */
    public function edit($id)
    {
        $this->renderForm($this->Persistence->getModelByIdOrFail($id), []);
    }

    /**
     * @param int $id
     * @return void
     */
    public function update($id)
    {
        $this->verifyCsrf();
        $this->persist($this->Persistence->getModelByIdOrFail($id));
    }

    /**
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        $this->verifyCsrf();
        $Model = $this->Persistence->getModelByIdOrFail($id);
        try {
            $this->Persistence->delete($Model);
            $this->flash('ok', Messages::get(Messages::REGISTRY_DELETED, $this->getSingular()));
        } catch (\PDOException $Exception) {
            if (!PersistenciaUtils::isForeignKeyViolation($Exception)) {
                throw $Exception;
            }
            $this->flash('erro', Messages::REGISTRY_IN_USE);
        }
        $this->redirect($this->getRoute());
    }

    /**
     * @param Model $Model
     * @return void
     * @throws \PDOException
     */
    private function persist(Model $Model)
    {
        $Validator = new Validator();
        foreach ($this->getFields() as $field) {
            $value = ControllerUtils::getPost($field['name']);
            $label = $field['label'];
            $Validator->rule(empty($field['required']) || $value !== '', Messages::get(Messages::V_FIELD_REQUIRED, $label));
            if (!empty($field['max'])) {
                $Validator->rule(StringUtils::length($value) <= $field['max'], Messages::get(Messages::V_FIELD_TOO_LONG, $label, $field['max']));
            }
            if ($field['type'] === 'select' && $value !== '') {
                $Validator->rule(isset($field['options'][$value]), Messages::get(Messages::V_FIELD_INVALID, $label));
            }
            $Model->fill([$field['name'] => $value === '' ? null : $value]);
        }
        if ($Validator->failed()) {
            $this->renderForm($Model, $Validator->getErrors());
            return;
        }
        try {
            $this->Persistence->save($Model);
        } catch (\PDOException $Exception) {
            if (!PersistenciaUtils::isUniqueViolation($Exception)) {
                throw $Exception;
            }
            $this->renderForm($Model, [Messages::REGISTRY_DUPLICATE]);
            return;
        }
        $this->flash('ok', Messages::get(Messages::REGISTRY_SAVED, $this->getSingular()));
        $this->redirect($this->getRoute());
    }

    /**
     * @param Model $Model
     * @param array $errors
     * @return void
     */
    private function renderForm(Model $Model, $errors)
    {
        $this->adminView('admin/crud/form', $this->getViewData() + [
            'Model' => $Model,
            'fields' => $this->getFields(),
            'errors' => $errors,
        ]);
    }

    /** @return array */
    private function getViewData()
    {
        return ['title' => $this->getTitle(), 'active' => $this->getRoute(), 'route' => $this->getRoute(), 'singular' => $this->getSingular()];
    }
}
