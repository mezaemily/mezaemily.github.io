<?php
require_once '../config.php';
protegerPagina();
protegerAdmin();

header('Content-Type: application/json');

$response = ['success' => false];

try {
    // DELETE
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        
        $response['success'] = true;
        $response['message'] = 'Producto eliminado exitosamente';
    }
    // CREATE o UPDATE
    else {
        $id = isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = (float)$_POST['precio'];
        $stock = (int)$_POST['stock'];
        $imagen = trim($_POST['imagen']);
        
        if (empty($nombre) || empty($descripcion) || $precio <= 0) {
            $response['message'] = 'Por favor complete todos los campos correctamente';
        } else {
            if ($id) {
                // UPDATE
                $stmt = $conn->prepare("
                    UPDATE productos 
                    SET nombre = ?, descripcion = ?, precio = ?, stock = ?, imagen = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen, $id]);
                $response['message'] = 'Producto actualizado exitosamente';
            } else {
                // CREATE
                $stmt = $conn->prepare("
                    INSERT INTO productos (nombre, descripcion, precio, stock, imagen) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen]);
                $response['message'] = 'Producto creado exitosamente';
            }
            
            $response['success'] = true;
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
}

echo json_encode($response);
?>