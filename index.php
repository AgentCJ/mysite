<?php

require_once ('init.php');
require_once('functions.php');
require_once('data.php');
require_once('helpers.php');


$title_name = 'Главная';

$categories = get_categories($link);
$lots = get_lots($link);

$main_content = include_template('main.php', [
    'categories' => $categories,
    'lots' => $lots
]);

$layout_content = include_template('layout.php', [
    'title_name' => $title_name,
    'categories' => $categories,
    'lots' => $lots,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'main_content' => $main_content
]);

print($layout_content);