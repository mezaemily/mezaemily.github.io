<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Por favor complete todos los campos";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['rol'] = $usuario['rol'];
               
                // Redirigir según el rol
                if ($usuario['rol'] === 'admin') {
                    header("Location: admin/dashboard.php");
                    
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Email o contraseña incorrectos";
            }
        } catch(PDOException $e) {
            $error = "Error al iniciar sesión";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dulce Encanto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            background: linear-gradient(135deg, #FBC5C5 0%, #957575 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
        }
        
        .logo {
            font-family: 'Great Vibes', cursive;
            color: #894962;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #894962;
            font-weight: 600;
        }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            color: #894962;
            font-size: 1.1rem;
            pointer-events: none;
        }
        
        input {
            width: 100%;
            padding: 0.8rem 0.8rem 0.8rem 3rem;
            border: 2px solid #F09BA2;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #894962;
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            background: #F09BA2;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #894962;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #894962;
        }
        
        .register-link a {
            color: #F09BA2;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .back-home a {
            color: #894962;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .back-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="back-home">
            <a href="index.html">← Volver al inicio</a>
        </div>
        
        <h1 class="logo">Dulce Encanto</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" placeholder="Correo" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </div>
            </div>
            
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>
    </div>
</body>
</html>