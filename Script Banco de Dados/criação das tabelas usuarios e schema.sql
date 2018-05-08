-- Usuários e Schema

CREATE ROLE adm_indicadores_internet LOGIN PASSWORD 'adm_indicadores_internet' NOSUPERUSER NOINHERIT NOCREATEDB NOCREATEROLE NOREPLICATION;

CREATE ROLE indicadores_internet LOGIN PASSWORD 'indicadores_internet' NOSUPERUSER NOINHERIT NOCREATEDB NOCREATEROLE NOREPLICATION;

CREATE SCHEMA adm_indicadores_internet AUTHORIZATION adm_indicadores_internet;
GRANT ALL ON SCHEMA adm_indicadores_internet TO adm_indicadores_internet;
GRANT USAGE ON SCHEMA adm_indicadores_internet TO indicadores_internet;

ALTER ROLE adm_indicadores_internet SET search_path = adm_indicadores_internet;
ALTER ROLE indicadores_internet SET search_path = adm_indicadores_internet;

-- Tabelas

CREATE TABLE adm_indicadores_internet.custo_trafego
(
 id serial NOT NULL,
 custo_por_gb numeric(9,2) NOT NULL,
 data_inicio timestamp without time zone NOT NULL,
 created_at timestamp without time zone NOT NULL,
 updated_at timestamp without time zone NOT NULL,
 CONSTRAINT custo_trafego_id PRIMARY KEY (id) 
)
WITH (  OIDS=FALSE);
ALTER TABLE adm_indicadores_internet.custo_trafego OWNER TO adm_indicadores_internet;
GRANT ALL ON TABLE adm_indicadores_internet.custo_trafego TO indicadores_internet;

--Inserir um custo inicial
INSERT INTO adm_indicadores_internet.custo_trafego (custo_por_gb, data_inicio, created_at, updated_at) 
 VALUES (7.58, '01-01-2017', now(), now());

