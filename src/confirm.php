<?php
session_start();
require_once __DIR__ . '/db.php';
use App\DB\Database;

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$pdo = Database::getInstance()->getConnection();
$email = $_SESSION['username'];

$order_number = "—";

if (!isset($_SESSION['flash_message'])) {
    $_SESSION['flash_message'] = "Parámetros incorrectos";
}

if (isset($_POST['order_id'], $_POST['action'])) {
    $order_id = (int)$_POST['order_id'];
    $action = $_POST['action'];

    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id=? AND buyer_email=?");
    $stmt->execute([$order_id, $email]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order || $order['status'] !== 'PENDING') {
        $_SESSION['flash_message'] = "Pedido no encontrado o ya procesado.";
    } else {
        if ($action === 'confirm') {
            $new_status = 'COMPLETED';
            $_SESSION['flash_message'] = "Compra confirmada.";
        } elseif ($action === 'cancel') {
            $new_status = 'CANCELLED';
            $_SESSION['flash_message'] = "Pedido cancelado.";
        } else {
            $_SESSION['flash_message'] = "Acción inválida.";
        }

        if (isset($new_status)) {
            $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
            $stmt->execute([$new_status, $order_id]);
            if ($new_status === 'COMPLETED') {
                $order_number = $order_id;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Confirmación</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <?php include 'flash.php'; ?>

  <header>
    <h1>Resultado de la operación</h1>
    <nav>
      <a href="index.php">Volver a Home</a>
      <a href="buy.php">Nueva compra</a>
    </nav>
  </header>

  <main>
    <p>Tu número de pedido es:
      <strong id="order-number"><?= htmlspecialchars($order_number) ?></strong>
    </p>
  </main>

</body>
</html>
