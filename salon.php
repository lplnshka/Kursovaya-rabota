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

// Получение информации о салоне из базы данных
$salon_id = $_GET['id'] ?? 1; // Получаем ID салона из URL
$stmt = $conn->prepare("SELECT name, description, services, phone, website, reviews, photos FROM salons WHERE id = ?");
$stmt->bind_param("i", $salon_id);
$stmt->execute();
$stmt->bind_result($salon_name, $salon_description, $salon_services, $salon_phone, $salon_website, $salon_reviews, $salon_photos);
$stmt->fetch();
$stmt->close();

// Преобразование строк в массивы
$salon_services = explode(", ", $salon_services);
$salon_reviews = explode("; ", $salon_reviews);
$salon_photos = explode("; ", $salon_photos);

// Проверка, добавлен ли салон в избранное
$stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND salon_id = ?");
$stmt->bind_param("ii", $user_id, $salon_id);
$stmt->execute();
$stmt->store_result();
$is_favorite = $stmt->num_rows > 0;
$stmt->close();

// Обработка формы добавления/удаления из избранного
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['favorite'])) {
    if ($is_favorite) {
        // Удаление из избранного
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND salon_id = ?");
        $stmt->bind_param("ii", $user_id, $salon_id);
        $stmt->execute();
        $stmt->close();
        $is_favorite = false;
    } else {
        // Добавление в избранное
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, salon_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $salon_id);
        $stmt->execute();
        $stmt->close();
        $is_favorite = true;
    }
}

// Обработка формы отзыва
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['review'])) {
    $review = $_POST['review'];
    // Добавление отзыва в базу данных
    $stmt = $conn->prepare("INSERT INTO reviews (salon_id, user_id, review) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $salon_id, $user_id, $review);
    $stmt->execute();
    $stmt->close();
    echo "Спасибо за отзыв!";
}

// Получение отзывов о салоне
$stmt = $conn->prepare("SELECT users.username, reviews.review FROM reviews JOIN users ON reviews.user_id = users.id WHERE reviews.salon_id = ?");
$stmt->bind_param("i", $salon_id);
$stmt->execute();
$stmt->bind_result($review_username, $review_text);
$reviews = [];
while ($stmt->fetch()) {
    $reviews[] = ["username" => $review_username, "review" => $review_text];
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($salon_name); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <style>
        .salon-details {
            display: flex;
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        .salon-info {
            flex: 1;
            margin-right: 20px;
        }

        .salon-info h2 {
            margin: 0 0 20px;
            font-size: 24px;
            color: #262626;
        }

        .salon-info p {
            margin: 0 0 20px;
            color: #4a4a4a;
        }

        .salon-info ul {
            list-style: none;
            padding: 0;
            margin: 0 0 20px;
        }

        .salon-info li {
            margin-bottom: 10px;
            color: #4a4a4a;
        }

        .salon-info .contacts {
            margin-bottom: 20px;
        }

        .salon-info .contacts a {
            color: #3897f0;
            text-decoration: none;
        }

        .salon-info .contacts a:hover {
            text-decoration: underline;
        }

        .salon-info .reviews {
            margin-bottom: 20px;
        }

        .salon-info .reviews h3 {
            margin: 0 0 20px;
            font-size: 20px;
            color: #262626;
        }

        .salon-info .reviews p {
            margin: 0 0 10px;
            color: #4a4a4a;
        }

        .salon-photos {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .salon-photos .main-photo {
            width: 800px;
            height: 800px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .salon-photos .small-photos {
            display: flex;
            justify-content: space-between;
            width: 800px;
        }

        .salon-photos .small-photos img {
            width: 260px;
            height: 260px;
            object-fit: cover;
        }

        .salon-info form {
            margin-top: 20px;
        }

        .salon-info textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #dbdbdb;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .salon-info input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #3897f0;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        .salon-info input[type="submit"]:hover {
            background: #357ae8;
        }

        .favorite-button {
            margin-top: 20px;
            padding: 10px;
            background: #3897f0;
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .favorite-button:hover {
            background: #357ae8;
        }
    </style>
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

    <div class="salon-details">
        <div class="salon-info">
            <h2><?php echo htmlspecialchars($salon_name); ?></h2>
            <p><?php echo htmlspecialchars($salon_description); ?></p>

            <h3>Услуги</h3>
            <ul>
                <?php foreach ($salon_services as $service): ?>
                    <li><?php echo htmlspecialchars($service); ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="contacts">
                <p>Телефон: <a href="tel:<?php echo htmlspecialchars($salon_phone); ?>"><?php echo htmlspecialchars($salon_phone); ?></a></p>
                <p>Сайт: <a href="<?php echo htmlspecialchars($salon_website); ?>" target="_blank"><?php echo htmlspecialchars($salon_website); ?></a></p>
                <p>WhatsApp: <a href="https://wa.me/<?php echo htmlspecialchars($salon_phone); ?>" target="_blank">Написать в WhatsApp</a></p>
            </div>

            <div class="reviews">
                <h3>Отзывы</h3>
                <?php if (empty($reviews)): ?>
                    <p>Отзывов пока нет.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <p><strong><?php echo htmlspecialchars($review['username']); ?>:</strong> <?php echo htmlspecialchars($review['review']); ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo htmlspecialchars($salon_id); ?>">
                <textarea name="review" placeholder="Оставьте свой отзыв" required></textarea>
                <input type="submit" value="Отправить отзыв">
            </form>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo htmlspecialchars($salon_id); ?>">
                <input type="hidden" name="favorite" value="1">
                <input type="submit" value="<?php echo $is_favorite ? 'Удалить из избранного' : 'Добавить в избранное'; ?>" class="favorite-button">
            </form>
        </div>

        <div class="salon-photos">
            <img src="<?php echo htmlspecialchars($salon_photos[0]); ?>" alt="Фото салона" class="main-photo">
            <div class="small-photos">
                <?php for ($i = 1; $i < count($salon_photos); $i++): ?>
                    <img src="<?php echo htmlspecialchars($salon_photos[$i]); ?>" alt="Фото салона">
                <?php endfor; ?>
            </div>
        </div>
    </div>
</body>
</html>