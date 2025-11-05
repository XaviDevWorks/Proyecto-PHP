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

$stmt = $pdo->prepare("SELECT id,total FROM orders WHERE buyer_email=? AND status='PENDING' ORDER BY id DESC LIMIT 1");
$stmt->execute([$email]);
$order = $stmt->fetch();

$order_items = [];
if ($order) {
    $stmt2 = $pdo->prepare("
        SELECT t.label, oi.quantity, oi.unit_price
        FROM order_items oi
        JOIN ticket_types t ON oi.ticket_type_id = t.id
        WHERE oi.order_id=?
    ");
    $stmt2->execute([$order['id']]);
    $order_items = $stmt2->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Vista previa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styles.css">
  <style>
    .cart-item { display: flex; justify-content: space-between; margin-bottom: 4px; }
    .cart-total { margin-top: 10px; font-weight: bold; }
  </style>
</head>
<body>

  <?php include 'flash.php'; ?>

  <header>
    <h1>Vista previa del pedido</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="buy.php">Editar compra</a>
    </nav>
  </header>

  <section aria-labelledby="cart-title">
    <h2 id="cart-title">Resumen</h2>
    <div id="cart-preview">
      <?php if ($order && $order_items) : ?>
            <?php foreach ($order_items as $item) : ?>
          <div class="cart-item">
            <span><?= htmlspecialchars($item['label']) ?> x <?= (int)$item['quantity'] ?></span>
            <span><?= number_format($item['quantity'] * $item['unit_price'], 2) ?> €</span>
          </div>
            <?php endforeach; ?>
        <div class="cart-total"><strong>Total: <?= number_format($order['total'], 2) ?> €</strong></div>
      <?php else : ?>
        <p>No hay pedido pendiente.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($order) : ?>
    <form action="confirm.php" method="post" style="display:inline">
      <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
      <button id="finalize-button" type="submit" name="action" value="confirm">Confirmar compra</button>
    </form>

    <form action="confirm.php" method="post" style="display:inline">
      <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
      <button id="cancel-button" type="submit" name="action" value="cancel">Cancelar pedido</button>
    </form>
  <?php endif; ?>

</body>
</html>
