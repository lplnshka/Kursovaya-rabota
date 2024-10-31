<?php
// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Проверка, авторизован ли пользователь
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получение информации о пользователе
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

// Получение рекомендованных салонов (заглушка)
$recommended_salons = [
    ["id" => 1, "name" => "Салон 1", "photo" => "https://via.placeholder.com/50"],
    ["id" => 2, "name" => "Салон 2", "photo" => "https://via.placeholder.com/50"],
    ["id" => 3, "name" => "Салон 3", "photo" => "https://via.placeholder.com/50"]
];

// Получение избранных салонов
$stmt = $conn->prepare("SELECT salons.id, salons.name, salons.photo FROM favorites JOIN salons ON favorites.salon_id = salons.id WHERE favorites.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($salon_id, $salon_name, $salon_photo);
$saved_salons = [];
while ($stmt->fetch()) {
    $saved_salons[] = ["id" => $salon_id, "name" => $salon_name, "photo" => $salon_photo];
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль</title>
    <link rel="stylesheet" href="profile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">
        <div class="logo">LuberAdmire</div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <a href="#">Избранное</a>
            <a href="profile.php">Профиль</a>
            <a href="logout.php">Выйти</a>
        </div>
    </div>

    <div class="container">
        <div class="profile">
            <div class="profile-icon"></div>
            <h2><?php echo htmlspecialchars($username); ?></h2>
        </div>

        <div class="sections">
            <div class="section">
                <h3>Рекомендованные салоны</h3>
                <ul>
                    <?php foreach ($recommended_salons as $salon): ?>
                        <li>
                            <img src="<?php echo htmlspecialchars($salon['photo']); ?>" alt="<?php echo htmlspecialchars($salon['name']); ?>">
                            <a href="salon.php?id=<?php echo htmlspecialchars($salon['id']); ?>"><?php echo htmlspecialchars($salon['name']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="section">
                <h3>Сохраненные</h3>
                <?php if (empty($saved_salons)): ?>
                    <p>Сохраненных салонов пока нет</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($saved_salons as $salon): ?>
                            <li>
                                <img src="<?php echo htmlspecialchars($salon['photo']); ?>" alt="<?php echo htmlspecialchars($salon['name']); ?>">
                                <a href="salon.php?id=<?php echo htmlspecialchars($salon['id']); ?>"><?php echo htmlspecialchars($salon['name']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>