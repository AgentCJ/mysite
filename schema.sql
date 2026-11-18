CREATE TABLE IF NOT EXISTS categories
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL,
    UNIQUE KEY unique_name(name)
);

CREATE TABLE IF NOT EXISTS users
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    contacts TEXT NOT NULL,
    UNIQUE KEY unique_email(email)

);

CREATE TABLE IF NOT EXISTS lots
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    starting_price INT NOT NULL,
    expiration_date DATE NOT NULL,
    bid_step INT NOT NULL,
    author_id INT NOT NULL,
    winner_id INT DEFAULT NULL,
    category_id INT NOT NULL, 

    CONSTRAINT fk_lots_autor FOREIGN KEY(author_id) REFERENCES users(id),
    CONSTRAINT fk_lots_winner FOREIGN KEY(winner_id) REFERENCES users(id),
    CONSTRAINT fk_lots_category FOREIGN KEY(category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS bids
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    bid_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount INT NOT NULL,
    user_id INT NOT NULL,
    lot_id INT NOT NULL,

    CONSTRAINT fk_bids_user FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_bids_lot FOREIGN KEY(lot_id) REFERENCES lots(id)
);

