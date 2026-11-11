<?php

namespace App\Core;

/**
 * Todas as mensagens do sistema ficam aqui.
 * Uso: Messages::get(Messages::ANIMAL_SAVED)  /  Messages::get(Messages::ANIMAL_DELETED, $name)
 */
class Messages
{
    const SITE_NAME = 'Takin Petz';
    const SITE_SLOGAN = 'Adote, acolha, transforme uma vida';

    // ---------- erros ----------
    const ERROR_NOT_FOUND = 'Página não encontrada.';
    const ERROR_RECORD_NOT_FOUND = 'Registro não encontrado.';
    const ERROR_CSRF = 'Sessão expirada ou requisição inválida. Volte e tente novamente.';
    const ERROR_INTERNAL = 'Ocorreu um erro interno. Tente novamente em instantes.';
    const ERROR_CONFIG_MISSING = 'Configuração ausente: copie config/config.example.php para config/config.php.';
    const ERROR_ANIMAL_NOT_FOUND = 'Animal não encontrado.';
    const ERROR_ANIMAL_UNAVAILABLE = 'Este animal não está disponível para adoção.';

    // ---------- validação genérica ----------
    const V_FIELD_REQUIRED = 'Preencha o campo "%s".';
    const V_FIELD_TOO_LONG = 'O campo "%s" aceita até %d caracteres.';
    const V_FIELD_INVALID = 'Valor inválido no campo "%s".';
    const V_DATE_INVALID = 'Informe uma data válida em "%s".';
    const V_DATE_ORDER = '"%s" não pode ser anterior a "%s".';

    // ---------- validação: pedido de adoção / contato ----------
    const V_FULL_NAME = 'Informe seu nome completo.';
    const V_NAME = 'Informe seu nome.';
    const V_EMAIL = 'Informe um e-mail válido.';
    const V_PHONE = 'Informe um telefone válido com DDD.';
    const V_STATE = 'Selecione o estado.';
    const V_CITY = 'Selecione sua cidade.';
    const V_HOUSING = 'Selecione o tipo de moradia.';
    const V_OTHER_PETS = 'Informe se você possui outros animais.';
    const V_MOTIVATION = 'Conte um pouco mais sobre sua motivação (mínimo de %d caracteres).';
    const V_ADULT = 'É necessário declarar que você tem mais de 18 anos.';
    const V_TERMS = 'É necessário aceitar o termo de responsabilidade.';
    const V_LGPD_ADOPTION = 'É necessário concordar com o uso dos seus dados para esta finalidade.';
    const V_LGPD_CONTACT = 'É necessário concordar com o uso dos seus dados para responder sua mensagem.';
    const V_MESSAGE_LENGTH = 'Escreva uma mensagem (entre %d e %d caracteres).';

    // ---------- validação: animal e fotos ----------
    const V_ANIMAL_NAME = 'Informe o nome (até %d caracteres).';
    const V_SPECIES = 'Selecione a espécie.';
    const V_SEX = 'Selecione o sexo.';
    const V_SIZE = 'Selecione o porte.';
    const V_AGE_GROUP = 'Selecione a faixa de idade.';
    const V_STATUS = 'Status inválido.';
    const V_BREED = 'A raça escolhida não pertence à espécie selecionada.';
    const V_COLOR = 'Cor inválida.';
    const V_PHOTOS_MIN = 'O animal precisa ter pelo menos uma foto.';
    const V_PHOTOS_MAX = 'Máximo de %d fotos por animal.';
    const V_PHOTO_TOO_LARGE = 'A foto "%s" é grande demais.';
    const V_PHOTO_LIMIT = 'A foto "%s" passa do limite de %s MB.';
    const V_PHOTO_TYPE = 'A foto "%s" precisa ser JPG, PNG ou WEBP válida.';
    const V_PHOTO_UPLOAD = 'Falha ao enviar a foto "%s".';
    const PHOTO_SAVE_FAILED = 'Não foi possível salvar a foto (verifique a permissão de public/uploads).';

    // ---------- validação: saúde ----------
    const V_VACCINE = 'Selecione a vacina.';
    const V_DIET_TYPE = 'Selecione o tipo de alimentação.';
    const V_MEALS = 'Refeições por dia deve ficar entre 1 e 12.';
    const V_VETERINARIAN = 'Veterinário inválido.';
    const V_REASON = 'Informe o motivo da consulta (até %d caracteres).';

