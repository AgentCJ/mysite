<?php

require_once('init.php');
require_once('functions.php');
require_once('helpers.php');

$categories = get_categories($con);

$lot_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); //FILTER_VALIDATE_INT - проверка по фильтру int
$lot    = $lot_id ? get_lot($con, $lot_id) : null;

if (!$lot) {
    http_response_code(404);

    $page_content = include_template('404.php', [
        'categories' => $categories,
    ]);
    $title_name = 'Страница не найдена';
} else {
    $page_content = include_template('lot.php', [
    'lot'        => $lot,
    'categories' => $categories,
    'is_auth'    => $is_auth,
    ]);
}

$layout_content = include_template('layout.php', [
    'title_name'   => $title_name,
    'categories'   => $categories,
    'user_name'    => $user_name,
    'is_auth'      => $is_auth,
    'main_content' => $page_content,
]);

print($layout_content);