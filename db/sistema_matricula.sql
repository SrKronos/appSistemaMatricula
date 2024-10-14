CREATE TABLE usuarios (
 id SERIAL PRIMARY KEY,
 username VARCHAR(50) UNIQUE NOT NULL,
 password VARCHAR(50) NOT NULL
);

insert into usuarios(username,password) values ('leo@mail.com','123');

CREATE TABLE estudiantes (
 id SERIAL PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 apellido VARCHAR(100) NOT NULL,
 cedula VARCHAR(10) UNIQUE NOT NULL,
 fecha_nacimiento DATE NOT NULL
);

alter table estudiantes add column estado boolean default true;

CREATE TABLE matriculas (
 id SERIAL PRIMARY KEY,
 id_estudiante INTEGER REFERENCES estudiantes(id),
 fecha_matricula DATE NOT NULL,
 curso VARCHAR(100) NOT NULL
);
alter table matriculas add column tipo varchar(2) default 'I';

/*Evita que se duplique un estudiante en el mismo curso dos veces*/
ALTER TABLE public.matriculas
ADD CONSTRAINT matriculas_id_estudiante_curso_ukey UNIQUE (id_estudiante, curso);



/*creacion de vista*/
create or replace view matriculados as 
select m.id, m.id_estudiante,m.tipo, m.fecha_matricula, m.curso, concat(e.nombre,' ',e.apellido) as estudiante,e.cedula 
from matriculas m inner join estudiantes e on m.id_estudiante = e.id order by m.fecha_matricula desc;
