<?php

session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Por favor, introduce un email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del email no es válido.";
    } else {
        $_SESSION['username'] = $email;
        header("Location: buy.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="styles.css">

</head>
<body>


  <header>
    <h1>Identificación</h1>
    <nav>
      <a href="index.php">Volver a Home</a>
    </nav>
  </header>

  <form id="login-form" action="" method="post" novalidate>
    <div>
      <label for="email-input">Email:</label>
      <input id="email-input" name="email" type="email" required placeholder="nombre@dominio.com" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" />

      <?php if (!empty($error)) : ?>
        <div style="color: red; font-weight: bold; margin-bottom: 1em;">
            <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
    </div>
    <button type="submit">Continuar a compra</button>
  </form>

</body>
</html>
