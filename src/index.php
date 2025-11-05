<?php
require_once __DIR__ . '/db.php';
use App\DB\Database;

$pdo = null;
try {
    $pdo = Database::getInstance()->getConnection();
} catch (Throwable $e) {
    die("Error de conexión: " . $e->getMessage());
}

$filter = $_GET['m'] ?? 'all';
$params = [];
$sql = "SELECT name, description, maintenance FROM attractions";
if ($filter === 'maintenance') {
    $sql .= " WHERE maintenance = ?";
    $params[] = 1;
} elseif ($filter === 'available') {
    $sql .= " WHERE maintenance = ?";
    $params[] = 0;
}
$sql .= " ORDER BY name";

$attractions = [];
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attractions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <header>
    <h1>Parque Espacial</h1>
    <nav>
      <a href="login.php">Iniciar compra</a>
    </nav>
  </header>

  <figure>
    <img id="theme-image" src="/public/webb.jpg" alt="Imagen temática del parque (espacio)" />
    <figcaption>Explora el universo desde nuestro parque</figcaption>
  </figure>

  <section aria-labelledby="filtro-title">
    <h2 id="filtro-title">Filtrar atracciones</h2>
    <form method="get" style="display:inline">
      <label for="filter-maintenance">Estado:</label>
      <select id="filter-maintenance" name="m" onchange="this.form.submit()">
        <option value="all"<?= $filter === 'all' ? ' selected' : '' ?>>Todas</option>
        <option value="maintenance"<?= $filter === 'maintenance' ? ' selected' : '' ?>>En mantenimiento</option>
        <option value="available"<?= $filter === 'available' ? ' selected' : '' ?>>Disponibles</option>
      </select>
    </form>
    <p>Mostrando: <strong id="attraction-count"><?= count($attractions) ?></strong></p>
  </section>

  <section aria-labelledby="lista-title">
    <h2 id="lista-title">Atracciones</h2>
    <div id="attraction-list">
      <?php if (empty($attractions)) : ?>
        <p>No hay atracciones disponibles.</p>
      <?php else : ?>
          <?php foreach ($attractions as $a) :
                $isMaintenance = (int)$a['maintenance'] === 1;
                $badgeClass = $isMaintenance ? 'm' : 'ok';
                $badgeText = $isMaintenance ? 'En mantenimiento' : 'Disponible';
                ?>
          <article class="attraction">
            <h3><?= htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string)$a['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
          </article>
          <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

</body>
</html>
