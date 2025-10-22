<?php
//Pagina principal (mapa de niveles)
include 'conn.php';
session_start();

// verificar sesion del usuario
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Función para obtener progreso del usuario
function obtenerProgreso($conexion, $user_id) {
    $query = "SELECT User_Progress FROM usuarios WHERE User_ID = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $progreso = $result['User_Progress'] ? json_decode($result['User_Progress'], true) : [];
    return is_array($progreso) ? $progreso : [];
}

// Obtener nivel actual del usuario
$nivel_usuario = 1;
$stmt = $conexion->prepare("SELECT User_Lvl FROM usuarios WHERE User_ID = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nivel_usuario = $row['User_Lvl'];
    }
    $stmt->close();
}

// Obtener progreso del usuario
$progreso_usuario = obtenerProgreso($conexion, $user_id);

// Obtener todos los niveles disponibles
$query = "SELECT DISTINCT nivel FROM ejercicio ORDER BY nivel ASC";
$result_niveles = $conexion->query($query);

$niveles_info = [];
while ($nivel_row = $result_niveles->fetch_assoc()) {
    $nivel_num = $nivel_row['nivel'];
    
    // Obtener ejercicios del nivel
    $query_ej = "SELECT id_ej FROM ejercicio WHERE nivel = ?";
    $stmt_ej = $conexion->prepare($query_ej);
    $stmt_ej->bind_param("i", $nivel_num);
    $stmt_ej->execute();
    $result_ej = $stmt_ej->get_result();
    
    $ejercicios_nivel = [];
    while ($ej = $result_ej->fetch_assoc()) {
        $ejercicios_nivel[] = $ej['id_ej'];
    }
    $stmt_ej->close();
    
    // Contar completados
    $completados = 0;
    foreach ($ejercicios_nivel as $ej_id) {
        if (in_array($ej_id, $progreso_usuario)) {
            $completados++;
        }
    }
    
    $niveles_info[] = [
        'nivel' => $nivel_num,
        'total_ejercicios' => count($ejercicios_nivel),
        'ejercicios_completados' => $completados
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeñApp Niveles</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-titles">
            <h1>SeñApp</h1>
        </div>

        <div class="menu-inferior">
            <div class="ranking-button-left">
                <a href="ranking.php" class="btn-ranking">
                    <img src="iconos/trofeo.svg" class="trofeo-svg" alt="Ranking">
                    Ranking
                </a>
            </div>

            <div class="user-menu">
                <button class="user-button" onclick="toggleMenu()">
                    <img src="iconos/usuario.svg" alt="Usuario" class="usuario-icon">
                    <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <a href="logout.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>
    
    <script>
        function toggleMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Cerrar el menú si se hace clic fuera de él
        window.addEventListener('click', function(event) {
            if (!event.target.closest('.user-menu')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>
    
    <div id="contenido">
        <div class="niveles-lista">
            <?php foreach($niveles_info as $nivel_data): 
                $num_nivel = $nivel_data['nivel'];
                $total = $nivel_data['total_ejercicios'];
                $completados = $nivel_data['ejercicios_completados'];
                $porcentaje = $total > 0 ? round(($completados / $total) * 100) : 0;
                $nivel_completado = ($completados >= $total);
                $nivel_bloqueado = ($num_nivel > $nivel_usuario);
                
                // Determinar clase
                if ($nivel_bloqueado) {
                    $clase = 'bloqueado';
                    $contenido = '<img src="iconos/candado.svg" alt="Bloqueado" class="icono-candado">';
                } elseif ($nivel_completado) {
                    $clase = 'completado';
                    $contenido = $num_nivel;
                } else {
                    $clase = 'incompleto';
                    $contenido = $num_nivel;
                }
            ?>
                <?php if ($nivel_bloqueado): ?>
                    <div class="nivel-btn <?php echo $clase; ?>">
                        <span class="nivel-numero"><?php echo $contenido; ?></span>
                    </div>
                <?php else: ?>
                    <a href="nivel.php?nivel=<?php echo $num_nivel; ?>" class="nivel-btn <?php echo $clase; ?>">
                        <span class="nivel-numero"><?php echo $contenido; ?></span>
                        
                        <?php if ($nivel_completado): ?>
                            <span class="nivel-check">✓</span>
                        <?php endif; ?>
                        
                        <?php if (!$nivel_completado && $completados > 0): ?>
                            <span class="nivel-progreso-mini"><?php echo $porcentaje; ?>%</span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>