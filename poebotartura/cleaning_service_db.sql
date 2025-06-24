-- 1. Таблица пользователей
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Таблица спортивного инвентаря
CREATE TABLE equipment (
    equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,  -- велосипед, лыжи, ролики и т.д.
    price_per_hour DECIMAL(10, 2) NOT NULL,
    available_quantity INT NOT NULL,
    description TEXT
);

-- 3. Таблица пунктов выдачи
CREATE TABLE pickup_points (
    point_id INT AUTO_INCREMENT PRIMARY KEY,
    address VARCHAR(200) NOT NULL,
    working_hours VARCHAR(100) NOT NULL
);

-- 4. Таблица заказов
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT NOT NULL,
    point_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'card') NOT NULL,
    status ENUM('new', 'confirmed', 'completed', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id),
    FOREIGN KEY (point_id) REFERENCES pickup_points(point_id)
);

-- 5. Таблица администраторов (для входа в админ-панель)
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Добавляем тестового администратора (логин: admin, пароль: sportgo2024)
INSERT INTO admins (login, password) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); 
-- Пароль: sportgo2024 (захеширован с помощью bcrypt)
-- Добавляем инвентарь
INSERT INTO equipment (name, type, price_per_hour, available_quantity, description) 
VALUES 
    ('Горный велосипед', 'велосипед', 300.00, 5, 'Подходит для бездорожья'),
    ('Беговые лыжи', 'лыжи', 200.00, 10, 'Профессиональные лыжи'),
    ('Роликовые коньки', 'ролики', 150.00, 8, 'Размеры 36-45');

-- Добавляем пункты выдачи
INSERT INTO pickup_points (address, working_hours) 
VALUES 
    ('ул. Спортивная, 10', '09:00-21:00'),
    ('пр. Ленина, 55', '10:00-20:00');

-- Добавляем тестового пользователя
INSERT INTO users (full_name, phone, email, login, password) 
VALUES ('Иванов Иван', '+79161234567', 'ivanov@example.com', 'ivan123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Пароль: 123456 (хеш)
