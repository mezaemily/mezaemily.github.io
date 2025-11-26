<?php
require_once 'config.php';
protegerPagina();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$action = $_POST['action'] ?? '';
$response = ['success' => false];

try {
    switch ($action) {
        case 'agregar':
            $producto_id = (int)$_POST['producto_id'];
            $usuario_id = $_SESSION['usuario_id'];
            
            // Verificar stock
            $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ?");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto || $producto['stock'] <= 0) {
                $response['message'] = 'Producto sin stock disponible';
                break;
            }
            
            // Verificar si ya existe en el carrito
            $stmt = $conn->prepare("SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
            $stmt->execute([$usuario_id, $producto_id]);
            $item_existente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item_existente) {
                // Actualizar cantidad
                if ($item_existente['cantidad'] < $producto['stock']) {
                    $stmt = $conn->prepare("UPDATE carrito SET cantidad = cantidad + 1 WHERE id = ?");
                    $stmt->execute([$item_existente['id']]);
                    $response['success'] = true;
                } else {
                    $response['message'] = 'No hay más stock disponible';
                }
            } else {
                // Insertar nuevo item
                $stmt = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, 1)");
                $stmt->execute([$usuario_id, $producto_id]);
                $response['success'] = true;
            }
            break;
            
        case 'eliminar':
            $item_id = (int)$_POST['item_id'];
            $usuario_id = $_SESSION['usuario_id'];
            
            $stmt = $conn->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$item_id, $usuario_id]);
            $response['success'] = true;
            break;
            
        case 'actualizar':
            $item_id = (int)$_POST['item_id'];
            $cambio = (int)$_POST['cambio'];
            $usuario_id = $_SESSION['usuario_id'];
            
            // Obtener item actual
            $stmt = $conn->prepare("
                SELECT c.cantidad, p.stock 
                FROM carrito c 
                JOIN productos p ON c.producto_id = p.id 
                WHERE c.id = ? AND c.usuario_id = ?
            ");
            $stmt->execute([$item_id, $usuario_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item) {
                $nueva_cantidad = $item['cantidad'] + $cambio;
                
                if ($nueva_cantidad <= 0) {
                    // Eliminar si la cantidad es 0 o menos
                    $stmt = $conn->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
                    $stmt->execute([$item_id, $usuario_id]);
                    $response['success'] = true;
                } elseif ($nueva_cantidad <= $item['stock']) {
                    // Actualizar cantidad
                    $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?");
                    $stmt->execute([$nueva_cantidad, $item_id, $usuario_id]);
                    $response['success'] = true;
                } else {
                    $response['message'] = 'No hay suficiente stock';
                }
            }
            break;
            
        case 'finalizar':
            $usuario_id = $_SESSION['usuario_id'];
            
            // Obtener items del carrito
            $stmt = $conn->prepare("
                SELECT c.*, p.precio, p.stock 
                FROM carrito c 
                JOIN productos p ON c.producto_id = p.id 
                WHERE c.usuario_id = ?
            ");
            $stmt->execute([$usuario_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($items)) {
                $response['message'] = 'El carrito está vacío';
                break;
            }
            
            // Calcular total y verificar stock
            $total = 0;
            foreach ($items as $item) {
                if ($item['cantidad'] > $item['stock']) {
                    $response['message'] = 'Stock insuficiente para completar la compra';
                    break 2;
                }
                $total += $item['precio'] * $item['cantidad'];
            }
            
            // Iniciar transacción
            $conn->beginTransaction();
            
            try {
                // Crear pedido
                $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
                $stmt->execute([$usuario_id, $total]);
                $pedido_id = $conn->lastInsertId();
                
                // Crear detalles del pedido y actualizar stock
                foreach ($items as $item) {
                    // Insertar detalle
                    $stmt = $conn->prepare("
                        INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$pedido_id, $item['producto_id'], $item['cantidad'], $item['precio']]);
                    
                    // Actualizar stock
                    $stmt = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['cantidad'], $item['producto_id']]);
                }
                
                // Vaciar carrito
                $stmt = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
                $stmt->execute([$usuario_id]);
                
                $conn->commit();
                $response['success'] = true;
                $response['pedido_id'] = $pedido_id;
                
            } catch (Exception $e) {
                $conn->rollBack();
                $response['message'] = 'Error al procesar el pedido';
            }
            break;
            
        default:
            $response['message'] = 'Acción no válida';
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos';
}

echo json_encode($response);
?>