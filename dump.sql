CREATE DATABASE blog_manga;
USE blog_mangaka;

create table categories
(
    id   int auto_increment
        primary key,
    name varchar(100) null
);

create table users
(
    id       int auto_increment
        primary key,
    name     varchar(100)         null,
    password varchar(100)         null,
    mail     varchar(255)         null,
    isAdmin  tinyint(1) default 0 null,
    date     date                 null
);

create table articles
(
    id            int auto_increment
        primary key,
    title         varchar(255) null,
    content       text         null,
    author        varchar(255) null,
    picture       text         null,
    date          date         null,
    users_id      int          null,
    categories_id int          null,
    is_featured   tinyint      null,
    constraint articles_categories
        foreign key (categories_id) references categories (id),
    constraint articles_users
        foreign key (users_id) references users (id)
);

create table comments
(
    id          int auto_increment
        primary key,
    date        date null,
    content     text null,
    users_id    int  null,
    articles_id int  null,
    constraint comments_articles
        foreign key (articles_id) references articles (id),
    constraint comments_users
        foreign key (users_id) references users (id)
);

INSERT INTO users (name, password, mail, isAdmin, date) VALUES ('jean', 'jean', 'jean@jean.fr', 1, now());

