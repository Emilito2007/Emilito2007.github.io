<?php
include 'conn.php';

// DEBUG - Ver qué datos llegan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>DEBUG - Datos recibidos:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Probar diferentes nombres de campos posibles
    $nombre = '';
    $email = '';
    $pass = '';
    
    // Buscar el nombre
    if (isset($_POST['nombre'])) $nombre = trim($_POST['nombre']);
    elseif (isset($_POST['name'])) $nombre = trim($_POST['name']);
    elseif (isset($_POST['usuario'])) $nombre = trim($_POST['usuario']);
    
    // Buscar el email
    if (isset($_POST['email'])) $email = trim($_POST['email']);
    elseif (isset($_POST['correo'])) $email = trim($_POST['correo']);
    
    // Buscar la contraseña
    if (isset($_POST['pass'])) $pass = $_POST['pass'];
    elseif (isset($_POST['password'])) $pass = $_POST['password'];
    elseif (isset($_POST['contraseña'])) $pass = $_POST['contraseña'];
    
    echo "<p><strong>Datos procesados:</strong></p>";
    echo "<p>Nombre: '$nombre' (" . strlen($nombre) . " caracteres)</p>";
    echo "<p>Email: '$email' (" . strlen($email) . " caracteres)</p>";
    echo "<p>Password: " . (empty($pass) ? "VACÍO" : "OK (" . strlen($pass) . " caracteres)") . "</p>";

    // Validación básica
    if (empty($nombre) || empty($email) || empty($pass)) {
        echo "<p style='color:red;'>❌ Todos los campos son obligatorios.</p>";
        echo "<p>Nombre vacío: " . (empty($nombre) ? "SÍ" : "NO") . "</p>";
        echo "<p>Email vacío: " . (empty($email) ? "SÍ" : "NO") . "</p>";
        echo "<p>Password vacío: " . (empty($pass) ? "SÍ" : "NO") . "</p>";
        exit;
    }

    // Verificar si el email ya existe
    $checkEmail = $conexion->prepare("SELECT User_ID FROM usuarios WHERE User_Mail = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    
    if ($result->num_rows > 0) {
        echo "<p style='color:red;'>❌ Este correo ya está registrado.</p>";
        exit;
    }
    $checkEmail->close();

    // Hash seguro de la contraseña
    $passHash = password_hash($pass, PASSWORD_DEFAULT);
    echo "<p>✅ Contraseña hasheada: " . substr($passHash, 0, 20) . "... (longitud: " . strlen($passHash) . ")</p>";

    // Insertar en la base de datos
    $stmt = $conexion->prepare("INSERT INTO usuarios (User_Name, User_Mail, User_Pass) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $passHash);
    
    if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ Registro exitoso!</p>";
        echo "<p>Usuario creado con ID: " . $conexion->insert_id . "</p>";
        // Redirigir a login (descomentado para debug)
        // header("Location: Login.php?msg=registered");
        // exit;
    } else {
        echo "<p style='color:red;'>❌ Error al registrar: " . $stmt->error . "</p>";
    }
    
    $stmt->close();
    $conexion->close();
}
?>