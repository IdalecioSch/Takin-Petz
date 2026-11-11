<?php

namespace App\Controllers\Admin;

use App\Core\{Domain, Messages, Validator};
use App\Models\{Animal, Consulta, Dieta, VacinaAplicada};
use App\Persistence\{AnimalPersistence, ConsultaPersistence, DietaPersistence, SintomaPersistence, VacinaAplicadaPersistence,
    VacinaPersistence, VeterinarioPersistence};
use App\Utils\{ArrayUtils, ControllerUtils, DateUtils, ModelUtils, StringUtils};

/** Vacinas, dieta e histórico de consultas de cada animal. */
class AnimalHealthController extends AdminController
{
    private $VaccinationPersistence;
    private $DietPersistence;
    private $ConsultationPersistence;

    /** @return AnimalPersistence */
    protected function createPersistence() { return new AnimalPersistence(); }

    /** @return Animal */
    protected function createModel() { return new Animal(); }

    /**
     */
    public function __construct()
    {
        parent::__construct();
        $this->VaccinationPersistence = new VacinaAplicadaPersistence();
        $this->DietPersistence = new DietaPersistence();
        $this->ConsultationPersistence = new ConsultaPersistence();
    }

    /**
     * @param int $id
     * @return void
     */
    public function index($id)
    {
        $this->renderPage($this->Persistence->getModelByIdOrFail($id), []);
    }

