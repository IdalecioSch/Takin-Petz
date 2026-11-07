# Site de Adoção e Acolhimento de Pets

Projeto de Extensão · Programação Web I · UNIDAVI.
**PHP puro (sem framework)**, **PostgreSQL** (PDO) e **SMTP** com cliente próprio (sockets, sem PHPMailer).

## Requisitos

- PHP 8.1+ com as extensões `pdo_pgsql`, `mbstring` e `fileinfo`
- PostgreSQL 12+
- Um servidor SMTP (para testar: [Mailtrap](https://mailtrap.io), porta 587 com `tls`)

## Instalação

```bash
# 1) banco (se já existia o schema antigo: dropdb adocao antes)
createdb -U postgres adocao
psql -U postgres -d adocao -f sql/schema.sql

# 2) configuração
cp config/config.example.php config/config.php   # edite banco, SMTP e dados da entidade

# 3) primeiro administrador (a senha é pedida no terminal)
php scripts/criar_admin.php "Seu Nome" voce@exemplo.org

# 4) rodar localmente
php -S localhost:8000 -t public public/index.php
```

Site: <http://localhost:8000> · Painel: <http://localhost:8000/admin/login>

### Uploads

Fotos vão para `public/uploads/` (o usuário do PHP precisa de permissão de escrita).
No `php.ini`, `upload_max_filesize` e `post_max_size` precisam comportar as fotos
(o padrão do PHP é 2 MB, e o limite do projeto é 3 MB por foto, até 5 por animal: use algo como `16M`).

### Produção

- Aponte o *document root* para a pasta `public/`. `app/` e `config/` ficam fora do alcance da web.
- Apache: o `public/.htaccess` já faz o rewrite. Nginx: `location / { try_files $uri /index.php?$query_string; }`.
- Se o site ficar em subpasta (ex.: `/adocao`), ajuste `base_path` em `config/config.php`.
- Com HTTPS, os cookies de sessão passam a sair com a flag `secure` automaticamente.
- Deixe `debug` como `false`.

## Arquitetura (OOP + MVC, sem framework)

```
public/index.php        front controller (único ponto de entrada) + .htaccess
app/
  Core/                 mini-framework: Application, Router, Request, Controller, View, Model (Active Record),
                        Database (PDO), Session, Csrf, Auth, Validator, Dominio, HttpException
  Models/               Animal, Foto, Raca, Cor, Pessoa, Cidade, Solicitacao, Administrador, MensagemContato, EmailLog
  Controllers/          Home, Catalogo, Adocao, Contato, Pagina  +  Admin/ (Login, Painel, Animal, Solicitacao)
  Services/             Smtp, Mailer, Notificador (e-mails), UploadFotos
  Views/                layouts/, partials/ e uma pasta por área
  rotas.php             tabela de rotas
config/  sql/  scripts/  docs/modelagem.md
```

**Models persistem direto (Active Record).** Cada model sabe ler e gravar a própria tabela:

```php
$animal = Animal::buscar(3);          // SELECT
$animal->nome = 'Rex';
$animal->salvar();                    // UPDATE só das colunas alteradas
$novo = (new Animal([...]))->salvar(); // INSERT ... RETURNING *
Animal::onde('status = :s', ['s' => 'disponivel'], 'nome');
$animal->fotos();                     // relacionamento (carregado sob demanda)
```

As regras de negócio ficam nos models (ex.: `Solicitacao::aprovar()` aprova, adota o animal e recusa os demais
numa transação), os controllers só orquestram e as views só exibem.

> O banco está em **3ª forma normal**. Veja `docs/modelagem.md` (problemas do modelo antigo, o que mudou, DER).

## O que foi implementado

**MVP completo:** RF01–RF09, RF11–RF20 e RF23–RF25. **Extra:** RF10 (contato por e-mail).

| Regra | Onde |
| --- | --- |
| RN01, RN17 (fotos: mín. 1, máx. 5, 3 MB, JPG/PNG/WEBP) | `Admin\AnimalController`, `Services\UploadFotos` (tipo real via `finfo` + `getimagesize`, nome aleatório) |
| RN03 (só Disponível no catálogo e para pedidos) | `Animal::catalogo()`, `CatalogoController`, `AdocaoController` |
| RN04, RN11, RN12 (aprovar → Adotado, recusa automática dos demais, data/responsável) | `Solicitacao::aprovar()` (transação com `FOR UPDATE`) |
| RN05, RF16 (animal com pedidos não é excluído, só ocultado) | `Animal::remover()` |
| RN10 (um pedido pendente por e-mail/animal) | `Solicitacao::jaPendente()` + índice único parcial no banco |
| RN14–RN16 (login, `password_hash`, dados só no painel) | `Core\Auth`, `Admin\AdminController`, `Administrador` |
| RN18–RN20 (e-mails; falha não impede o pedido; log) | `Services\Mailer`/`Notificador`, tabela `emails_enviados` |
| RN21, RN22 (consentimento LGPD; exclusão de dados) | `AdocaoController`, `Pessoa::excluirDados()` (cascade) |

Segurança (RNF02/03/06): PDO com *prepared statements*, `htmlspecialchars` em toda saída, CSRF em todos os POST,
`session_regenerate_id` no login, cookies `HttpOnly`/`SameSite`, honeypot anti-bot nos formulários públicos,
`.htaccess` bloqueando execução de PHP em `uploads/`; só `public/` é exposta.

## SMTP

Em `config.php` → `smtp`:

- `seguranca`: `'tls'` (STARTTLS, porta 587), `'ssl'` (porta 465) ou `'none'` (só para testes locais).
- Autenticação `AUTH LOGIN`; o certificado do servidor é verificado.
- E-mail em `multipart/alternative` (texto + HTML), assunto em UTF-8.
- Cada envio (sucesso ou falha) é registrado: `SELECT * FROM emails_enviados ORDER BY id DESC;`

## Fora desta versão

RF21 e RF22 (gerenciar administradores, recuperar senha) e RF26–RF30 (extras 3 a 7).
O status "Em análise" existe e pode ser definido manualmente no painel, mas o sistema não o aplica sozinho
(decisão a validar com a entidade, como diz o documento).

## Antes de entregar

- Preencher os dados da entidade em `config.php` e os textos de `app/Views/pagina/sobre.php`.
- Não subir `config/config.php` nem dados reais de solicitantes para o GitHub (já está no `.gitignore`).
