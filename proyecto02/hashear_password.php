<?php
require_once 'config.php';

echo "<h1>Herramienta para Hashear Contraseñas</h1>";
echo "<hr>";

// Actualizar contraseña del admin
$email_admin = 'admin@dulceencanto.com';
$password_admin = 'admin123';
$password_hash_admin = password_hash($password_admin, PASSWORD_DEFAULT);

try {
    // Verificar si existe el usuario admin
    $stmt = $conn->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE email = ?");
    $stmt->execute([$email_admin]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Actualizar contraseña hasheada
        $stmt = $conn->prepare("UPDATE usuarios SET password = ?, rol = 'admin' WHERE email = ?");
        $stmt->execute([$password_hash_admin, $email_admin]);
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin-bottom: 20px;'>";
        echo "<h3>✓ Contraseña actualizada exitosamente</h3>";
        echo "<p><strong>Usuario:</strong> " . htmlspecialchars($admin['nombre']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p><strong>Rol:</strong> admin</p>";
        echo "<p><strong>Nueva contraseña:</strong> admin123</p>";
        echo "</div>";
    } else {
        // Crear nuevo admin si no existe
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrador', $email_admin, $password_hash_admin, 'admin']);
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin-bottom: 20px;'>";
        echo "<h3>✓ Usuario administrador creado exitosamente</h3>";
        echo "<p><strong>Email:</strong> admin@dulceencanto.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "</div>";
    }
    
    // Actualizar contraseña del usuario de prueba
    $email_usuario = 'cliente@example.com';
    $password_usuario = 'usuario123';
    $password_hash_usuario = password_hash($password_usuario, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
        $stmt->execute([$password_hash_usuario, $email_usuario]);
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin-bottom: 20px;'>";
        echo "<h3>✓ Usuario de prueba actualizado</h3>";
        echo "<p><strong>Email:</strong> cliente@example.com</p>";
        echo "<p><strong>Password:</strong> usuario123</p>";
        echo "</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Cliente Prueba', $email_usuario, $password_hash_usuario, 'usuario']);
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin-bottom: 20px;'>";
        echo "<h3>✓ Usuario de prueba creado</h3>";
        echo "<p><strong>Email:</strong> cliente@example.com</p>";
        echo "<p><strong>Password:</strong> usuario123</p>";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h2>Todos los usuarios en la base de datos:</h2>";
    
    $stmt = $conn->query("SELECT id, nombre, email, rol, DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i') as fecha FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        echo "<thead>";
        echo "<tr style='background: #894962; color: white;'>";
        echo "<th style='padding: 12px; text-align: left;'>ID</th>";
        echo "<th style='padding: 12px; text-align: left;'>Nombre</th>";
        echo "<th style='padding: 12px; text-align: left;'>Email</th>";
        echo "<th style='padding: 12px; text-align: left;'>Rol</th>";
        echo "<th style='padding: 12px; text-align: left;'>Fecha Registro</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($usuarios as $user) {
            $bg_color = $user['rol'] === 'admin' ? '#fff3cd' : 'white';
            echo "<tr style='background: $bg_color; border-bottom: 1px solid #ddd;'>";
            echo "<td style='padding: 10px;'>" . $user['id'] . "</td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($user['nombre']) . "</td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='padding: 10px;'><strong>" . $user['rol'] . "</strong></td>";
            echo "<td style='padding: 10px;'>" . ($user['fecha'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<div style='margin-top: 30px; text-align: center;'>";
    echo "<a href='login.php' style='background: #F09BA2; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; display: inline-block;'>Ir al Login</a>";
    echo "</div>";
    
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24; margin-top: 20px;'>";
    echo "<h4>⚠️ IMPORTANTE:</h4>";
    echo "<p>Por razones de seguridad, <strong>ELIMINA este archivo (hashear_password.php)</strong> después de usarlo.</p>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ Error de Base de Datos</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hashear Contraseñas - Dulce Encanto</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FBC5C5 0%, #957575 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #894962;
            margin-bottom: 10px;
        }
        
        h2, h3, h4 {
            color: #894962;
        }
        
        hr {
            margin: 20px 0;
            border: none;
            border-top: 2px solid #f0f0f0;
        }
        
        table {
            margin-top: 20px;
        }
        
        th {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
</body>
</html>