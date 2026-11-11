<?php

namespace App\Controllers\Admin;

use App\Core\{BusinessException, Config, Database, Domain, Messages, Validator};
use App\Models\Animal;
use App\Persistence\{AnimalPersistence, CorPersistence, RacaPersistence};
use App\Services\PhotoUploader;
use App\Utils\{ArrayUtils, ControllerUtils, ModelUtils, StringUtils};

class AnimalController extends AdminController
{
    /** @return AnimalPersistence */
    protected function createPersistence() { return new AnimalPersistence(); }

    /** @return Animal */
    protected function createModel() { return new Animal(); }

    /**
     * @return void
     */
    public function index()
    {
        $search = ControllerUtils::getQuery('q');
        $status = ControllerUtils::getQuery('status');
        $this->adminView('admin/animals/index', [
            'title' => 'Animais',
            'active' => 'animais',
            'search' => $search,
            'status' => $status,
            'animals' => $this->Persistence->getForAdmin($search, $status),
        ]);
    }

    /**
     * @return void
     */
    public function create()
    {
        $this->Model->status = 'disponivel';
        $this->renderForm($this->Model, []);
    }

    /**
     * @return void
     */
    public function store()
    {
        $this->verifyCsrf();
        $errors = $this->persist($this->Model);
        $errors ? $this->renderForm($this->Model, $errors) : $this->finish();
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
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $errors = $this->persist($Animal);
        $errors ? $this->renderForm($Animal, $errors) : $this->finish();
    }

    /**
     * @param int $id
     * @return void
     */
    public function changeStatus($id)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $status = ControllerUtils::getPost('status');
        if (Domain::isValid('status_animal', $status)) {
            $this->Persistence->updateStatus($Animal, $status);
            $this->flash('ok', Messages::get(Messages::ANIMAL_STATUS_CHANGED, $Animal->nome, $Animal->getStatusLabel()));
        }
        $this->redirect('/admin/animais');
    }

    /**
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        try {
            (new PhotoUploader())->remove($this->Persistence->deleteSafely($Animal));
            $this->flash('ok', Messages::get(Messages::ANIMAL_DELETED, $Animal->nome));
        } catch (BusinessException $Exception) {
            $this->flash('erro', $Exception->getMessage());
        }
        $this->redirect('/admin/animais');
    }

    /** @return void */
    private function finish()
    {
        $this->flash('ok', Messages::ANIMAL_SAVED);
        $this->redirect('/admin/animais');
    }

    /** Valida e grava animal + fotos. Retorna a lista de erros (vazia = sucesso). */
    private function persist(Animal $Animal)
    {
        $data = [
            'nome' => ControllerUtils::getPost('nome'),
            'especie' => ControllerUtils::getPost('especie'),
            'sexo' => ControllerUtils::getPost('sexo'),
            'porte' => ControllerUtils::getPost('porte'),
            'faixa_etaria' => ControllerUtils::getPost('faixa_etaria'),
            'status' => ControllerUtils::getPost('status') ?: 'disponivel',
            'raca_id' => ControllerUtils::getPostInt('raca_id') ?: null,
            'cor_id' => ControllerUtils::getPostInt('cor_id') ?: null,
            'descricao' => StringUtils::emptyToNull(ControllerUtils::getPost('descricao')),
            'temperamento' => StringUtils::emptyToNull(ControllerUtils::getPost('temperamento')),
            'castrado' => ControllerUtils::isChecked('castrado'),
            'vacinado' => ControllerUtils::isChecked('vacinado'),
            'vermifugado' => ControllerUtils::isChecked('vermifugado'),
        ];
        $Animal->fill($data);

        $Validator = new Validator();
        $Validator
            ->rule($data['nome'] !== '' && StringUtils::length($data['nome']) <= 80, Messages::get(Messages::V_ANIMAL_NAME, 80))
            ->inDomain('especies', $data['especie'], Messages::V_SPECIES)
            ->inDomain('sexos', $data['sexo'], Messages::V_SEX)
            ->inDomain('portes', $data['porte'], Messages::V_SIZE)
            ->inDomain('faixas_etarias', $data['faixa_etaria'], Messages::V_AGE_GROUP)
            ->inDomain('status_animal', $data['status'], Messages::V_STATUS);
        if ($data['raca_id']) {
            $Raca = (new RacaPersistence())->getModelById($data['raca_id']);
            $Validator->rule($Raca && $Raca->especie === $data['especie'], Messages::V_BREED);
        }
        if ($data['cor_id']) {
            $Validator->rule((new CorPersistence())->getModelById($data['cor_id']) !== null, Messages::V_COLOR);
        }

        // Fotos: RN01 (mín. 1) e RN17 (máx. 5, 3 MB, JPG/PNG/WEBP)
        $Uploader = new PhotoUploader();
        $newFiles = ControllerUtils::getFiles('photos');
        $currentIds = $Animal->isNew() ? [] : ModelUtils::getIds($Animal->getPhotos());
        $removeIds = array_values(array_intersect(ControllerUtils::getPostIntList('remove'), $currentIds));
        $total = count($currentIds) - count($removeIds) + count($newFiles);
        $max = (int)Config::get('upload.max_fotos');
        $Validator
            ->rule($total >= 1, Messages::V_PHOTOS_MIN)
            ->rule($total <= $max, Messages::get(Messages::V_PHOTOS_MAX, $max))
            ->addErrors($Uploader->validate($newFiles));
        if ($Validator->failed()) {
            return $Validator->getErrors();
        }

        $stored = [];
        try {
            $stored = $Uploader->store($newFiles);
            $removedFiles = Database::transaction(function () use ($Animal, $removeIds, $stored) {
                $this->Persistence->save($Animal);
                return $this->Persistence->syncPhotos($Animal, $removeIds, $stored, ControllerUtils::getPostInt('principal'));
            });
        } catch (\Throwable $Exception) {
            $Uploader->remove($stored);
            error_log('[animal] ' . $Exception->getMessage());
            return [Messages::get(Messages::SAVE_FAILED, Config::get('debug') ? $Exception->getMessage() : Messages::TRY_AGAIN)];
        }
        $Uploader->remove($removedFiles); // só depois do commit
        return [];
    }

    /**
     * @param Animal $Animal
     * @param array $errors
     * @return void
     */
    private function renderForm(Animal $Animal, $errors)
    {
        $this->adminView('admin/animals/form', [
            'title' => $Animal->isNew() ? 'Cadastrar animal' : 'Editar ' . $Animal->nome,
            'active' => 'animais',
            'Animal' => $Animal,
            'errors' => $errors,
            'breeds' => (new RacaPersistence())->getAllModel(),
            'colors' => (new CorPersistence())->getAllModel(),
        ]);
    }
}
