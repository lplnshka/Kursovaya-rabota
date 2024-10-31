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

// Обработка формы регистрации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверка на пустые поля
    if (empty($username) || empty($email) || empty($password)) {
        echo "Please fill in all fields.";
    } else {
        // Хеширование пароля
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Подготовленное выражение
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Установка сессии
            session_start();
            $_SESSION['user_id'] = $stmt->insert_id;

            // Перенаправление на страницу профиля после успешной регистрации
            header("Location: profile.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="logreg.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>LuberAdmire</h1>
        <h2>Создайте профиль</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="text" id="username" name="username" placeholder="Username" required><br><br>
            <input type="email" id="email" name="email" placeholder="Email" required><br><br>
            <input type="password" id="password" name="password" placeholder="Password" required><br><br>
            <input type="submit" value="Зарегистрироваться">
        </form>
        <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div>
</body>
</html>