<?php
include 'conn.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass = isset($_POST['pass']) ? $_POST['pass'] : '';

    // Validación mejorada
    if (empty($nombre) || strlen($nombre) < 2) {
        $error = "El nombre debe tener al menos 2 caracteres.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, introduce un correo electrónico válido.";
    } elseif (empty($pass) || strlen($pass) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif (strlen($pass) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
        // Verificar si el email ya existe
        $checkEmail = $conexion->prepare("SELECT User_ID FROM usuarios WHERE User_Mail = ?");
        if ($checkEmail) {
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $result = $checkEmail->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Este correo electrónico ya está registrado.";
            } else {
                // Hash seguro de la contraseña
                $passHash = password_hash($pass, PASSWORD_DEFAULT);

                // Insertar en la base de datos
                $stmt = $conexion->prepare("INSERT INTO usuarios (User_Name, User_Mail, User_Pass) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $nombre, $email, $passHash);
                    
                    if ($stmt->execute()) {
                        // Obtener el ID del usuario recién registrado
                        $user_id = $conexion->insert_id;
                        
                        // Iniciar sesión automáticamente
                        session_start();
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['usuario'] = $nombre;
                        
                        // Redirigir a la página principal
                        header("Location: PagPrincipal.php");
                        exit();
                    } else {
                        $error = "Error al crear la cuenta. Inténtalo de nuevo.";
                    }
                    $stmt->close();
                } else {
                    $error = "Error en el servidor. Inténtalo más tarde.";
                }
            }
            $checkEmail->close();
        } else {
            $error = "Error en el servidor. Inténtalo más tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SeñApp</title>
    <link rel="stylesheet" href="style.css">
           
</head>
<body>
    <div class="container">
        <h1>SeñApp</h1>
        <h2>Crear Cuenta</h2>
        
        <?php if (!empty($error)): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="mensaje exito"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="form-container">
            <div class="form-group">
                <label for="nombre">Nombre completo</label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    class="input-field"
                    required
                    placeholder="Ingresa tu nombre completo"
                    value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="input-field"
                    required
                    placeholder="ejemplo@correo.com"
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="pass">Contraseña</label>
                <input 
                    type="password" 
                    id="pass" 
                    name="pass" 
                    class="input-field"
                    required
                    minlength="6"
                    placeholder="Mínimo 6 caracteres"
                >
            </div>
            
            <button type="submit" class="button">Registrarse</button>
        </form>
        
        <div class="auth-links">
            <p>¿Ya tienes una cuenta? <a href="Login.php" class="link">Inicia sesión aquí</a></p>
        </div>
    </div>
</body>
</html>