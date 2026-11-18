<?php

require_once('init.php');
require_once('functions.php');
require_once('helpers.php');

if (!$is_auth) {
    http_response_code(403);
    exit('403 Forbidden');
}

$categories = get_categories($con);

$errors = [];
$lot = [
    'title'           => '',
    'category_id'     => '',
    'description'     => '',
    'starting_price'  => '',
    'bid_step'        => '',
    'expiration_date' => '',
];

// ID автора берём из текущей сессии
$author_id = $user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lot = array_merge($lot, $_POST);
    $errors = validate_lot($lot, $categories);

    if (empty($_FILES['image']['name'])) {
        $errors['image'] = 'Загрузите изображение';
    }

    if (empty($errors)) {

        $image = save_upload($_FILES['image'], __DIR__ . '/uploads');

        if (!$image) {
            $errors['image'] = 'Не удалось загрузить файл';
        } else {
            $lot_id = insert_lot($con, $lot, $image, $author_id);

            if ($lot_id) {
                header('Location: lot.php?id=' . $lot_id);
                exit;
            }

            $errors['image'] = 'Не удалось сохранить лот';
        }
    }
}

$page_content = include_template('add.php', [
    'categories' => $categories,
    'lot'        => $lot,
    'errors'     => $errors,
]);

$layout_content = include_template('layout.php', [
    'title_name'   => 'Добавление лота',
    'categories'   => $categories,
    'user_name'    => $user_name,
    'is_auth'      => $is_auth,
    'main_content' => $page_content,
]);

print($layout_content);
