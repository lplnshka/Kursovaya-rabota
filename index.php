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

// Получение списка салонов из базы данных
$stmt = $conn->prepare("SELECT id, name, description, rating, reviews, photo FROM salons");
$stmt->execute();
$stmt->bind_result($salon_id, $salon_name, $salon_description, $salon_rating, $salon_reviews, $salon_photo);
$salons = [];
while ($stmt->fetch()) {
    $salons[] = [
        "id" => $salon_id,
        "name" => $salon_name,
        "description" => $salon_description,
        "rating" => $salon_rating,
        "reviews" => explode("; ", $salon_reviews),
        "photo" => $salon_photo
    ];
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">LuberAdmire</div>
        <div class="nav-links">
            <a href="profile.php">Профиль</a>
            <a href="#">Избранное</a>
            <a href=".php">Мои отзывы</a>
        </div>
    </div>
    <div>
        <h1>Салоны красоты и СПА в Люберцах</h1>
    </div>
    <div class="salon-list">
        <?php foreach ($salons as $salon): ?>
            <div class="salon">
                <div class="salon-info">
                    <h3><a href="salon.php?id=<?php echo htmlspecialchars($salon['id']); ?>"><?php echo htmlspecialchars($salon['name']); ?></a></h3>
                    <p><?php echo htmlspecialchars($salon['description']); ?></p>
                    <p class="rating">Оценка: <?php echo htmlspecialchars($salon['rating']); ?>/5</p>
                    <div class="reviews">
                        <?php foreach ($salon['reviews'] as $review): ?>
                            <p><?php echo htmlspecialchars($review); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <img src="<?php echo htmlspecialchars($salon['photo']); ?>" alt="<?php echo htmlspecialchars($salon['name']); ?>">
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>