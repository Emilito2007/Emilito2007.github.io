<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: PagPrincipal.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="style.css">
    <title>SeñApp</title>
</head>
<body>
    <div class="welcome-layout">
        <div class="logo-section">
            <img src="iconos/logoblanco.svg" alt="SeñApp Logo" class="giant-logo">
        </div>
        <div class="content-section">
            <div class="bienvenida">
                <h1>Bienvenido a SeñApp</h1>
                <p class="app-description">
                    Aprende Lengua de Señas Argentino Hoy
                </p>
            </div>
            
            <div class="button-section">
                <a href="Login.php" class="btn-comenzar" style="display: inline-block; text-decoration: none;">Comenzar</a>
            </div>
        </div>
    </div>
</body>
</html>