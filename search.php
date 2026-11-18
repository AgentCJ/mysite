<?php

require_once 'init.php';
require_once 'functions.php';
require_once 'helpers.php';

$categories = get_categories($link);

$search = '';

if (isset($_GET['search']) && is_string($_GET['search'])) {
    $search = trim($_GET['search']);
}

$lots = [];

if ($search !== '') {
    $sql = 'SELECT
                l.id,
                l.title,
                l.description,
                l.image_path,
                l.starting_price,
                l.expiration_date,
                l.bid_step,
                c.name AS category_name
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE MATCH(l.title, l.description) AGAINST(?)
            ORDER BY l.creation_date DESC';

    $stmt = mysqli_prepare($link, $sql);

    if (!$stmt) {
        die('Ошибка подготовки запроса: ' . mysqli_error($link));
    }

    mysqli_stmt_bind_param($stmt, 's', $search);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

    mysqli_stmt_close($stmt);
}

$page_content = include_template('search.php', [
    'search' => $search,
    'lots' => $lots
]);

$layout_content = include_template('layout.php', [
    'title_name' => 'Результаты поиска',
    'categories' => $categories,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'main_content' => $page_content,
    'search' => $search
]);

print($layout_content);
