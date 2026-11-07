-- =====================================================================
-- Site de Adoção e Acolhimento de Pets — modelo relacional em 3FN
-- Uso (banco novo):  psql -U postgres -d adocao -f sql/schema.sql
-- Se já existe o schema antigo, recrie o banco:  dropdb adocao && createdb adocao
-- =====================================================================

-- ---------- Tabelas de domínio (valores fixos que antes eram texto repetido) ----------
CREATE TABLE especies          (codigo VARCHAR(10) PRIMARY KEY, nome VARCHAR(30) NOT NULL, ordem SMALLINT NOT NULL DEFAULT 0);
CREATE TABLE sexos             (codigo VARCHAR(10) PRIMARY KEY, nome VARCHAR(30) NOT NULL, ordem SMALLINT NOT NULL DEFAULT 0);
CREATE TABLE portes            (codigo VARCHAR(10) PRIMARY KEY, nome VARCHAR(30) NOT NULL, ordem SMALLINT NOT NULL DEFAULT 0);
CREATE TABLE faixas_etarias    (codigo VARCHAR(10) PRIMARY KEY, nome VARCHAR(30) NOT NULL, ordem SMALLINT NOT NULL DEFAULT 0);
CREATE TABLE status_animal     (codigo VARCHAR(12) PRIMARY KEY, nome VARCHAR(30) NOT NULL, ordem SMALLINT NOT NULL DEFAULT 0);
CREATE TABLE status_solicitacao(codigo VARCHAR(10) PRIMARY KEY, nome VARCHAR(30) NOT NULL, ordem SMALLINT NOT NULL DEFAULT 0);
CREATE TABLE tipos_moradia     (codigo VARCHAR(12) PRIMARY KEY, nome VARCHAR(30) NOT NULL, ordem SMALLINT NOT NULL DEFAULT 0);
CREATE TABLE estados           (sigla  CHAR(2)     PRIMARY KEY, nome VARCHAR(30) NOT NULL, ordem SMALLINT NOT NULL DEFAULT 0);

INSERT INTO especies VALUES ('cachorro','Cachorro',1),('gato','Gato',2),('outro','Outro',3);
INSERT INTO sexos VALUES ('macho','Macho',1),('femea','Fêmea',2);
INSERT INTO portes VALUES ('pequeno','Pequeno',1),('medio','Médio',2),('grande','Grande',3);
INSERT INTO faixas_etarias VALUES ('filhote','Filhote',1),('jovem','Jovem',2),('adulto','Adulto',3),('idoso','Idoso',4);
INSERT INTO status_animal VALUES ('disponivel','Disponível',1),('em_analise','Em análise',2),('adotado','Adotado',3),('indisponivel','Indisponível',4);
INSERT INTO status_solicitacao VALUES ('pendente','Pendente',1),('aprovado','Aprovado',2),('recusado','Recusado',3);
INSERT INTO tipos_moradia VALUES ('casa','Casa',1),('apartamento','Apartamento',2);
INSERT INTO estados (sigla, nome) VALUES
 ('AC','Acre'),('AL','Alagoas'),('AP','Amapá'),('AM','Amazonas'),('BA','Bahia'),('CE','Ceará'),
 ('DF','Distrito Federal'),('ES','Espírito Santo'),('GO','Goiás'),('MA','Maranhão'),('MT','Mato Grosso'),
 ('MS','Mato Grosso do Sul'),('MG','Minas Gerais'),('PA','Pará'),('PB','Paraíba'),('PR','Paraná'),
 ('PE','Pernambuco'),('PI','Piauí'),('RJ','Rio de Janeiro'),('RN','Rio Grande do Norte'),
 ('RS','Rio Grande do Sul'),('RO','Rondônia'),('RR','Roraima'),('SC','Santa Catarina'),
 ('SP','São Paulo'),('SE','Sergipe'),('TO','Tocantins');

-- ---------- Localização ----------
CREATE TABLE cidades (
    id    SERIAL PRIMARY KEY,
    nome  VARCHAR(100) NOT NULL,
    uf    CHAR(2) NOT NULL REFERENCES estados(sigla)
);
CREATE UNIQUE INDEX uq_cidade ON cidades (uf, lower(nome));

