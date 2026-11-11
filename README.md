# Takin Petz — site de adoção e acolhimento de pets

Projeto de Extensão · Programação Web I · UNIDAVI.
PHP puro (sem framework), PostgreSQL (PDO), SMTP com cliente próprio, arquitetura MVC com `Persistence` por model.

## Requisitos

- PHP 8.1+ com `pdo_pgsql`, `mbstring`, `fileinfo` (e `allow_url_fopen` ligado, para buscar as cidades no IBGE)
- PostgreSQL 12+
- Um servidor SMTP (para testar: [Mailtrap](https://mailtrap.io), porta 587 com `tls`)

## Instalação

```bash
createdb -U postgres adocao                       # se já existia o schema antigo: dropdb adocao antes
psql -U postgres -d adocao -f sql/schema.sql      # tabelas + cadastros iniciais (raças, cores, vacinas, sintomas)
cp config/config.example.php config/config.php    # edite banco, SMTP e dados da entidade
php scripts/create_admin.php "Seu Nome" voce@exemplo.org
php -S localhost:8000 -t public public/index.php
```

Site: <http://localhost:8000> · Painel: <http://localhost:8000/admin/login>

- **Produção:** document root em `public/`. Apache usa o `public/.htaccess`; Nginx: `location / { try_files $uri /index.php?$query_string; }`.
- **Uploads:** `public/uploads/` precisa de escrita. No `php.ini`, `upload_max_filesize` e `post_max_size` ≥ `16M` (o padrão do PHP é 2 MB).
- **PHP 8.4+:** `Animal $Animal = null` (tipo implícito nulo, padrão do projeto) gera aviso de *deprecated*; se for rodar em 8.4+, troque por `?Animal $Animal = null`.

## Arquitetura

```
public/index.php        ponto único de entrada
app/
  Core/         Application, Router, Controller, View, Model, Persistence, Database, Messages, Validator,
                Domain, Session, Csrf, Auth, Config, exceções
  Utils/        ArrayUtils, ModelUtils, PersistenciaUtils, ControllerUtils, StringUtils, DateUtils  (tudo estático)
  Models/       só dados (+ rótulos): Animal, Foto, Pessoa, Solicitacao, Consulta...
  Persistence/  um por model; é aqui que ficam os SQLs escritos à mão
  Controllers/  públicos + Admin/ (CrudController genérico para os cadastros)
  Services/     Smtp, Mailer, Notifier, PhotoUploader, IbgeClient
  Views/        layouts/, partials/ e uma pasta por área
  routes.php    tabela de rotas
```

**Caminho de uma requisição:** `Router` → `Controller` (tem `$this->Persistence` e `$this->Model`) → `Persistence` (SQL) → `Model` (dados) → `View`.

### Convenções do código

- **Mensagens:** todas em `Core\Messages`. Uso: `Messages::get(Messages::ANIMAL_SAVED)` ou `Messages::get(Messages::ANIMAL_DELETED, $name)`.
- **Nomes:** métodos em inglês (`getAllModel`, `save`, `refresh`...). Variáveis que guardam objetos começam com maiúscula (`$Animal`, `$Persistence`); arrays, textos e números em minúscula.
- **Tipos:** nada de tipo em escalares/retorno; só classes, como em `Animal $Animal = null`. Cada método tem docblock só com `@param`, `@return` e `@throws`.
- **Persistence:** cada model tem a sua. A base (`Core\Persistence`) traz `getAllModel`, `getModelById`, `count`, `save`, `insert`, `update`, `delete`, `refresh`, `getSqlGetAll`, `getSqlCount`, `getSqlGetById`, `getSqlDelete`. Cada filha escreve o seu `getSqlInsert`, `getSqlUpdate`, `getParams` (e `getSqlGetAll` quando precisa de JOIN). O alias da tabela principal é sempre `t`.
- **`getModelById` e `refresh`** trazem o model completo (inclusive coleções, como as fotos do animal); `getAllModel` traz só as linhas, sem consultas extras.
- **Utils estáticas:** lógica reaproveitável (montar WHERE, tratar upload, ler POST, datas, ids de models...) fica nas `*Utils`.

### Criar um cadastro novo em 3 arquivos

1. `Models/Xxx.php` (classe vazia que estende `Model`)
2. `Persistence/XxxPersistence.php` (`getTable`, `getModelClass`, `getSqlInsert`, `getSqlUpdate`, `getParams`)
3. `Controllers/Admin/XxxController.php` estendendo `CrudController` (título, campos e colunas) + uma linha em `routes.php`: `$Router->crud('/admin/xxx', XxxController::class);`

## O que o sistema faz

- **Site:** catálogo com filtros, página do animal (com vacinas aplicadas), pedido de adoção, contato, sobre.
- **Menos digitação:** estado e cidade por seleção (municípios vindos do IBGE e guardados no banco na primeira consulta de cada UF; se o IBGE estiver fora do ar, o formulário aceita digitar a cidade); raça (filtrada pela espécie) e cor por seleção, com cadastro no painel.
- **Painel:** animais (fotos, status), pedidos (aprovar/recusar com e-mails e recusa automática dos demais), cadastros (raças, cores, veterinários, vacinas, sintomas).
- **Saúde do animal:** vacinas (com próxima dose), alimentação/dieta (com histórico), consultas (veterinário, o que o animal apresentava, diagnóstico, tratamento, retorno).
- **Regras:** RN01, RN03–RN05, RN10–RN12, RN14–RN22 (veja `docs/modelagem.md` para as constraints no banco).
- **Segurança:** PDO com prepared statements, escape em toda saída, CSRF em todo POST, sessão regenerada no login, honeypot nos formulários, upload validado pelo conteúdo real do arquivo.

## SMTP

Em `config.php` → `smtp`: `seguranca` = `'tls'` (587), `'ssl'` (465) ou `'none'` (só local). Todo envio (sucesso ou falha) é registrado em `emails_enviados`; falha de e-mail nunca derruba o pedido.

## Antes de entregar

- Preencher os dados da entidade em `config.php` e o texto de `app/Views/page/about.php`.
- Não subir `config/config.php` nem dados reais de solicitantes para o GitHub (já está no `.gitignore`).