    // ---------- mensagens de tela (flash) ----------
    const LOGIN_FAILED = 'E-mail ou senha incorretos.';
    const SAVE_FAILED = 'Não foi possível salvar. %s';
    const TRY_AGAIN = 'Tente novamente.';
    const ANIMAL_SAVED = 'Animal salvo com sucesso.';
    const ANIMAL_STATUS_CHANGED = 'Status de %s alterado para %s.';
    const ANIMAL_DELETED = '%s foi excluído.';
    const ANIMAL_HAS_REQUESTS = '%s tem pedidos de adoção e não pode ser excluído. Marque como Indisponível para ocultar.';
    const ANIMAL_ALREADY_ADOPTED = 'Este animal já consta como adotado.';
    const DUPLICATE_REQUEST = 'Você já enviou um pedido para este animal e ele ainda está em análise.';
    const REQUEST_APPROVED = 'Pedido aprovado. O animal foi marcado como Adotado e os demais pedidos pendentes foram recusados.';
    const REQUEST_REFUSED = 'Pedido recusado.';
    const REQUEST_ALREADY_DECIDED = 'Este pedido já foi decidido.';
    const REFUSAL_REASON_TOO_LONG = 'O motivo é muito longo (máx. %d caracteres).';
    const AUTOMATIC_REFUSAL = 'Outro pedido de adoção foi aprovado para este animal.';
    const PERSONAL_DATA_DELETED = 'Os dados do solicitante foram excluídos.';
    const CONTACT_SENT = 'Mensagem enviada! Responderemos em breve.';
    const REGISTRY_SAVED = '%s salvo(a) com sucesso.';
    const REGISTRY_DELETED = '%s excluído(a).';
    const REGISTRY_IN_USE = 'Este registro está em uso e não pode ser excluído.';
    const REGISTRY_DUPLICATE = 'Já existe um registro com esse nome.';
    const VACCINATION_SAVED = 'Vacina registrada.';
    const VACCINATION_DELETED = 'Registro de vacina removido.';
    const DIET_SAVED = 'Dieta registrada.';
    const DIET_ENDED = 'Dieta encerrada.';
    const DIET_DELETED = 'Registro de dieta removido.';
    const CONSULTATION_SAVED = 'Consulta registrada.';
    const CONSULTATION_DELETED = 'Consulta removida.';

    // ---------- e-mails ----------
    const EMAIL_GREETING = 'Olá, %s!';
    const EMAIL_REQUEST_RECEIVED_SUBJECT = 'Recebemos seu pedido de adoção - %s';
    const EMAIL_REQUEST_RECEIVED_TITLE = 'Recebemos seu pedido de adoção';
    const EMAIL_REQUEST_RECEIVED_BODY = 'Recebemos seu pedido para adotar <strong>%s</strong>. Nossa equipe vai analisar e entrar em contato pelo e-mail ou telefone informado.';
    const EMAIL_REQUEST_RECEIVED_THANKS = 'Obrigado por pensar em adotar!';
    const EMAIL_NEW_REQUEST_SUBJECT = 'Novo pedido de adoção - %s';
    const EMAIL_NEW_REQUEST_TITLE = 'Novo pedido de adoção';
    const EMAIL_NEW_REQUEST_BODY = '<strong>%s</strong> pediu para adotar <strong>%s</strong>.';
    const EMAIL_NEW_REQUEST_DETAILS = 'Cidade: %s<br>Contato: %s / %s';
    const EMAIL_NEW_REQUEST_PANEL = 'Acesse o painel administrativo para analisar o pedido #%d.';
    const EMAIL_APPROVED_SUBJECT = 'Pedido aprovado - %s';
    const EMAIL_APPROVED_TITLE = 'Seu pedido foi aprovado!';
    const EMAIL_APPROVED_BODY = 'Temos uma ótima notícia: seu pedido para adotar <strong>%s</strong> foi <strong>aprovado</strong>.';
    const EMAIL_APPROVED_NEXT = 'Entraremos em contato para combinar os próximos passos.';
    const EMAIL_REFUSED_SUBJECT = 'Atualização do seu pedido - %s';
    const EMAIL_REFUSED_TITLE = 'Sobre seu pedido de adoção';
    const EMAIL_REFUSED_BODY = 'Infelizmente não foi possível seguir com seu pedido para adotar <strong>%s</strong>.';
    const EMAIL_REFUSED_REASON = 'Motivo: %s';
    const EMAIL_REFUSED_CLOSING = 'Agradecemos o interesse. Outros bichinhos esperam por um lar!';
    const EMAIL_CONTACT_SUBJECT = 'Contato pelo site - %s';
    const EMAIL_CONTACT_TITLE = 'Nova mensagem de contato';
    const EMAIL_CONTACT_BODY = '<strong>%s</strong> (%s) escreveu:';

    /**
     * @param string $message
     * @param mixed ...$args
     * @return string
     */
    public static function get($message)
    {
        $args = array_slice(func_get_args(), 1);
        return $args ? vsprintf($message, $args) : $message;
    }
}
