-- Добавление данных в таблицу категорий
INSERT INTO categories (name, code) VALUES
('Доски и лыжи', 'boards'),
('Крепления', 'attachment'),
('Ботинки', 'boots'),
('Одежда', 'clothing'),
('Инструменты', 'tools'),
('Разное', 'other');

-- Добавление данных в таблицу пользователей
INSERT INTO users (email, name, password_hash, contacts) VALUES
('gogolmogol@gmail.com','Николай','$2b$10$KYVbZ5JFVfqu0oV98LnF5eTk4QTe2e4PQG7QNYfhumEpGdi/867AO','Телефон: +78004444555'),
('programmer@gmail.com','Данил','$2y$12$wNt7lt/xf8wRJgPU7kK2juGrirhHK4gdb0NiCRdsSoAxqQoNbiluu','Телефон: +78004444557');

-- Добавление данных в таблицу лотов
INSERT INTO lots (title, description, image_path, starting_price, expiration_date, bid_step, author_id, category_id) VALUES
('2014 Rossignol District Snowboard', 'Сноуборд в хорошем состоянии', 'img/lot-1.jpg', 10999, '2026-04-28', 100, 1, 1),
('DC Ply Mens 2016/2017 Snowboard', 'Отличный сноуборд', 'img/lot-2.jpg', 159999,'2026-05-12', 100, 1, 1),
('Крепления Union Contact Pro 2015 года размер L/XL', 'Крепления размер L/XL', 'img/lot-3.jpg', 8000, '2026-05-09', 50, 2, 2),
('Ботинки для сноуборда DC Mutiny Charocal', 'Удобные ботинки', 'img/lot-4.jpg', 10999, '2026-05-07', 100, 2, 3),
('Куртка для сноуборда DC Mutiny Charocal','Теплая куртка','img/lot-5.jpg', 7500, '2026-05-14', 50, 1, 4),
('Маска Oakley Canopy', 'Новая маска', 'img/lot-6.jpg', 5400, '2026-05-14', 50, 2, 6);

-- Добавление данных в таблицу ставок
INSERT INTO bids (amount, user_id, lot_id) VALUES
(11000, 2, 1),
(12000, 1, 1),
(8500, 1, 3),
(9000, 2, 3);

-- Запрос на список всех категорий
SELECT * FROM categories;

-- Запрос на список лотов, которые еще не истекли отсортированных по дате публикации, от новых к старым
SELECT lots.title, lots.starting_price, lots.image_path, categories.name AS category_name, lots.expiration_date
FROM lots
JOIN categories ON lots.category_id = categories.id
WHERE lots.expiration_date >= CURDATE()
ORDER BY lots.creation_date DESC;

-- Запрос на информацию о лоте по его ID
SELECT lots.title, lots.description, lots.starting_price, lots.image_path, categories.name AS category_name, lots.expiration_date
FROM lots
JOIN categories ON lots.category_id = categories.id
WHERE lots.id = 1;

-- Запрос на обновление названия лота по его идентификатору
UPDATE lots
SET title = "Новое название"
WHERE id = 1;

-- Запрос на список ставок для лота по его идентификатору с сортировкой по дате
SELECT bids.bid_date, bids.amount, lots.title, users.name
FROM bids
JOIN lots ON bids.lot_id = lots.id
JOIN users ON bids.user_id = users.id
WHERE lots.id = 1
ORDER BY bids.bid_date DESC;