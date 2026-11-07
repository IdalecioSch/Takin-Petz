<?php
// Copie para config/config.php e preencha. NUNCA suba config.php para o Git.
return [
    'debug'     => false,          // true só em desenvolvimento
    'base_path' => '',             // '' se o site está na raiz do domínio; '/adocao' se estiver em subpasta

    'entidade' => [
        'nome'     => 'Entidade Parceira (definir)',
        'email'    => 'contato@exemplo.org',   // recebe avisos de novos pedidos e contatos
        'telefone' => '(47) 00000-0000',
        'endereco' => 'Endereço da entidade',
    ],

    'db' => [
        'host'    => '127.0.0.1',
        'porta'   => 5432,
        'nome'    => 'adocao',
        'usuario' => 'postgres',
        'senha'   => '',
    ],

    'smtp' => [
        'host'            => 'smtp.exemplo.com',
        'porta'           => 587,
        'seguranca'       => 'tls',    // 'tls' (STARTTLS, porta 587) | 'ssl' (porta 465) | 'none'
        'usuario'         => '',
        'senha'           => '',
        'remetente_email' => 'nao-responder@exemplo.org',
        'remetente_nome'  => 'Adoção de Pets',
        'timeout'         => 10,
    ],

    'upload' => [
        'max_bytes' => 3 * 1024 * 1024, // RN17: 3 MB por foto
        'max_fotos' => 5,               // RN17: até 5 fotos por animal
    ],
];
