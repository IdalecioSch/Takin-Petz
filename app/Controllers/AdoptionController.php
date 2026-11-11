<?php

namespace App\Controllers;

use App\Core\{BusinessException, Controller, Database, HttpException, Messages, Validator};
use App\Models\{Animal, Cidade, Solicitacao};
use App\Persistence\{AnimalPersistence, CidadePersistence, PessoaPersistence, SolicitacaoPersistence};
use App\Services\Notifier;
use App\Utils\{ControllerUtils, PersistenciaUtils, StringUtils};

class AdoptionController extends Controller
{
    private $AnimalPersistence;
    private $PessoaPersistence;
    private $CidadePersistence;

    /** @return SolicitacaoPersistence */
    protected function createPersistence() { return new SolicitacaoPersistence(); }

    /** @return Solicitacao */
    protected function createModel() { return new Solicitacao(); }

    /**
     */
    public function __construct()
    {
        parent::__construct();
        $this->AnimalPersistence = new AnimalPersistence();
        $this->PessoaPersistence = new PessoaPersistence();
        $this->CidadePersistence = new CidadePersistence();
    }

    /**
     * @param int $id
     * @return void
     */
    public function form($id)
    {
        $Animal = $this->getAvailableAnimal($id);
        if ($Animal) {
            $this->renderForm($Animal, []);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    public function submit($id)
    {
        $this->verifyCsrf();
        if (ControllerUtils::getPost('website') !== '') { // honeypot anti-bot
            $this->redirect('/');
        }
        $Animal = $this->getAvailableAnimal($id);
        if (!$Animal) {
            return;
        }

        $data = [
            'name' => ControllerUtils::getPost('nome'),
            'email' => StringUtils::normalizeEmail(ControllerUtils::getPost('email')),
            'phone' => ControllerUtils::getPost('telefone'),
            'uf' => ControllerUtils::getPost('uf'),
            'housing' => ControllerUtils::getPost('moradia'),
            'other_pets' => ControllerUtils::getPost('outros_animais'),
            'motivation' => ControllerUtils::getPost('motivacao'),
        ];
        $Cidade = $this->resolveCity($data['uf']);
        $phoneDigits = strlen(StringUtils::digits($data['phone']));

        $Validator = new Validator();
        $Validator
            ->rule(StringUtils::length($data['name']) >= 5 && StringUtils::length($data['name']) <= 160 && strpos($data['name'], ' ') !== false, Messages::V_FULL_NAME)
            ->email($data['email'], Messages::V_EMAIL)
            ->rule($phoneDigits >= 10 && $phoneDigits <= 11, Messages::V_PHONE)
            ->inDomain('estados', $data['uf'], Messages::V_STATE)
            ->rule($Cidade !== null, Messages::V_CITY)
            ->inDomain('tipos_moradia', $data['housing'], Messages::V_HOUSING)
            ->rule(in_array($data['other_pets'], ['sim', 'nao'], true), Messages::V_OTHER_PETS)
            ->rule(StringUtils::length($data['motivation']) >= 20, Messages::get(Messages::V_MOTIVATION, 20))
            ->rule(ControllerUtils::isChecked('maior_18'), Messages::V_ADULT)
            ->rule(ControllerUtils::isChecked('termo'), Messages::V_TERMS)
            ->rule(ControllerUtils::isChecked('lgpd'), Messages::V_LGPD_ADOPTION);
        if ($Validator->failed()) {
            $this->renderForm($Animal, $Validator->getErrors());
            return;
        }

        try {
            $Solicitacao = Database::transaction(function () use ($Animal, $data, $Cidade) {
                $Pessoa = $this->PessoaPersistence->register($data['name'], $data['email'], $data['phone'], $Cidade);
                // RN10: aviso amigável (o índice único parcial garante mesmo em corrida)
                if ($this->Persistence->hasPending($Animal, $Pessoa)) {
                    throw new BusinessException(Messages::DUPLICATE_REQUEST);
                }
                return $this->Persistence->open($Animal, $Pessoa, [
                    'housing' => $data['housing'],
                    'has_other_pets' => $data['other_pets'] === 'sim',
                    'motivation' => $data['motivation'],
                ]);
            });
        } catch (BusinessException $Exception) {
            $this->renderForm($Animal, [$Exception->getMessage()]);
            return;
        } catch (\PDOException $Exception) {
            if (!PersistenciaUtils::isUniqueViolation($Exception)) {
                throw $Exception;
            }
            $this->renderForm($Animal, [Messages::DUPLICATE_REQUEST]);
            return;
        }

        // RN18/RN20: e-mails depois de salvar; falha de envio não desfaz o pedido
        (new Notifier())->requestReceived($Solicitacao);
        $this->redirect('/animais/' . $id . '/adotar/sucesso');
    }

    /**
     * @param int $id
     * @return void
     */
    public function success($id)
    {
        $Animal = $this->AnimalPersistence->getModelByIdOrFail($id);
        $this->view('adoption/success', ['title' => 'Pedido enviado', 'active' => 'animais', 'Animal' => $Animal]);
    }

    /**
     * @param Animal $Animal
     * @param array $errors
     * @return void
     */
    private function renderForm(Animal $Animal, $errors)
    {
        $this->view('adoption/form', ['title' => 'Pedido de adoção: ' . $Animal->nome, 'active' => 'animais', 'Animal' => $Animal, 'errors' => $errors]);
    }

    /** Cidade escolhida no select; só aceita digitação se a lista da UF não existir (IBGE fora do ar). */
    private function resolveCity($uf)
    {
        $cityId = ControllerUtils::getPostInt('city_id');
        if ($cityId > 0) {
            $Cidade = $this->CidadePersistence->getModelById($cityId);
            return ($Cidade && $Cidade->uf === $uf) ? $Cidade : null;
        }
        $cityName = ControllerUtils::getPost('city_name');
        if ($cityName !== '' && StringUtils::length($cityName) <= 100 && $this->CidadePersistence->countByState($uf) === 0) {
            return $this->CidadePersistence->obtain($cityName, $uf);
        }
        return null;
    }

    /**
     * @param int $id
     * @return Animal|null
     * @throws HttpException
     */
    private function getAvailableAnimal($id)
    {
        $Animal = $this->AnimalPersistence->getModelById($id);
        if (!$Animal) {
            throw new HttpException(404, Messages::ERROR_ANIMAL_NOT_FOUND);
        }
        if (!$Animal->isAvailable()) { // RN03
            $this->view('catalog/unavailable', ['title' => 'Indisponível', 'active' => 'animais'], 'site', 404);
            return null;
        }
        return $Animal;
    }
}
