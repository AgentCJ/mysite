<?php

require_once 'init.php';
require_once 'functions.php';
require_once 'data.php';
require_once 'helpers.php';

$categories = get_categories($link);

$category_id = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

if ($category_id !== null && $category_id !== false && $category_id > 0) {
    $category_exists = false;

    foreach ($categories as $category) {
        if ((int) $category['id'] === $category_id) {
            $category_exists = true;
            $title_name = $category['name'];
            break;
        }
    }

    if (!$category_exists) {
        $category_id = null;
    }
} else {
    $category_id = null;
}

if ($category_id === null) {
    $title_name = 'Главная';
}

$lots = get_lots($link, null, $category_id);

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