-- ---------- Pessoas e administradores ----------
-- Quem pede adoção ou manda mensagem: nome/telefone/cidade dependem da PESSOA (e-mail),
-- não de cada pedido. Antes estavam repetidos em solicitacoes_adocao.
CREATE TABLE pessoas (
    id         SERIAL PRIMARY KEY,
    nome       VARCHAR(160) NOT NULL,
    email      VARCHAR(160) NOT NULL,
    telefone   VARCHAR(30),
    cidade_id  INTEGER REFERENCES cidades(id),
    criado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX uq_pessoa_email ON pessoas (lower(email));

CREATE TABLE usuarios_admin (
    id          SERIAL PRIMARY KEY,
    nome        VARCHAR(120) NOT NULL,
    email       VARCHAR(160) NOT NULL UNIQUE,
    senha_hash  VARCHAR(255) NOT NULL,
    ativo       BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ---------- Animais ----------
-- Raça determina a espécie (Siamês é gato): por isso a raça tem tabela própria e o animal
-- usa FK composta (raca_id, especie), que impede "gato" com raça de cachorro.
CREATE TABLE racas (
    id       SERIAL PRIMARY KEY,
    especie  VARCHAR(10) NOT NULL REFERENCES especies(codigo),
    nome     VARCHAR(80) NOT NULL,
    UNIQUE (id, especie)
);
CREATE UNIQUE INDEX uq_raca ON racas (especie, lower(nome));

CREATE TABLE cores (
    id    SERIAL PRIMARY KEY,
    nome  VARCHAR(60) NOT NULL
);
CREATE UNIQUE INDEX uq_cor ON cores (lower(nome));

CREATE TABLE animais (
    id             SERIAL PRIMARY KEY,
    nome           VARCHAR(80) NOT NULL,
    especie        VARCHAR(10) NOT NULL REFERENCES especies(codigo),
    sexo           VARCHAR(10) NOT NULL REFERENCES sexos(codigo),
    porte          VARCHAR(10) NOT NULL REFERENCES portes(codigo),
    faixa_etaria   VARCHAR(10) NOT NULL REFERENCES faixas_etarias(codigo),
    raca_id        INTEGER,
    cor_id         INTEGER REFERENCES cores(id),
    descricao      TEXT,
    temperamento   TEXT,
    castrado       BOOLEAN NOT NULL DEFAULT FALSE,
    vacinado       BOOLEAN NOT NULL DEFAULT FALSE,
    vermifugado    BOOLEAN NOT NULL DEFAULT FALSE,
    status         VARCHAR(12) NOT NULL DEFAULT 'disponivel' REFERENCES status_animal(codigo),
    criado_em      TIMESTAMPTZ NOT NULL DEFAULT now(),
    atualizado_em  TIMESTAMPTZ NOT NULL DEFAULT now(),
    FOREIGN KEY (raca_id, especie) REFERENCES racas (id, especie)
);
CREATE INDEX idx_animais_status ON animais (status);

CREATE TABLE fotos_animais (
    id         SERIAL PRIMARY KEY,
    animal_id  INTEGER NOT NULL REFERENCES animais(id) ON DELETE CASCADE,
    arquivo    VARCHAR(255) NOT NULL,
    principal  BOOLEAN NOT NULL DEFAULT FALSE,
    ordem      INTEGER NOT NULL,
    UNIQUE (animal_id, ordem)
);
-- no máximo uma foto principal por animal
CREATE UNIQUE INDEX uq_foto_principal ON fotos_animais (animal_id) WHERE principal;

-- ---------- Pedidos de adoção ----------
CREATE TABLE solicitacoes_adocao (
    id                  SERIAL PRIMARY KEY,
    animal_id           INTEGER NOT NULL REFERENCES animais(id),
    pessoa_id           INTEGER NOT NULL REFERENCES pessoas(id) ON DELETE CASCADE,
    tipo_moradia        VARCHAR(12) NOT NULL REFERENCES tipos_moradia(codigo),
    tem_outros_animais  BOOLEAN NOT NULL,
    motivacao           TEXT NOT NULL,
    termo_aceito_em     TIMESTAMPTZ NOT NULL,   -- prova do aceite (substitui boolean sempre true)
    lgpd_aceito_em      TIMESTAMPTZ NOT NULL,
    status              VARCHAR(10) NOT NULL DEFAULT 'pendente' REFERENCES status_solicitacao(codigo),
    motivo_recusa       TEXT,
    criado_em           TIMESTAMPTZ NOT NULL DEFAULT now(),
    decidido_em         TIMESTAMPTZ,
    decidido_por        INTEGER REFERENCES usuarios_admin(id) ON DELETE SET NULL,
    CONSTRAINT ck_decisao CHECK ((status = 'pendente') = (decidido_em IS NULL)),
    CONSTRAINT ck_motivo  CHECK (motivo_recusa IS NULL OR status = 'recusado')
);
CREATE INDEX idx_solic_status ON solicitacoes_adocao (status);
CREATE INDEX idx_solic_animal ON solicitacoes_adocao (animal_id);
CREATE INDEX idx_solic_pessoa ON solicitacoes_adocao (pessoa_id);
-- RN10: uma mesma pessoa não pode ter 2 pedidos pendentes para o mesmo animal
CREATE UNIQUE INDEX uq_pedido_pendente ON solicitacoes_adocao (animal_id, pessoa_id) WHERE status = 'pendente';

-- ---------- Contato e log de e-mails ----------
CREATE TABLE mensagens_contato (
    id         SERIAL PRIMARY KEY,
    pessoa_id  INTEGER NOT NULL REFERENCES pessoas(id) ON DELETE CASCADE,
    mensagem   TEXT NOT NULL,
    criado_em  TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE emails_enviados (
    id            SERIAL PRIMARY KEY,
    destinatario  VARCHAR(160) NOT NULL,
    assunto       VARCHAR(255) NOT NULL,
    sucesso       BOOLEAN NOT NULL,
    erro          TEXT,
    criado_em     TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_emails_dest ON emails_enviados (lower(destinatario));
