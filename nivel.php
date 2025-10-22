<?php
session_start();
include 'conn.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$nivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 1;
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

// Función para actualizar el JSON de progreso y dar puntos
function actualizarProgresoJSON($conexion, $user_id, $ejercicio_id) {
    $progreso = obtenerProgreso($conexion, $user_id);
    
    // Verificar si el ejercicio NO estaba completado antes
    if (!in_array($ejercicio_id, $progreso)) {
        $progreso[] = $ejercicio_id;
        
        $json_progreso = json_encode($progreso);
        
        // Actualizar JSON y sumar 10 puntos
        $update = "UPDATE usuarios SET User_Progress = ?, User_Points = User_Points + 10 WHERE User_ID = ?";
        $stmt_update = $conexion->prepare($update);
        $stmt_update->bind_param("si", $json_progreso, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        return true; // Nuevo ejercicio completado, se otorgaron 10 puntos
    }
    
    return false; // Ya estaba completado, no se otorgan puntos
}

// Función para verificar si un ejercicio está completado
function ejercicioCompletado($progreso, $ejercicio_id) {
    return in_array($ejercicio_id, $progreso);
}

// Obtener progreso del usuario
$progreso_usuario = obtenerProgreso($conexion, $user_id);

// Obtener todos los ejercicios del nivel
$query_todos = "SELECT e.id_ej, e.nivel, e.rtaAcorrect, e.rtaB, e.rtaC, e.rtaD, e.video, e.type
                FROM ejercicio e
                WHERE e.nivel = ?
                ORDER BY e.id_ej ASC";
$stmt_todos = $conexion->prepare($query_todos);
$stmt_todos->bind_param("i", $nivel);
$stmt_todos->execute();
$result_todos = $stmt_todos->get_result();

$todos_ejercicios = [];
while ($row = $result_todos->fetch_assoc()) {
    $todos_ejercicios[] = $row;
}
$stmt_todos->close();

if (empty($todos_ejercicios)) {
    echo "No hay ejercicios disponibles para este nivel.";
    exit;
}

// Filtrar solo ejercicios incompletos
$ejercicios_incompletos = [];
foreach ($todos_ejercicios as $ej) {
    if (!ejercicioCompletado($progreso_usuario, $ej['id_ej'])) {
        $ejercicios_incompletos[] = $ej;
    }
}

// Si no hay ejercicios incompletos, todos están completados
if (empty($ejercicios_incompletos)) {
    header("Location: Resultados.php?nivel=$nivel&todos_completados=1");
    exit();
}

// Si el usuario quiere saltar el ejercicio actual
$saltar = isset($_GET['saltar']) ? true : false;
$indice_ejercicio = 0;

if ($saltar && count($ejercicios_incompletos) > 1) {
    // Rotar al siguiente ejercicio incompleto
    $primer_ejercicio = array_shift($ejercicios_incompletos);
    $ejercicios_incompletos[] = $primer_ejercicio;
}

// Obtener el ejercicio actual
$ejercicio = $ejercicios_incompletos[$indice_ejercicio];
$total_incompletos = count($ejercicios_incompletos);
$total_nivel = count($todos_ejercicios);
$total_completados = count($todos_ejercicios) - count($ejercicios_incompletos);
$posicion_actual = 1;

// Procesar respuesta tipo Escribir
if(isset($_POST['respuesta']) && $ejercicio['type'] == 'Escribir') {
    $respuesta_usuario = strtolower(trim($_POST['respuesta']));
    $respuesta_correcta = strtolower(trim($ejercicio['rtaAcorrect']));
    
    // Normalizar caracteres especiales
    $respuesta_usuario = iconv('UTF-8', 'ASCII//TRANSLIT', $respuesta_usuario);
    $respuesta_correcta = iconv('UTF-8', 'ASCII//TRANSLIT', $respuesta_correcta);
    
    $esCorrecta = ($respuesta_usuario === $respuesta_correcta);

    if ($esCorrecta) {
        // Actualizar JSON y otorgar puntos si es nuevo
        $puntos_otorgados = actualizarProgresoJSON($conexion, $user_id, $ejercicio['id_ej']);
        
        // Redirigir al mismo nivel para cargar el siguiente incompleto
        $params = "nivel=$nivel&mensaje=correcto";
        if ($puntos_otorgados) {
            $params .= "&puntos=10";
        }
        header("Location: nivel.php?$params");
        exit();
    }
}

// Procesar respuesta tipo Elegir
if(isset($_POST['opcion']) && $ejercicio['type'] == 'Elegir') {
    $esCorrecta = ($_POST['opcion'] === $ejercicio['rtaAcorrect']);
    
    if ($esCorrecta) {
        // Actualizar JSON y otorgar puntos si es nuevo
        $puntos_otorgados = actualizarProgresoJSON($conexion, $user_id, $ejercicio['id_ej']);
        
        // Redirigir al mismo nivel para cargar el siguiente incompleto
        $params = "nivel=$nivel&mensaje=correcto";
        if ($puntos_otorgados) {
            $params .= "&puntos=10";
        }
        header("Location: nivel.php?$params");
        exit();
    }
}

// Preparar opciones para tipo Elegir
if($ejercicio['type'] == 'Elegir') {
    $opciones = array();
    if(!empty($ejercicio['rtaAcorrect'])) $opciones[] = $ejercicio['rtaAcorrect'];
    if(!empty($ejercicio['rtaB'])) $opciones[] = $ejercicio['rtaB'];
    if(!empty($ejercicio['rtaC'])) $opciones[] = $ejercicio['rtaC'];
    if(!empty($ejercicio['rtaD'])) $opciones[] = $ejercicio['rtaD'];
    shuffle($opciones);
}

// Mostrar mensaje de respuesta anterior si existe
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
$puntos_ganados = isset($_GET['puntos']) ? (int)$_GET['puntos'] : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nivel <?php echo $nivel; ?> - SeñApp</title>
    <link rel="stylesheet" href="style.css">
</head>
</head>
<body>
    <header>
        <div class="header-nav">
            <a href="PagPrincipal.php" class="btn-volver">&larr; Volver al mapa</a>
            <div class="progreso-detallado">
                <div class="progreso">
                    Ejercicio <?php echo $posicion_actual; ?>/<?php echo $total_incompletos; ?> 
                    
                </div>
            </div>
        </div>
        <h1>Nivel <?php echo $nivel; ?></h1>
        <div class="progreso-info" style="text-align: center;">
            <?php 
            $porcentaje = round(($total_completados / $total_nivel) * 100);
            ?>
            Progreso total: <?php echo $total_completados; ?>/<?php echo $total_nivel; ?> (<?php echo $porcentaje; ?>%)
        </div>
    </header>

    <div class="contenedor-nivel">
        <?php if ($mensaje == 'correcto'): ?>
            <div class="mensaje-transitorio">
                ¡Correcto!
            </div>
        <?php endif; ?>
        
        <?php if ($puntos_ganados > 0): ?>
            <div class="mensaje-puntos">
                🎉 ¡+<?php echo $puntos_ganados; ?> puntos! 🎉
            </div>
        <?php endif; ?>
        
        <div class="gif-container">
            <img src="videos/<?php echo $ejercicio['video']; ?>" alt="Seña animada" class="gif-seña">
        </div>

        <?php if($ejercicio['type'] == 'Escribir'): ?>
            <div class="ejercicio-container">
                <form method="POST" class="form-respuesta">
                    <input type="text" 
                           name="respuesta" 
                           placeholder="Escribe tu respuesta" 
                           required 
                           class="input-respuesta"
                           autocomplete="off">
                    <button type="submit" class="btn-responder">Responder</button>
                </form>
                
                <?php if(isset($esCorrecta) && !$esCorrecta): ?>
                    <div class="mensaje-resultado incorrecto">
                        Incorrecto. Intenta de nuevo.
                    </div>
                    <div class="navegacion">
                        <a href="nivel.php?nivel=<?php echo $nivel; ?>" class="btn-siguiente secundario">
                            ↻ Intentar de nuevo
                        </a>
                        <?php if ($total_incompletos > 1): ?>
                            <a href="nivel.php?nivel=<?php echo $nivel; ?>&saltar=1" class="btn-siguiente">
                                Saltar ejercicio →
                            </a>
                        <?php else: ?>
                            <a href="PagPrincipal.php" class="btn-siguiente">
                                ← Volver al mapa
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($ejercicio['type'] == 'Elegir'): ?>
            <div class="ejercicio-container">
                <?php if(!isset($esCorrecta)): ?>
                    <form method="POST" class="form-opciones">
                        <?php foreach($opciones as $opcion): ?>
                            <button type="submit" 
                                    name="opcion" 
                                    value="<?php echo htmlspecialchars($opcion); ?>" 
                                    class="btn-opcion">
                                <?php echo htmlspecialchars($opcion); ?>
                            </button>
                        <?php endforeach; ?>
                    </form>
                <?php else: ?>
                    <div class="form-opciones">
                        <?php foreach($opciones as $opcion): 
                            $esLaCorrecta = ($opcion === $ejercicio['rtaAcorrect']);
                            $clase = $esLaCorrecta ? 'correcta' : 'incorrecta';
                        ?>
                            <button class="btn-opcion <?php echo $clase; ?>" disabled>
                                <?php echo htmlspecialchars($opcion); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="mensaje-resultado incorrecto">
                        Incorrecto. La respuesta correcta era: <strong><?php echo htmlspecialchars($ejercicio['rtaAcorrect']); ?></strong>
                    </div>
                    <div class="navegacion">
                        <a href="nivel.php?nivel=<?php echo $nivel; ?>" class="btn-siguiente secundario">
                            ↻ Intentar de nuevo
                        </a>
                        <?php if ($total_incompletos > 1): ?>
                            <a href="nivel.php?nivel=<?php echo $nivel; ?>&saltar=1" class="btn-siguiente">
                                Saltar ejercicio →
                            </a>
                        <?php else: ?>
                            <a href="PagPrincipal.php" class="btn-siguiente">
                                ← Volver al mapa
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: #666; font-size: 0.9em;">
                💡 Solo se muestran ejercicios incompletos. 
                <?php if ($total_completados > 0): ?>
                    Ya completaste <?php echo $total_completados; ?> ejercicio<?php echo $total_completados > 1 ? 's' : ''; ?> de este nivel.
                <?php endif; ?>
            </p>
            <?php if ($saltar): ?>
                <p style="color: #d55404; font-size: 0.9em; margin-top: 10px;">
                    ⚠️ Ejercicio movido al final. Podrás intentarlo más tarde.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>