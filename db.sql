create table companies
(
    id         int auto_increment
        primary key,
    name       varchar(100) not null,
    email      varchar(228) not null,
    country_id int          not null,
    constraint companies_email_uindex
        unique (email)
);

create index companies_countries_id_fk
    on companies (country_id);

create table countries
(
    id   int auto_increment
        primary key,
    name varchar(50) not null,
    plan int         null
);

create table mining
(
    id         int auto_increment
        primary key,
    company_id int                                 not null,
    mined      int                                 not null,
    date       timestamp default CURRENT_TIMESTAMP null,
    constraint mining_company_id_date_uindex
        unique (company_id, date),
    constraint mining_companies_id_fk
        foreign key (company_id) references companies (id)
);

create index mining_company_index
    on mining (company_id);

create index mining_date_index
    on mining (date);

INSERT INTO goldmining.countries (id, name, plan)
VALUES (1, 'USA', 100000000);
INSERT INTO goldmining.countries (id, name, plan)
VALUES (2, 'Canada', 10000000);
INSERT INTO goldmining.countries (id, name, plan)
VALUES (3, 'Russia', 8000000);
INSERT INTO goldmining.countries (id, name, plan)
VALUES (4, 'Australia', 900000);

INSERT INTO goldmining.companies (id, name, email, country_id)
VALUES (1, 'Adults', 'a@b.c', 1);
INSERT INTO goldmining.companies (id, name, email, country_id)
VALUES (2, 'Goldcorp', 'goldcorp@mail.com', 2);
INSERT INTO goldmining.companies (id, name, email, country_id)
VALUES (3, 'Barrick Gold', 'barrick@gold.com', 2);
INSERT INTO goldmining.companies (id, name, email, country_id)
VALUES (4, 'Newmont Mining', 'newmont@mining.com', 1);
INSERT INTO goldmining.companies (id, name, email, country_id)
VALUES (5, 'Polyus Gold', 'polyus@gold.com', 3);
INSERT INTO goldmining.companies (id, name, email, country_id)
VALUES (6, 'Newcrest Mining', 'newcrest@mining.com', 4);
