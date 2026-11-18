<?php

require_once 'init.php';
require_once 'functions.php';
require_once 'helpers.php';

// Если пользователь уже вошёл, запрещаем повторный вход
if ($is_auth) {
    http_response_code(403);
    exit('Доступ запрещён');
}

$categories = get_categories($link);

$errors = [];
$user = [
    'email' => '',
    'password' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = array_merge($user, $_POST);

    $errors = validate_login($link, $user);

    if (!$errors) {
        $found_user = get_user_by_email($link, $user['email']);

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $found_user['id'],
            'name' => $found_user['name'],
        ];

        header('Location: index.php');
        exit;
    }
}

$page_content = include_template('login.php', [
    'user' => $user,
    'errors' => $errors,
]);

$layout_content = include_template('layout.php', [
    'main_content' => $page_content,
    'categories' => $categories,
    'title_name' => 'Вход',
    'is_auth' => $is_auth,
    'user_name' => $user_name,
]);

print($layout_content);
