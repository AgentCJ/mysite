<?php

date_default_timezone_set('Europe/Moscow');

/** Порог в часах, меньше которого таймер считается «горящим» */
const HOURS_UNTIL_FINISH = 24;

/** Секунд в минуте */
const SECONDS_PER_MINUTE = 60;

/** Минут в часе */
const MINUTES_PER_HOUR = 60;

/** Секунд в часе */
const SECONDS_PER_HOUR = SECONDS_PER_MINUTE * MINUTES_PER_HOUR;

/** Часов в сутках */
const HOURS_PER_DAY = 24;

/** Секунд в сутках */
const SECONDS_PER_DAY = HOURS_PER_DAY * SECONDS_PER_HOUR;

/**
 * Форматирует число сгруппированными тысячами и знаком рубля.
 */
function to_price(int $num): string
{
    return number_format($num, 0, '', ' ') . ' ₽';
}

/**
 * Считает оставшееся до окончания лота время.
 * Возвращает массив строк [«ЧЧ», «ММ»].
 */
function get_dt_range(string $date): array
{
    $date = substr(trim($date), 0, 10);
    $expire = strtotime($date . ' 23:59:59');
    $now = time();
    $diff = $expire - $now;

    if ($diff <= 0) {
        return ['00', '00'];
    }

    $hours = (int) floor($diff / SECONDS_PER_HOUR);
    $minutes = (int) floor(($diff % SECONDS_PER_HOUR) / SECONDS_PER_MINUTE);

    return [
        str_pad((string) $hours, 2, '0', STR_PAD_LEFT),
        str_pad((string) $minutes, 2, '0', STR_PAD_LEFT)
    ];
}

/**
 * Возвращает список всех категорий.
 */