    /**
     * @param int $id
     * @return void
     */
    public function storeVaccination($id)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $data = [
            'vacina_id' => ControllerUtils::getPostInt('vacina_id'),
            'aplicada_em' => ControllerUtils::getPost('aplicada_em'),
            'proxima_dose_em' => StringUtils::emptyToNull(ControllerUtils::getPost('proxima_dose_em')),
            'veterinario_id' => ControllerUtils::getPostInt('veterinario_id') ?: null,
            'observacoes' => StringUtils::emptyToNull(ControllerUtils::getPost('observacoes')),
        ];
        $Validator = new Validator();
        $Validator
            ->rule(ModelUtils::findById((new VacinaPersistence())->getBySpecies($Animal->especie), $data['vacina_id']) !== null, Messages::V_VACCINE)
            ->rule(DateUtils::isValid($data['aplicada_em']), Messages::get(Messages::V_DATE_INVALID, 'Aplicada em'))
            ->addErrors($this->validateOptionalDate($data['proxima_dose_em'], 'Próxima dose', $data['aplicada_em'], 'Aplicada em'))
            ->addErrors($this->validateVeterinarian($data['veterinario_id']));
        if ($Validator->failed()) {
            $this->renderPage($Animal, $Validator->getErrors(), 'vaccination');
            return;
        }
        $this->VaccinationPersistence->save(new VacinaAplicada($data + ['animal_id' => $Animal->getId()]));
        $this->done($Animal, Messages::VACCINATION_SAVED);
    }

    /**
     * @param int $id
     * @param int $recordId
     * @return void
     */
    public function deleteVaccination($id, $recordId)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $Record = $this->VaccinationPersistence->getFirstModel(['t.id = :id', 't.animal_id = :animal'], ['id' => $recordId, 'animal' => $Animal->getId()]);
        if ($Record) {
            $this->VaccinationPersistence->delete($Record);
        }
        $this->done($Animal, Messages::VACCINATION_DELETED);
    }

    /**
     * @param int $id
     * @return void
     */
    public function storeDiet($id)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $data = [
            'tipo_alimentacao' => ControllerUtils::getPost('tipo_alimentacao'),
            'descricao' => StringUtils::emptyToNull(ControllerUtils::getPost('descricao')),
            'refeicoes_por_dia' => ControllerUtils::getPostInt('refeicoes_por_dia') ?: null,
            'restricoes' => StringUtils::emptyToNull(ControllerUtils::getPost('restricoes')),
            'iniciada_em' => ControllerUtils::getPost('iniciada_em'),
        ];
        $Validator = new Validator();
        $Validator
            ->inDomain('tipos_alimentacao', $data['tipo_alimentacao'], Messages::V_DIET_TYPE)
            ->rule($data['refeicoes_por_dia'] === null || ($data['refeicoes_por_dia'] >= 1 && $data['refeicoes_por_dia'] <= 12), Messages::V_MEALS)
            ->rule(DateUtils::isValid($data['iniciada_em']), Messages::get(Messages::V_DATE_INVALID, 'Início'));
        if ($Validator->failed()) {
            $this->renderPage($Animal, $Validator->getErrors(), 'diet');
            return;
        }
        $this->DietPersistence->save(new Dieta($data + ['animal_id' => $Animal->getId()]));
        $this->done($Animal, Messages::DIET_SAVED);
    }

    /**
     * @param int $id
     * @param int $recordId
     * @return void
     */
    public function endDiet($id, $recordId)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $Diet = $this->DietPersistence->getFirstModel(['t.id = :id', 't.animal_id = :animal'], ['id' => $recordId, 'animal' => $Animal->getId()]);
        if ($Diet && $Diet->isActive()) {
            $this->DietPersistence->end($Diet, DateUtils::today());
        }
        $this->done($Animal, Messages::DIET_ENDED);
    }

    /**
     * @param int $id
     * @param int $recordId
     * @return void
     */
    public function deleteDiet($id, $recordId)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $Diet = $this->DietPersistence->getFirstModel(['t.id = :id', 't.animal_id = :animal'], ['id' => $recordId, 'animal' => $Animal->getId()]);
        if ($Diet) {
            $this->DietPersistence->delete($Diet);
        }
        $this->done($Animal, Messages::DIET_DELETED);
    }

    /**
     * @param int $id
     * @return void
     */
    public function storeConsultation($id)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $data = [
            'consultada_em' => ControllerUtils::getPost('consultada_em'),
            'veterinario_id' => ControllerUtils::getPostInt('veterinario_id') ?: null,
            'motivo' => ControllerUtils::getPost('motivo'),
            'diagnostico' => StringUtils::emptyToNull(ControllerUtils::getPost('diagnostico')),
            'tratamento' => StringUtils::emptyToNull(ControllerUtils::getPost('tratamento')),
            'observacoes' => StringUtils::emptyToNull(ControllerUtils::getPost('observacoes')),
            'retorno_em' => StringUtils::emptyToNull(ControllerUtils::getPost('retorno_em')),
        ];
        $symptomIds = ControllerUtils::getPostIntList('sintomas');
        $validSymptoms = ModelUtils::getIds((new SintomaPersistence())->getAllModel());

        $Validator = new Validator();
        $Validator
            ->rule(DateUtils::isValid($data['consultada_em']), Messages::get(Messages::V_DATE_INVALID, 'Data da consulta'))
            ->rule($data['motivo'] !== '' && StringUtils::length($data['motivo']) <= 200, Messages::get(Messages::V_REASON, 200))
            ->rule(count(array_diff($symptomIds, $validSymptoms)) === 0, Messages::get(Messages::V_FIELD_INVALID, 'Sintomas'))
            ->addErrors($this->validateOptionalDate($data['retorno_em'], 'Retorno', $data['consultada_em'], 'Data da consulta'))
            ->addErrors($this->validateVeterinarian($data['veterinario_id']));
        if ($Validator->failed()) {
            $this->renderPage($Animal, $Validator->getErrors(), 'consultation');
            return;
        }
        $this->ConsultationPersistence->saveWithSymptoms(new Consulta($data + ['animal_id' => $Animal->getId()]), $symptomIds);
        $this->done($Animal, Messages::CONSULTATION_SAVED);
    }

    /**
     * @param int $id
     * @param int $recordId
     * @return void
     */
    public function deleteConsultation($id, $recordId)
    {
        $this->verifyCsrf();
        $Animal = $this->Persistence->getModelByIdOrFail($id);
        $Consultation = $this->ConsultationPersistence->getFirstModel(['t.id = :id', 't.animal_id = :animal'], ['id' => $recordId, 'animal' => $Animal->getId()]);
        if ($Consultation) {
            $this->ConsultationPersistence->delete($Consultation);
        }
        $this->done($Animal, Messages::CONSULTATION_DELETED);
    }

    /**
     * @param Animal $Animal
     * @param string $message
     * @return void
     */
    private function done(Animal $Animal, $message)
    {
        $this->flash('ok', $message);
        $this->redirect('/admin/animais/' . $Animal->getId() . '/saude');
    }

    /**
     * @param string|null $date
     * @param string $label
     * @param string $minDate
     * @param string $minLabel
     * @return array
     */
    private function validateOptionalDate($date, $label, $minDate, $minLabel)
    {
        if ($date === null) {
            return [];
        }
        if (!DateUtils::isValid($date)) {
            return [Messages::get(Messages::V_DATE_INVALID, $label)];
        }
        return DateUtils::isValid($minDate) && $date < $minDate ? [Messages::get(Messages::V_DATE_ORDER, $label, $minLabel)] : [];
    }

    /**
     * @param int|null $veterinarianId
     * @return array
     */
    private function validateVeterinarian($veterinarianId)
    {
        if (!$veterinarianId) {
            return [];
        }
        return (new VeterinarioPersistence())->getModelById($veterinarianId) ? [] : [Messages::V_VETERINARIAN];
    }

    /**
     * @param Animal $Animal
     * @param array $errors
     * @param string $openForm
     * @return void
     */
    private function renderPage(Animal $Animal, $errors, $openForm = '')
    {
        $this->adminView('admin/animals/health', [
            'title' => 'Saúde de ' . $Animal->nome,
            'active' => 'animais',
            'Animal' => $Animal,
            'errors' => $errors,
            'openForm' => $openForm,
            'vaccinations' => $this->VaccinationPersistence->getByAnimal($Animal->getId()),
            'diets' => $this->DietPersistence->getByAnimal($Animal->getId()),
            'consultations' => $this->ConsultationPersistence->getByAnimal($Animal->getId()),
            'vaccines' => (new VacinaPersistence())->getBySpecies($Animal->especie),
            'veterinarians' => (new VeterinarioPersistence())->getAllModel([], [], 't.nome'),
            'symptoms' => (new SintomaPersistence())->getAllModel(),
        ]);
    }
}
