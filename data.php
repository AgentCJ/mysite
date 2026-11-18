<?php
require_once('functions.php');
$is_auth = rand(0, 1);
$title_name = 'Главная';
$user_name = 'Сергей'; // укажите здесь ваше имя
$categories = ['Доски и лыжи', 'Крепления', 'Ботинки', 'Одежда', 'Инструменты', 'Разное'];
$lots = [
[
    'name' => '2014 Rossignol District Snowboard',
    'categorie' => 'Доски и лыжи',
    'price' => 10999,
    'URL' => 'img/lot-1.jpg',
    'date_expire' => '2024-10-15'
],
[
    'name' => 'DC Ply Mens 2016/2017 Snowboard',
    'categorie' => 'Доски и лыжи',
    'price' => 159999,
    'URL' => 'img/lot-2.jpg',
    'date_expire' => '2026-10-20'
],
[
    'name' => 'Крепления Union Contact Pro 2015 года размер L/XL',
    'categorie' => 'Крепления',
    'price' => 8000,
    'URL' => 'img/lot-3.jpg',
    'date_expire' => '2026-10-30'
],
[
    'name' => 'Ботинки для сноуборда DC Mutiny Charocal',
    'categorie' => 'Ботинки',
    'price' => 10999,
    'URL' => 'img/lot-4.jpg',
    'date_expire' => '2026-11-02'
],
[
    'name' => 'Куртка для сноуборда DC Mutiny Charocal',
    'categorie' => 'Одежда',
    'price' => 7500,
    'URL' => 'img/lot-5.jpg',
    'date_expire' => '2026-11-14'
],
[
    'name' => 'Маска Oakley Canopy',
    'categorie' => 'Разное',
    'price' => 5400,
    'URL' => 'img/lot-6.jpg',
    'date_expire' => '2026-12-15'
]
];

?>