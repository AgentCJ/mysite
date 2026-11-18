<?php

require_once 'init.php';
require_once 'functions.php';
require_once 'helpers.php';

if ($is_auth) {
    http_response_code(403);
    exit('403 Forbidden');
}

$categories = get_categories($link);

$errors = [];
$user = [
    'email' => '',
    'name' => '',
    'password' => '',
    'contacts' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = array_merge($user, $_POST);

    $errors = validate_user($link, $user);

    if (!$errors) {
        $password_hash = password_hash($user['password'], PASSWORD_DEFAULT);
        $user_id = insert_user($link, $user, $password_hash);

        if ($user_id) {
            header('Location: login.php');
            exit;
        }

        $errors['email'] = 'Не удалось сохранить пользователя';
    }
}

$page_content = include_template('sign-up.php', [
    'user' => $user,
    'errors' => $errors,
]);

$layout_content = include_template('layout.php', [
    'main_content' => $page_content,
    'categories' => $categories,
    'title_name' => 'Регистрация',
    'is_auth' => $is_auth,
    'user_name' => $user_name,
]);

print($layout_content);
