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

// Обработка формы входа
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверка на пустые поля
    if (empty($email) || empty($password)) {
        echo "Please fill in all fields.";
    } else {
        // Подготовленное выражение
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            // Проверка пароля
            if (password_verify($password, $hashed_password)) {
                // Установка сессии
                session_start();
                $_SESSION['user_id'] = $user_id;

                // Перенаправление на страницу профиля после успешного входа
                header("Location: profile.php");
                exit();
            } else {
                echo "Неправильный логин или пароль.";
            }
        } else {
            echo "Неправильный логин или пароль.";
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
    <title>Вход</title>
    <link rel="stylesheet" href="logreg.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
    <h1>LuberAdmire</h1>
        <h2>Авторизация</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="email" id="email" name="email" placeholder="Email" required><br><br>
            <input type="password" id="password" name="password" placeholder="Password" required><br><br>
            <input type="submit" value="Войти">
        </form>
        <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>
</body>
</html>