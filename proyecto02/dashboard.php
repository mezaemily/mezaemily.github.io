<?php
require_once 'config.php';
protegerPagina();

// Si es admin, redirigir al dashboard de admin
if (esAdmin()) {
    header("Location: admin/dashboard.php");
    exit();
}

// Obtener productos
$stmt = $conn->query("SELECT * FROM productos WHERE stock > 0 ORDER BY fecha_creacion DESC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener items del carrito
$stmt = $conn->prepare("
    SELECT c.*, p.nombre, p.precio, p.imagen, p.stock 
    FROM carrito c 
    JOIN productos p ON c.producto_id = p.id 
    WHERE c.usuario_id = ?
");
$stmt->execute([$_SESSION['usuario_id']]);
$carrito_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_carrito = 0;
foreach ($carrito_items as $item) {
    $total_carrito += $item['precio'] * $item['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dulce Encanto</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f7fb;
        }
        
        .navbar {
            background: white;
            padding: 1.5rem 3rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-family: 'Great Vibes', cursive;
            color: #894962;
            font-size: 2rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .cart-icon {
            position: relative;
            cursor: pointer;
            font-size: 1.5rem;
            color: #894962;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #F09BA2;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .btn-logout {
            background: #F09BA2;
            color: white;
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: #894962;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .welcome h2 {
            color: #894962;
            margin-bottom: 0.5rem;
        }
        
        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .producto-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .producto-card:hover {
            transform: translateY(-5px);
        }
        
        .producto-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .producto-info {
            padding: 1.5rem;
        }
        
        .producto-info h3 {
            color: #894962;
            margin-bottom: 0.5rem;
        }
        
        .precio {
            color: #F09BA2;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 1rem 0;
        }
        
        .stock-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .btn-agregar {
            width: 100%;
            background: #F09BA2;
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-agregar:hover {
            background: #894962;
        }
        
        /* Modal del carrito */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 400px;
            background: white;
            padding: 2rem;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .close-modal {
            font-size: 2rem;
            cursor: pointer;
            color: #894962;
        }
        
        .carrito-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 1rem;
        }
        
        .carrito-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .carrito-item-info {
            flex: 1;
        }
        
        .carrito-total {
            background: #f7f7fb;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
        }
        
        .btn-finalizar {
            width: 100%;
            background: #894962;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .btn-finalizar:hover {
            opacity: 0.9;
        }
        
        .btn-eliminar {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.3rem 0.8rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .cantidad-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .cantidad-control button {
            background: #F09BA2;
            color: white;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
            color: #666;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1 class="logo">Dulce Encanto</h1>
        <div class="user-info">
            <div class="cart-icon" onclick="abrirCarrito()">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cartCount"><?php echo count($carrito_items); ?></span>
            </div>
            <span>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome">
            <h2>Bienvenido a Dulce Encanto</h2>
            <p>Explora nuestros deliciosos pasteles y postres</p>
        </div>
        
        <div id="alertContainer"></div>
        
        <div class="productos-grid">
            <?php foreach ($productos as $producto): ?>
            <div class="producto-card">
                <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                <div class="producto-info">
                    <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                    <p class="precio">$<?php echo number_format($producto['precio'], 2); ?></p>
                    <p class="stock-info">Stock disponible: <?php echo $producto['stock']; ?></p>
                    <button class="btn-agregar" onclick="agregarAlCarrito(<?php echo $producto['id']; ?>)">
                        <i class="fas fa-cart-plus"></i> Agregar al carrito
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Modal del carrito -->
    <div id="carritoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Mi Carrito</h2>
                <span class="close-modal" onclick="cerrarCarrito()">&times;</span>
            </div>
            
            <div id="carritoItems">
                <?php if (empty($carrito_items)): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc;"></i>
                        <p>Tu carrito está vacío</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($carrito_items as $item): ?>
                    <div class="carrito-item" id="item-<?php echo $item['id']; ?>">
                        <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                        <div class="carrito-item-info">
                            <h4><?php echo htmlspecialchars($item['nombre']); ?></h4>
                            <p>$<?php echo number_format($item['precio'], 2); ?></p>
                            <div class="cantidad-control">
                                <button onclick="actualizarCantidad(<?php echo $item['id']; ?>, -1)">-</button>
                                <span id="cantidad-<?php echo $item['id']; ?>"><?php echo $item['cantidad']; ?></span>
                                <button onclick="actualizarCantidad(<?php echo $item['id']; ?>, 1)">+</button>
                            </div>
                            <button class="btn-eliminar" onclick="eliminarDelCarrito(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="carrito-total">
                        <h3>Total: $<span id="totalCarrito"><?php echo number_format($total_carrito, 2); ?></span></h3>
                    </div>
                    
                    <button class="btn-finalizar" onclick="finalizarCompra()">
                        <i class="fas fa-check"></i> Finalizar Compra
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function abrirCarrito() {
            document.getElementById('carritoModal').style.display = 'block';
        }
        
        function cerrarCarrito() {
            document.getElementById('carritoModal').style.display = 'none';
        }
        
        function mostrarAlerta(mensaje, tipo) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${tipo}`;
            alert.textContent = mensaje;
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }
        
        async function agregarAlCarrito(productoId) {
            try {
                const response = await fetch('carrito_actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=agregar&producto_id=${productoId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mostrarAlerta('Producto agregado al carrito', 'success');
                    location.reload();
                } else {
                    mostrarAlerta(result.message || 'Error al agregar al carrito', 'error');
                }
            } catch (error) {
                mostrarAlerta('Error al agregar al carrito', 'error');
            }
        }
        
        async function eliminarDelCarrito(itemId) {
            if (!confirm('¿Eliminar este producto del carrito?')) return;
            
            try {
                const response = await fetch('carrito_actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=eliminar&item_id=${itemId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    mostrarAlerta(result.message || 'Error al eliminar', 'error');
                }
            } catch (error) {
                mostrarAlerta('Error al eliminar', 'error');
            }
        }
        
        async function actualizarCantidad(itemId, cambio) {
            try {
                const response = await fetch('carrito_actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=actualizar&item_id=${itemId}&cambio=${cambio}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    mostrarAlerta(result.message || 'Error al actualizar', 'error');
                }
            } catch (error) {
                mostrarAlerta('Error al actualizar cantidad', 'error');
            }
        }
        
        async function finalizarCompra() {
            if (!confirm('¿Confirmar la compra?')) return;
            
            try {
                const response = await fetch('carrito_actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=finalizar'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('¡Compra realizada con éxito! Gracias por tu pedido.');
                    location.reload();
                } else {
                    mostrarAlerta(result.message || 'Error al finalizar compra', 'error');
                }
            } catch (error) {
                mostrarAlerta('Error al finalizar compra', 'error');
            }
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('carritoModal');
            if (event.target === modal) {
                cerrarCarrito();
            }
        }
    </script>
</body>
</html>