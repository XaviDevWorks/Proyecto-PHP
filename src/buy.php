<?php
session_start();
require_once __DIR__ . '/db.php';
use App\DB\Database;

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$email = $_SESSION['username'];
$pdo = Database::getInstance()->getConnection();

$stmt = $pdo->prepare("SELECT id,label,price FROM ticket_types ORDER BY id ASC");
$stmt->execute();
$ticket_types = $stmt->fetchAll();

$stmtPending = $pdo->prepare("SELECT id FROM orders WHERE buyer_email=? AND status='PENDING' LIMIT 1");
$stmtPending->execute([$email]);
$pendingOrder = $stmtPending->fetch();

if ($pendingOrder) {
    $_SESSION['flash_message'] = "Ya tienes un pedido pendiente (ID: {$pendingOrder['id']}). Revísalo antes de crear uno nuevo.";
    header("Location: preview.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantities = $errors = [];
    foreach ($ticket_types as $t) {
        $name = 'quantity-' . $t['id'];
        $qty = isset($_POST[$name]) ? (int)$_POST[$name] : 0;
        if ($qty < 0 || $qty > 100) {
            $errors[] = "Cantidad inválida ID {$t['id']}";
        }
        $quantities[$t['id']] = $qty;
    }
    if (array_sum($quantities) === 0) {
        $errors[] = "Selecciona al menos una entrada";
    }

    if (empty($errors)) {
        try {
            $priceMap = [];
            foreach ($ticket_types as $t) {
                $priceMap[$t['id']] = (float)$t['price'];
            }

            $pdo->beginTransaction();
            $total = 0;
            foreach ($quantities as $id => $qty) {
                if ($qty > 0) {
                    $total += $priceMap[$id] * $qty;
                }
            }
            $stmtOrder = $pdo->prepare("INSERT INTO orders(buyer_email,total,status) VALUES(?,?, 'PENDING')");
            $stmtOrder->execute([$email, $total]);
            $order_id = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare("INSERT INTO order_items(order_id,ticket_type_id,quantity,unit_price) VALUES(?,?,?,?)");
            foreach ($quantities as $id => $qty) {
                if ($qty > 0) {
                    $stmtItem->execute([$order_id, $id, $qty, $priceMap[$id]]);
                }
            }

            $pdo->commit();
            $_SESSION['flash_message'] = "Pedido creado. Número de pedido: $order_id";
            header("Location: preview.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_message'] = "Error: " . $e->getMessage();
            header("Location: buy.php");
            exit();
        }
    } else {
        $_SESSION['flash_message'] = implode("<br>", $errors);
        header("Location: buy.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Compra</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styles.css">
  <style>
    .ticket-row { margin-bottom: 8px; }
    .ticket-price { font-weight: bold; margin-left: 5px; }
  </style>
</head>
<body>

  <?php include 'flash.php'; ?>

  <header>
    <h1>Compra de entradas</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="login.php">Cambiar de usuario</a>
    </nav>
  </header>

    <form id="buy-form" action="" method="post" novalidate>
    <p>Selecciona cantidades (0–100). El precio se muestra junto al tipo:</p>

    <fieldset>
      <legend>Tipos de entrada</legend>
      <?php foreach ($ticket_types as $t) : ?>
        <div class="ticket-row">
          <label for="quantity-<?= $t['id'] ?>" id="ticket-type-<?= $t['id'] ?>">
            <?= htmlspecialchars($t['label']) ?> —
            <span class="ticket-price"><?= number_format($t['price'], 2) ?>€</span>
          </label>
          <input
            id="quantity-<?= $t['id'] ?>"
            name="quantity-<?= $t['id'] ?>"
            type="number"
            min="0"
            max="100"
            step="1"
            value="0"
            inputmode="numeric"
          />
          <input type="hidden" name="ticket_ids[]" value="<?= $t['id'] ?>" />
        </div>
      <?php endforeach; ?>
    </fieldset>

    <button type="submit">Ir a vista previa</button>
  </form>

</body>
</html>
