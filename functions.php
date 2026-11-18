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
const HOURS_PER_DAY = '24';

/** Секунд в сутках */
const SECONDS_PER_DAY = HOURS_PER_DAY * SECONDS_PER_HOUR;

/**
 * Форматирует число сгруппированными тысячами и знаком рубля
 */
function to_price(int $num): string {
    return number_format($num, 0, '', ' ') . ' ₽';
}

/**
 * Считает оставшееся до окончания лота время.
 * Возвращает массив строк [«ЧЧ», «ММ»].
 */
function get_dt_range(string $date): array
{
    // Берем дату (YYYY-MM-DD)
    $date = substr(trim($date), 0, 10);
    // Время окончания — 23:59:59 этого дня
    $expire = strtotime($date . ' 23:59:59');
    $now = time() + (2 * SECONDS_PER_HOUR);
    $diff = $expire - $now;

    // когда время вышло
    if ($diff <= 0) {
        return ['00', '00'];
    }

    $hours   = (int) floor($diff / SECONDS_PER_HOUR);
    $minutes = (int) floor(($diff % SECONDS_PER_HOUR) / SECONDS_PER_MINUTE);

    $hours   = str_pad((string)$hours, 2, '0', STR_PAD_LEFT);
    $minutes = str_pad((string)$minutes, 2, '0', STR_PAD_LEFT);

    return [$hours, $minutes];
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
 * Возвращает список лотов, у которых не истёк срок.
 */

function get_lots($con) {
    $sql = "SELECT
                l.id,
                l.title,
                l.description,
                l.image AS image_path,
                l.price AS starting_price,
                l.date_expire AS expiration_date,
                l.step AS bid_step,
                c.name AS category_name
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE l.date_expire >= CURDATE()
            ORDER BY l.created_at DESC";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        die('Ошибка получения лотов: ' . mysqli_error($con));
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
/**
 * Возвращает данные одного лота по его ID.
 * Если лота нет — возвращает null.
 */
function get_lot(mysqli $con, int $id): ?array
{
    $sql = 'SELECT l.id, l.title, l.description, l.image_path, l.starting_price,
                   l.expiration_date, l.bid_step, l.author_id, l.winner_id,
                   c.name AS category_name
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE l.id = ?';

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $lot    = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $lot ?: null;
}


/**
 * Валидирует форму добавления лота.
 */
function validate_lot(array $lot, array $categories): array
{
    $errors = [];

    // проверка основных полей на пустоту, поля обязательные
    $required_fields = ['title', 'description', 'starting_price', 'bid_step', 'expiration_date'];
    
    foreach ($required_fields as $field) { //проходимся по полям
        if (empty($lot[$field])) {
            $errors[$field] = 'Заполните это поле';
        }
    }

    // Проверяем категорию
    if (empty($lot['category_id'])) {
        $errors['category_id'] = 'Категория не выбрана';
    } else {
        $ids = array_column($categories, 'id');
        if (!in_array((int) $lot['category_id'], array_map('intval', $ids), true)) {
            $errors['category_id'] = 'Категория не выбрана';
        }
    }

    // Проверяем, что цена и шаг — положительные
    $number_fields = ['starting_price', 'bid_step'];
    
    foreach ($number_fields as $field) {
        // Проверяем только те поля, которые не пустые (чтобы не перезаписать ошибку "Заполните это поле")
        if (!isset($errors[$field])) {
            if (!ctype_digit((string) $lot[$field]) || (int) $lot[$field] <= 0) {
                $errors[$field] = 'Введите целое положительное число';
            }
        }
    }

    // Проверяем формат даты и то, что она ещё не наступила
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
 * Сохраняет загруженный файл в папку uploads
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
 * Добавляет новый лот в БД
 */
function insert_lot(mysqli $con, array $lot, string $image, int $author_id): ?int
{
    $sql = 'INSERT INTO lots
          (title, description, image, price, date_expire, step, category_id, author_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'sssisiii', //типы данных значений передаваемых в базу
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

    // Проверяем на пустоту все обязательные поля
    $required_fields = ['email', 'password', 'name', 'contacts'];
    
    foreach ($required_fields as $field) { //проходимся по полям
        if (empty($user[$field])) {
            $errors[$field] = 'Заполните это поле';
        }
    }

    // Если email заполнен (нет ошибки пустоты), то проверяем его формат и уникальность
    if (!isset($errors['email'])) {
        if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) { //FILTER_VALIDATE_EMAIL встроенная константа проверяем используя фильтр EMAIL
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
    $sql = 'SELECT id, email, name, password AS password
		FROM users
		WHERE email = ?';
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email); //передаём строку
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result); //получаем данные массивом
    mysqli_stmt_close($stmt);

    return $user ?: null; //если не найдет user, то вернётся false, поэтому возвращаем null
}

/**
 * Добавляет нового пользователя и возвращает его ID.
 */
function insert_user(mysqli $link, array $user, string $password): ?int
{
    $sql = 'INSERT INTO users (email, name, password, contacts)
            VALUES (?, ?, ?, ?)';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'ssss', //все 5 параметров - строки
        $user['email'],
        $user['name'],
        $password,
        $user['contacts'],
    );

    if (!mysqli_stmt_execute($stmt)) { //если запрос не выполнился, то null
        mysqli_stmt_close($stmt);
        return null;
    }

    $id = mysqli_insert_id($link); //получаем id созданого user
    mysqli_stmt_close($stmt);

    return $id;
}
