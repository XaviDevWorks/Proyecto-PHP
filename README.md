# Taquilla Online — Practica PHP

## Temática del trabajo: Espacio

---

## 1. Cómo se conecta la base de datos (Singleton)

La conexión a la base de datos se implementa mediante el patrón Singleton en el archivo `src/db.php`.

La clase `Database` tiene:
- **Constructor privado**: Impide crear instancias directamente con `new Database()`.
- **Método estático `getInstance()`**: Proporciona acceso a la única instancia de la conexión.
- **Propiedad estática `$instance`**: Almacena la única instancia de la clase.
- **Método `getConnection()`**: Devuelve el objeto `\PDO` configurado.

**Ejemplo de uso:**
```php
require_once __DIR__ . '/db.php';
use App\DB\Database;

$pdo = Database::getInstance()->getConnection();
// A partir de aquí, $pdo es reutilizable en toda la aplicación
```

**Beneficios:**
- Una única conexión reutilizable en toda la aplicación.
- Configuración centralizada (variables de entorno).
- Control centralizado de errores.
- Protección contra clonado y deserialización mediante `__clone()` y `__wakeup()`.

---

## 2. Cómo se recupera el pedido pendiente

En `src/preview.php`, se recupera el último pedido PENDING del usuario actualmente logueado mediante la siguiente consulta:

```php
// Buscar el último pedido PENDING del usuario
$stmt = $pdo->prepare("SELECT id,total FROM orders WHERE buyer_email=? AND status='PENDING' ORDER BY id DESC LIMIT 1");
$stmt->execute([$email]);
$order = $stmt->fetch();
```

**Explicación:**
- Se usa `buyer_email` para identificar al usuario (almacenado en `$_SESSION['username']`).
- El filtro `status='PENDING'` asegura que solo se recuperan pedidos pendientes.
- `ORDER BY id DESC LIMIT 1` obtiene el pedido más reciente.
- Una vez obtenido el pedido, se recuperan sus items con un JOIN:

```php
$stmt2 = $pdo->prepare("
    SELECT t.label, oi.quantity, oi.unit_price
    FROM order_items oi
    JOIN ticket_types t ON oi.ticket_type_id = t.id
    WHERE oi.order_id=?
");
$stmt2->execute([$order['id']]);
$order_items = $stmt2->fetchAll();
```

---

## 3. Ejemplo de una consulta con prepared statement

Todas las consultas a la base de datos utilizan sentencias preparadas para prevenir inyección SQL. Ejemplo completo del proceso de compra en `src/buy.php`:

```php
// 1. Preparar la consulta con placeholders (?)
$stmtOrder = $pdo->prepare("INSERT INTO orders(buyer_email,total,status) VALUES(?,?, 'PENDING')");

// 2. Ejecutar con los valores reales como array
$stmtOrder->execute([$email, $total]);

// 3. Obtener el ID del pedido creado
$order_id = $pdo->lastInsertId();

// 4. Insertar items del pedido (también con prepared statement)
$stmtItem = $pdo->prepare("INSERT INTO order_items(order_id,ticket_type_id,quantity,unit_price) VALUES(?,?,?,?)");
foreach ($quantities as $id => $qty) {
    if ($qty > 0) {

        $stmtItem->execute([$order_id, $id, $qty, $priceMap[$id]]);
    }
}
```

**Ventajas de las prepared statements:**
- Previenen inyección SQL al separar la estructura de la consulta de los datos.
- Mejoran el rendimiento al reutilizar el plan de ejecución.
- Permiten validar tipos de datos antes de ejecutar.

---

## 4. Enlace al video demostración



---
