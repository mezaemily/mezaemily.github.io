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
        
        // No permitir eliminar el propio usuario
        if ($id === $_SESSION['usuario_id']) {
            $response['message'] = 'No puedes eliminar tu propia cuenta';
        } else {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            $response['success'] = true;
            $response['message'] = 'Usuario eliminado exitosamente';
        }
    }
    // UPDATE
    else {
        $id = (int)$_POST['id'];
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $rol = $_POST['rol'];
        
        if (empty($nombre) || empty($email)) {
            $response['message'] = 'Por favor complete todos los campos';
        } elseif (!in_array($rol, ['usuario', 'admin'])) {
            $response['message'] = 'Rol inválido';
        } else {
            // Verificar si el email ya existe para otro usuario
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            
            if ($stmt->fetch()) {
                $response['message'] = 'Este email ya está registrado por otro usuario';
            } else {
                $stmt = $conn->prepare("
                    UPDATE usuarios 
                    SET nombre = ?, email = ?, rol = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$nombre, $email, $rol, $id]);
                
                $response['success'] = true;
                $response['message'] = 'Usuario actualizado exitosamente';
            }
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
}

echo json_encode($response);
?>