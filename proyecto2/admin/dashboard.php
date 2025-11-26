<?php
// Verificar que el archivo config existe
if (!file_exists('../config.php')) {
    die('Error: No se encuentra el archivo config.php. Ruta actual: ' . __DIR__);
}

require_once '../config.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar que sea admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

try {
    // Obtener estadísticas
    $stmt = $conn->query("SELECT COUNT(*) as total FROM productos");
    $total_productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'");
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $conn->query("SELECT COUNT(*) as total FROM pedidos");
    $total_pedidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $conn->query("SELECT SUM(total) as total FROM pedidos WHERE estado = 'completado'");
    $total_ventas = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Obtener todos los productos
    $stmt = $conn->query("SELECT * FROM productos ORDER BY fecha_creacion DESC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todos los usuarios
    $stmt = $conn->query("SELECT id, nombre, email, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Dulce Encanto</title>
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
            gap: 1rem;
        }
        
        .badge-admin {
            background: #894962;
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: #F09BA2;
            margin-bottom: 1rem;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #894962;
            font-size: 2rem;
            font-weight: 600;
        }
        
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 15px;
        }
        
        .tab-btn {
            padding: 0.8rem 2rem;
            border: none;
            background: transparent;
            color: #894962;
            font-weight: 600;
            cursor: pointer;
            border-radius: 10px;
            transition: background 0.3s;
        }
        
        .tab-btn.active {
            background: #F09BA2;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            background: #894962;
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.3s;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        th {
            background: #894962;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .producto-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-right: 0.5rem;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal-content {
            background: white;
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 15px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .close-modal {
            font-size: 2rem;
            cursor: pointer;
            color: #894962;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #894962;
            font-weight: 600;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #F09BA2;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
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
        <h1 class="logo">Dulce Encanto - Admin</h1>
        <div class="user-info">
            <span class="badge-admin">ADMINISTRADOR</span>
            <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="../logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-cake-candles"></i>
                <h3>Total Productos</h3>
                <p><?php echo $total_productos; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3>Total Usuarios</h3>
                <p><?php echo $total_usuarios; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>Total Pedidos</h3>
                <p><?php echo $total_pedidos; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>Ventas Totales</h3>
                <p>$<?php echo number_format($total_ventas, 2); ?></p>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab-btn active" onclick="cambiarTab('productos')">
                <i class="fas fa-cake-candles"></i> Productos
            </button>
            <button class="tab-btn" onclick="cambiarTab('usuarios')">
                <i class="fas fa-users"></i> Usuarios
            </button>
        </div>
        
        <div id="alertContainer"></div>
        
        <!-- Tab Productos -->
        <div id="productos-tab" class="tab-content active">
            <div class="section-header">
                <h2>Gestión de Productos</h2>
                <button class="btn-primary" onclick="abrirModalProducto()">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </button>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><img src="../<?php echo htmlspecialchars($producto['imagen']); ?>" class="producto-img"></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                        <td><?php echo $producto['stock']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?></td>
                        <td>
                            <button class="btn-edit" onclick='editarProducto(<?php echo json_encode($producto); ?>)'>
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn-delete" onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Tab Usuarios -->
        <div id="usuarios-tab" class="tab-content">
            <div class="section-header">
                <h2>Gestión de Usuarios</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo ucfirst($usuario['rol']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                        <td>
                            <button class="btn-edit" onclick='editarUsuario(<?php echo json_encode($usuario); ?>)'>
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                            <button class="btn-delete" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal Producto -->
    <div id="productoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Nuevo Producto</h2>
                <span class="close-modal" onclick="cerrarModalProducto()">&times;</span>
            </div>
            
            <form id="productoForm">
                <input type="hidden" id="producto_id" name="id">
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="imagen">URL de la Imagen</label>
                    <input type="text" id="imagen" name="imagen" placeholder="./img/producto.webp" required>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
            </form>
        </div>
    </div>
    
    <!-- Modal Usuario -->
    <div id="usuarioModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Usuario</h2>
                <span class="close-modal" onclick="cerrarModalUsuario()">&times;</span>
            </div>
            
            <form id="usuarioForm">
                <input type="hidden" id="usuario_id" name="id">
                
                <div class="form-group">
                    <label for="usuario_nombre">Nombre</label>
                    <input type="text" id="usuario_nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="usuario_email">Email</label>
                    <input type="email" id="usuario_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="usuario_rol">Rol</label>
                    <select id="usuario_rol" name="rol" required>
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function cambiarTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
        }
        
        function mostrarAlerta(mensaje, tipo) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${tipo}`;
            alert.textContent = mensaje;
            alertContainer.appendChild(alert);
            
            setTimeout(() => alert.remove(), 3000);
        }
        
        // Funciones Producto
        function abrirModalProducto() {
            document.getElementById('modalTitulo').textContent = 'Nuevo Producto';
            document.getElementById('productoForm').reset();
            document.getElementById('producto_id').value = '';
            document.getElementById('productoModal').style.display = 'block';
        }
        
        function cerrarModalProducto() {
            document.getElementById('productoModal').style.display = 'none';
        }
        
        function editarProducto(producto) {
            document.getElementById('modalTitulo').textContent = 'Editar Producto';
            document.getElementById('producto_id').value = producto.id;
            document.getElementById('nombre').value = producto.nombre;
            document.getElementById('descripcion').value = producto.descripcion;
            document.getElementById('precio').value = producto.precio;
            document.getElementById('stock').value = producto.stock;
            document.getElementById('imagen').value = producto.imagen;
            document.getElementById('productoModal').style.display = 'block';
        }
        
        document.getElementById('productoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('productos_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mostrarAlerta('Producto guardado exitosamente', 'success');
                    cerrarModalProducto();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarAlerta(result.message || 'Error al guardar producto', 'error');
                }
            } catch (error) {
                mostrarAlerta('Error al guardar producto', 'error');
            }
        });
        
        async function eliminarProducto(id) {
            if (!confirm('¿Estás seguro de eliminar este producto?')) return;
            
            try {
                const response = await fetch('productos_crud.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete&id=${id}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mostrarAlerta('Producto eliminado exitosamente', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarAlerta(result.message || 'Error al eliminar producto', 'error');
                }
            } catch (error) {
                mostrarAlerta('Error al eliminar producto', 'error');
            }
        }
        
        // Funciones Usuario
        function cerrarModalUsuario() {
            document.getElementById('usuarioModal').style.display = 'none';
        }
        
        function editarUsuario(usuario) {
            document.getElementById('usuario_id').value = usuario.id;
            document.getElementById('usuario_nombre').value = usuario.nombre;
            document.getElementById('usuario_email').value = usuario.email;
            document.getElementById('usuario_rol').value = usuario.rol;
            document.getElementById('usuarioModal').style.display = 'block';
        }
        
        document.getElementById('usuarioForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('usuarios_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mostrarAlerta('Usuario actualizado exitosamente', 'success');
                    cerrarModalUsuario();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarAlerta(result.message || 'Error al actualizar usuario', 'error');
                }
            } catch (error) {
                mostrarAlerta('Error al actualizar usuario', 'error');
            }
        });
        
        async function eliminarUsuario(id) {
            if (!confirm('¿Estás seguro de eliminar este usuario?')) return;
            
            try {
                const response = await fetch('usuarios_crud.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete&id=${id}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mostrarAlerta('Usuario eliminado exitosamente', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarAlerta(result.message || 'Error al eliminar usuario', 'error');
                }
            } catch (error) {
                mostrarAlerta('Error al eliminar usuario', 'error');
            }
        }
    </script>
</body>
</html>