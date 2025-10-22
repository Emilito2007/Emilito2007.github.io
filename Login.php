<?php
session_start();
include("conn.php");

$error = '';
$success = '';



if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'registered':
            $success = "¡Registro exitoso! Ya puedes iniciar sesión.";
            break;
        case 'logout':
            $success = "Has cerrado sesión correctamente.";
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $email = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($debug_mode) {
        error_log("=== LOGIN ATTEMPT ===");
        error_log("Email: " . $email);
        error_log("Password length: " . strlen($password));
    }
    
    if (empty($email)) {
        $error = "El correo electrónico es obligatorio.";
    } elseif (empty($password)) {
        $error = "La contraseña es obligatoria.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
        $stmt = $conexion->prepare("SELECT User_ID, User_Name, User_Mail, User_Pass, User_Lvl FROM usuarios WHERE User_Mail = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if ($debug_mode) {
                    error_log("User found: " . $user['User_Name']);
                    error_log("Hash in DB: " . substr($user['User_Pass'], 0, 20) . "...");
                    error_log("Hash length: " . strlen($user['User_Pass']));
                }
                
                // Verificar si el hash es válido (debe empezar con $2y$)
                if (strpos($user['User_Pass'], '$2y$') !== 0 && strpos($user['User_Pass'], '$2a$') !== 0) {
                    if ($debug_mode) {
                        error_log("WARNING: Password not properly hashed!");
                    }
                    $error = "Error en la configuración de la cuenta. Por favor, contacta al administrador.";
                } else {
                    // Intentar verificar la contraseña
                    $verify_result = password_verify($password, $user['User_Pass']);
                    
                    if ($debug_mode) {
                        error_log("Password verify result: " . ($verify_result ? "TRUE" : "FALSE"));
                    }
                    
                    if ($verify_result) {
                        // Contraseña correcta - establecer sesión
                        $_SESSION['user_id'] = $user['User_ID'];
                        $_SESSION['user_name'] = $user['User_Name'];
                        $_SESSION['user_email'] = $user['User_Mail'];
                        $_SESSION['logged_in'] = true;
                        $_SESSION['usuario'] = $user['User_Name'];
                        
                        if (isset($user['User_Lvl'])) {
                            $_SESSION['User_Lvl'] = (int) $user['User_Lvl'];
                        }
                        
                        if ($debug_mode) {
                            error_log("Login successful! Redirecting to PagPrincipal.php");
                        }
                        
                        header("Location: PagPrincipal.php");
                        exit();
                    } else {
                        $error = "Correo electrónico o contraseña incorrectos.";
                        
                        if ($debug_mode) {
                            error_log("Password verification failed");
                        }
                    }
                }
            } else {
                $error = "Correo electrónico o contraseña incorrectos.";
                
                if ($debug_mode) {
                    error_log("User not found with email: " . $email);
                }
            }
            
            $stmt->close();
        } else {
            $error = "Error en el servidor. Inténtalo más tarde.";
            
            if ($debug_mode) {
                error_log("Database error: " . $conexion->error);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        
        <?php if (!empty($success)): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input 
                type="email" 
                name="correo" 
                placeholder="Correo electrónico" 
                class="input" 
                required
                value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
            >
            
            <input 
                type="password" 
                name="password" 
                placeholder="Contraseña" 
                class="input" 
                required
            >
            
            <input type="submit" name="login-btn" value="Iniciar Sesión" class="btn">
        </form>
        
        <div class="register-link">
            <a href="Registro.php">¿No tienes usuario? Regístrate aquí</a>
        </div>
    
    </div>
</body>
</html>