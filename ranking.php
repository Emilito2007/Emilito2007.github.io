<?php
session_start();
include 'conn.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener top 5 usuarios por puntos
$query_top = "SELECT User_ID, User_Name, User_Points, User_Lvl,
                     ROW_NUMBER() OVER (ORDER BY User_Points DESC, User_Lvl DESC, User_ID ASC) as posicion
              FROM usuarios 
              ORDER BY User_Points DESC, User_Lvl DESC, User_ID ASC 
              LIMIT 5";

$result_top = $conexion->query($query_top);
$top_usuarios = [];
$usuario_en_top = false;

while ($row = $result_top->fetch_assoc()) {
    $top_usuarios[] = $row;
    if ($row['User_ID'] == $user_id) {
        $usuario_en_top = true;
    }
}

// Si el usuario no está en el top 5, obtener su posición
$mi_posicion = null;
if (!$usuario_en_top) {
    $query_mi_posicion = "SELECT User_ID, User_Name, User_Points, User_Lvl,
                                 (SELECT COUNT(*) + 1 
                                  FROM usuarios u2 
                                  WHERE (u2.User_Points > u1.User_Points) 
                                     OR (u2.User_Points = u1.User_Points AND u2.User_Lvl > u1.User_Lvl)
                                     OR (u2.User_Points = u1.User_Points AND u2.User_Lvl = u1.User_Lvl AND u2.User_ID < u1.User_ID)
                                 ) as posicion
                          FROM usuarios u1 
                          WHERE User_ID = ?";
    
    $stmt = $conexion->prepare($query_mi_posicion);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $mi_posicion = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Posiciones - SeñApp</title>
    <link rel="stylesheet" href="style.css">
  
</head>
<body>
    <div class="container ranking-container">
        <h1>Tabla de Posiciones</h1>
        <p style="text-align: center; color: #666;">Los mejores jugadores de SeñApp</p>
        
        <ul class="ranking-list">
            <?php foreach ($top_usuarios as $index => $usuario): ?>
                <li class="ranking-item <?php echo ($usuario['User_ID'] == $user_id) ? 'usuario-actual' : ''; ?>">
                    <?php if ($index == 0): ?>
                        
                        <div class="posicion oro">1</div>
                    <?php elseif ($index == 1): ?>
                        
                        <div class="posicion plata">2</div>
                    <?php elseif ($index == 2): ?>
                        
                        <div class="posicion bronce">3</div>
                    <?php else: ?>
                        <div class="posicion"><?php echo $index + 1; ?></div>
                    <?php endif; ?>
                    
                    <div class="usuario-info">
                        <div class="usuario-nombre"><?php echo htmlspecialchars($usuario['User_Name']); ?></div>
                        <div class="usuario-nivel">Nivel <?php echo $usuario['User_Lvl']; ?></div>
                    </div>
                    
                    <div class="usuario-puntos"><?php echo number_format($usuario['User_Points']); ?> pts</div>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <?php if (!$usuario_en_top && $mi_posicion): ?>
            <div class="separador">...</div>
            <ul class="ranking-list">
                <li class="ranking-item usuario-actual">
                    <div class="posicion"><?php echo $mi_posicion['posicion']; ?></div>
                    <div class="usuario-info">
                        <div class="usuario-nombre"><?php echo htmlspecialchars($mi_posicion['User_Name']); ?> (Tú)</div>
                        <div class="usuario-nivel">Nivel <?php echo $mi_posicion['User_Lvl']; ?></div>
                    </div>
                    <div class="usuario-puntos"><?php echo number_format($mi_posicion['User_Points']); ?> pts</div>
                </li>
            </ul>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="PagPrincipal.php" class="btn">Volver al mapa</a>
        </div>
    </div>
</body>
</html>