<?php
require_once 'helpers.php';
require_once 'init.php';
require_once 'functions.php';

$categories = get_categories($link);

$page_content = '<h2>Вход</h2><p>Форма входа появится в следующем задании.</p>';

print(include_template('layout.php', [
    'main_content' => $page_content,
    'categories'   => $categories,
    'title_name'   => 'Вход',
    'is_auth'      => $is_auth,
    'user_name'    => $user_name,
]));