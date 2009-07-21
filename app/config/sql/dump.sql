CREATE TABLE usuarios (
    id int auto_increment,
    nome varchar(80),
    email varchar(80),
    senha varchar(100),
    pais_id int not null,
    twitter varchar(80),
    idade_id int,
    fontes_de_trafego_id int,
    local_de_uso_id int,
    adddate datetime,
    PRIMARY KEY  (id),
    UNIQUE KEY (id)
);

CREATE TABLE tarefas (
    id int auto_increment,
    nome varchar(80),
    descricao text,
    tipo varchar(40),
    listas_de_tarefa_id int not null,
    tipos_de_tarefa_id int not null,
    usuario_id int not null,
    ordem int not null,
    cronometro_tempo varchar(120),
    adddate datetime,
    PRIMARY KEY  (id),
    UNIQUE KEY (id)
);