CREATE TABLE adm_indicadores_internet.economia
(
  id serial NOT NULL,
  custo_trafego_id integer NOT NULL,
  tipo character varying(50) NOT NULL,
  periodo date NOT NULL,
  bytes bigint NOT NULL,
  created_at timestamp without time zone NOT NULL,
  updated_at timestamp without time zone NOT NULL,
  CONSTRAINT economia_id PRIMARY KEY (id),
  CONSTRAINT fk_custo_trafego_id FOREIGN KEY (custo_trafego_id)
      REFERENCES adm_indicadores_internet.custo_trafego (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT un_tip_per_cus UNIQUE (tipo, periodo, custo_trafego_id)
)
WITH (OIDS=FALSE);
ALTER TABLE adm_indicadores_internet.economia OWNER TO adm_indicadores_internet;
GRANT ALL ON TABLE adm_indicadores_internet.economia TO indicadores_internet;

CREATE TABLE adm_indicadores_internet.fonte
(
  id serial NOT NULL,
  nome character varying(100) NOT NULL,
  created_at timestamp without time zone NOT NULL,
  updated_at timestamp without time zone NOT NULL,
  CONSTRAINT fonte_id PRIMARY KEY (id)
)
WITH (OIDS=FALSE);
ALTER TABLE adm_indicadores_internet.fonte OWNER TO adm_indicadores_internet;
GRANT ALL ON TABLE adm_indicadores_internet.fonte TO indicadores_internet;

--Inserir fontes iniciais
INSERT INTO adm_indicadores_internet.fonte(nome, created_at, updated_at) 
 VALUES ('Internet', now(), now()), ('Internet Economia', now(), now()), 
 ('Outras Fontes', now(), now()), ('Facebook', now(), now()), 
 ('Youtube', now(), now()), ('Instagram', now(), now());

CREATE TABLE adm_indicadores_internet.setor
(
  id serial NOT NULL,
  nome character varying(100) NOT NULL,
  created_at timestamp without time zone NOT NULL,
  updated_at timestamp without time zone NOT NULL,
  CONSTRAINT setor_id PRIMARY KEY (id)
)
WITH (OIDS=FALSE);
ALTER TABLE adm_indicadores_internet.setor OWNER TO adm_indicadores_internet;
GRANT ALL ON TABLE adm_indicadores_internet.setor TO indicadores_internet;

--Inserir setores iniciais
begin
INSERT INTO adm_indicadores_internet.setor(nome, created_at, updated_at) 
 VALUES ('adins', now(), now()), ('biblioteca', now(), now()), ('celic', now(), now()), 
 ('celic_adv', now(), now()), ('cetrei', now(), now()), ('consultoria', now(), now()), 
 ('corregedoria', now(), now()), ('cti', now(), now()), ('depaf', now(), now()), 
 ('divida', now(), now()), ('cedat', now(), now()), ('fiscal', now(), now()), 
 ('gab', now(), now()), ('gabinete', now(), now()), ('gespalacio', now(), now()), 
 ('judicial', now(), now()), ('comissao_calculo', now(), now()), ('procadin', now(), now()), 
 ('prodat', now(), now()), ('propad', now(), now()), ('propama', now(), now()), 
 ('protocolo', now(), now()), ('caba', now(), now()), ('fornecedores', now(), now()), 
 ('prolic', now(), now()), ('assessoria comunicacao', now(), now()), ('ouvidoria', now(), now());

CREATE TABLE adm_indicadores_internet.trafego
(
  id serial NOT NULL,
  custo_trafego_id integer NOT NULL,
  setor_id integer,  
  fonte_id integer NOT NULL,
  usuario character varying(50) NOT NULL,
  periodo date NOT NULL,
  bytes bigint NOT NULL,  
  created_at timestamp without time zone NOT NULL,
  updated_at timestamp without time zone NOT NULL,
  CONSTRAINT trafego_id PRIMARY KEY (id),
  CONSTRAINT fk_custo_trafego_id FOREIGN KEY (custo_trafego_id)
      REFERENCES adm_indicadores_internet.custo_trafego (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_fonte_id FOREIGN KEY (fonte_id)
      REFERENCES adm_indicadores_internet.fonte (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_setor_id FOREIGN KEY (setor_id)
      REFERENCES adm_indicadores_internet.setor (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT un_usu_per_set_fon_cus UNIQUE (usuario, periodo, setor_id, fonte_id, custo_trafego_id)
)
WITH (OIDS=FALSE);
ALTER TABLE adm_indicadores_internet.trafego OWNER TO adm_indicadores_internet;
GRANT ALL ON TABLE adm_indicadores_internet.trafego TO indicadores_internet;

CREATE TABLE adm_indicadores_internet.ultimo_registro_mapeado
(
  id serial NOT NULL,  
  fonte_id integer NOT NULL,
  tempo character varying(30),
  created_at timestamp without time zone NOT NULL,
  updated_at timestamp without time zone NOT NULL,  
  CONSTRAINT historico_accesslog_id PRIMARY KEY (id),
  CONSTRAINT fk_fonte_id FOREIGN KEY (fonte_id)
      REFERENCES adm_indicadores_internet.fonte (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (OIDS=FALSE);
ALTER TABLE adm_indicadores_internet.ultimo_registro_mapeado OWNER TO adm_indicadores_internet;
GRANT ALL ON TABLE adm_indicadores_internet.ultimo_registro_mapeado TO indicadores_internet;

-- Grant nas Sequences
GRANT ALL ON SEQUENCE adm_indicadores_internet.custo_trafego_id_seq TO indicadores_internet;
GRANT ALL ON SEQUENCE adm_indicadores_internet.fonte_id_seq TO indicadores_internet;
GRANT ALL ON SEQUENCE adm_indicadores_internet.trafego_id_seq TO indicadores_internet;
GRANT ALL ON SEQUENCE adm_indicadores_internet.ultimo_registro_mapeado_id_seq TO indicadores_internet;
GRANT ALL ON SEQUENCE adm_indicadores_internet.setor_id_seq TO indicadores_internet;
GRANT ALL ON SEQUENCE adm_indicadores_internet.economia_id_seq TO indicadores_internet;