function get_categories(mysqli $con): array
{
    $sql = "SELECT id, name FROM categories ORDER BY id";
    $result = mysqli_query($con, $sql);

    if (!$result) {
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Возвращает лоты:
 * - все, если фильтры не переданы;
 * - только лоты указанного пользователя;
 * - только лоты выбранной категории;
 * - лоты, подходящие под оба фильтра.
 */
function get_lots(mysqli $con, ?int $user_id = null, ?int $category_id = null): array
{
    $sql = "SELECT
                l.id,
                l.title,
                l.description,
                l.image_path,
                l.starting_price,
                l.expiration_date,
                l.bid_step,
                c.name AS category_name
            FROM lots l
            JOIN categories c ON l.category_id = c.id";

    $conditions = [];
    $params = [];
    $types = '';

    if ($user_id !== null) {
        $conditions[] = 'l.author_id = ?';
        $params[] = $user_id;
        $types .= 'i';
    }

    if ($category_id !== null) {
        $conditions[] = 'l.category_id = ?';
        $params[] = $category_id;
        $types .= 'i';
    }

    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY l.creation_date DESC';

    if (!$params) {
        $result = mysqli_query($con, $sql);

        if (!$result) {
            die('Ошибка получения лотов: ' . mysqli_error($con));
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    $stmt = mysqli_prepare($con, $sql);

    if (!$stmt) {
        die('Ошибка подготовки запроса: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die('Ошибка получения лотов: ' . mysqli_error($con));
    }

    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $lots;
}

/**
 * Возвращает данные одного лота по его ID.
 * Если лота нет — возвращает null.
 */
function get_lot(mysqli $con, int $id): ?array
{
    $sql = 'SELECT
                l.id,
                l.title,
                l.description,
                l.image_path,
                l.starting_price,
                l.expiration_date,
                l.bid_step,
                l.author_id,
                l.winner_id,
                c.name AS category_name
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE l.id = ?';

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $lot = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $lot ?: null;
}

/**
 * Валидирует форму добавления лота.
 */
function validate_lot(array $lot, array $categories): array
{
    $errors = [];

    $required_fields = [
        'title',
        'description',
        'starting_price',
        'bid_step',
        'expiration_date'
    ];

    foreach ($required_fields as $field) {
        if (empty($lot[$field])) {
            $errors[$field] = 'Заполните это поле';
        }
    }

    if (empty($lot['category_id'])) {
        $errors['category_id'] = 'Категория не выбрана';
    } else {
        $ids = array_column($categories, 'id');

        if (!in_array((int) $lot['category_id'], array_map('intval', $ids), true)) {
            $errors['category_id'] = 'Категория не выбрана';
        }
    }

    $number_fields = ['starting_price', 'bid_step'];

    foreach ($number_fields as $field) {
        if (!isset($errors[$field])) {
            if (!ctype_digit((string) $lot[$field]) || (int) $lot[$field] <= 0) {
                $errors[$field] = 'Введите целое положительное число';
            }
        }
    }

    if (!isset($errors['expiration_date'])) {
        if (!is_date_valid($lot['expiration_date'])) {
            $errors['expiration_date'] = 'Введите дату в формате ГГГГ-ММ-ДД';
        } elseif (strtotime($lot['expiration_date']) <= time()) {
            $errors['expiration_date'] = 'Дата должна быть больше текущей';
        }
    }

    return $errors;
}

/**
 * Сохраняет загруженный файл в папку uploads.
 */
function save_upload(array $file, string $uploads_dir): ?string
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('lot_', true) . ($ext ? '.' . $ext : '');

    if (!move_uploaded_file($file['tmp_name'], $uploads_dir . '/' . $name)) {
        return null;
    }

    return 'uploads/' . $name;
}

/**
 * Добавляет новый лот в БД.
 */
function insert_lot(mysqli $con, array $lot, string $image, int $author_id): ?int
{
    $sql = 'INSERT INTO lots
        (title, description, image_path, starting_price, expiration_date, bid_step, category_id, author_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = mysqli_prepare($con, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        'sssisiii',
        $lot['title'],
        $lot['description'],
        $image,
        $lot['starting_price'],
        $lot['expiration_date'],
        $lot['bid_step'],
        $lot['category_id'],
        $author_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return null;
    }

    $id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    return $id;
}

/**
 * Валидирует форму регистрации.
 * Все поля, кроме аватара, обязательны.
 */
function validate_user(mysqli $link, array $user): array
{
    $errors = [];

    $required_fields = ['email', 'password', 'name', 'contacts'];

    foreach ($required_fields as $field) {
        if (empty($user[$field])) {
            $errors[$field] = 'Заполните это поле';
        }
    }

    if (!isset($errors['email'])) {
        if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный e-mail';
        } elseif (get_user_by_email($link, $user['email'])) {
            $errors['email'] = 'Такой e-mail уже зарегистрирован';
        }
    }

    return $errors;
}

/**
 * Ищет пользователя по email.
 */
function get_user_by_email(mysqli $link, string $email): ?array
{
    $sql = 'SELECT id, email, name, password_hash AS password
            FROM users
            WHERE email = ?';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $user ?: null;
}

/**
 * Добавляет нового пользователя и возвращает его ID.
 */
function insert_user(mysqli $link, array $user, string $password): ?int
{
    $sql = 'INSERT INTO users (email, name, password_hash, contacts)
            VALUES (?, ?, ?, ?)';

    $stmt = mysqli_prepare($link, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        'ssss',
        $user['email'],
        $user['name'],
        $password,
        $user['contacts']
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return null;
    }

    $id = mysqli_insert_id($link);
    mysqli_stmt_close($stmt);

    return $id;
}

/**
 * Проверяет email и пароль при входе.
 */
function validate_login(mysqli $link, array $user): array
{
    $errors = [];

    if (empty($user['email'])) {
        $errors['email'] = 'Заполните это поле';
    }

    if (empty($user['password'])) {
        $errors['password'] = 'Заполните это поле';
    }

    if (!$errors) {
        $found_user = get_user_by_email($link, $user['email']);

        if (!$found_user || !password_verify($user['password'], $found_user['password'])) {
            $errors['auth'] = 'Вы ввели неверный логин или пароль';
        }
    }

    return $errors;
}
