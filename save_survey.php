<?php
// ==========================================
//  save_survey.php
//  Guarda las respuestas de la encuesta
//  Base de datos: MySQL (XAMPP / phpMyAdmin)
//  Autor: Vinos Nicko’s
// ==========================================

// ---------- CONFIGURACIÓN DE CONEXIÓN ----------
$db_host = '127.0.0.1';      // o 'localhost'
$db_name = 'encuesta1'; // <--- cambia este nombre si tu BD tiene otro
$db_user = 'root';           // por defecto en XAMPP
$db_pass = '';               // por defecto en XAMPP sin contraseña

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

// ---------- CONEXIÓN A LA BASE DE DATOS ----------
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "<h2>Error de conexión a la base de datos</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// ---------- VALIDACIÓN DEL MÉTODO ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método no permitido.";
    exit;
}

// ---------- FUNCIÓN DE LIMPIEZA ----------
function limpiar($valor) {
    return trim((string)$valor);
}

// ---------- OBTENER Y VALIDAR DATOS ----------
$gusto = limpiar($_POST['gusto'] ?? '');
$parte = limpiar($_POST['parte'] ?? '');
$visual = limpiar($_POST['visual'] ?? '');
$sugerencias = limpiar($_POST['sugerencias'] ?? '');
$calificacion = (int)($_POST['calificacion'] ?? 0);

$errores = [];
if ($gusto === '') $errores[] = "Debe indicar si le gustó la página.";
if ($parte === '') $errores[] = "Debe escribir qué parte le llamó la atención.";
if ($calificacion < 1 || $calificacion > 10) $errores[] = "Calificación inválida.";

// ---------- SI HAY ERRORES ----------
if (!empty($errores)) {
    echo "<h2>Errores en el formulario</h2><ul>";
    foreach ($errores as $err) echo "<li>" . htmlspecialchars($err) . "</li>";
    echo "</ul><p><a href='encuesta.html'>Volver al formulario</a></p>";
    exit;
}

// ---------- DATOS ADICIONALES ----------
$ip_cliente = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

// ---------- GUARDAR EN LA BASE DE DATOS ----------
$sql = "INSERT INTO encuestas (gusto, parte, visual, sugerencias, calificacion, ip_cliente, user_agent)
        VALUES (:gusto, :parte, :visual, :sugerencias, :calificacion, :ip_cliente, :user_agent)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':gusto' => $gusto,
        ':parte' => $parte,
        ':visual' => $visual ?: null,
        ':sugerencias' => $sugerencias ?: null,
        ':calificacion' => $calificacion,
        ':ip_cliente' => $ip_cliente,
        ':user_agent' => $user_agent,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "<h2>Error al guardar los datos</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// ---------- REDIRECCIÓN CON CONFIRMACIÓN ----------
header('Location: encuesta.html?saved=1');
exit;
?>