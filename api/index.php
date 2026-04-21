<?php
// --- CONFIGURACIÓN ---
$C = [
    "pid" => "semcatrp183721293",
    "key" => "AIzaSyB8UIE_aatbroEr28IB_3PtSDv3qwoPpjg",
    "col" => "usuarios"
];

$debug_info = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = trim($_POST['usuario'] ?? '');
    $p = trim($_POST['contrasena'] ?? '');

    // Construcción de URL
    $url = "https://firestore.googleapis.com/v1/projects/{$C['pid']}/databases/(default)/documents/{$C['col']}/" . urlencode($u) . "?key=" . $C['key'];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // --- FILTRADOR Y DEBUGGER ---
    $json = json_decode($res, true);
    
    // Guardamos info para debug (esto te dirá qué está pasando)
    $debug_info .= "HTTP CODE: $http_code <br>";
    if($curl_error) $debug_info .= "CURL ERR: $curl_error <br>";
    
    if ($http_code == 200 && $json) {
        // Filtrar estructura de Firestore: fields -> contrasena -> stringValue
        if (isset($json['fields']['contrasena']['stringValue'])) {
            $pass_db = $json['fields']['contrasena']['stringValue'];
            
            if ($pass_db === $p) {
                setcookie("auth_user", "ok", time() + 3600, "/");
                echo "<script>window.location.href='selectorAper.php';</script>";
                exit();
            } else {
                $error = "PASSWORD_MISMATCH: Pusiste '$p' pero en BD hay '$pass_db'";
            }
        } else {
            $error = "JSON_STRUCTURE_ERROR: No se encontró el campo 'contrasena'.";
            $debug_info .= "ESTRUCTURA RECIBIDA: <pre>" . print_r($json['fields'] ?? 'Sin campos', true) . "</pre>";
        }
    } else {
        $error = "LOGIN_FAILED: Código $http_code";
        if($http_code == 404) $error .= " - El documento '$u' no existe.";
        if($http_code == 403) $error .= " - Permisos denegados (Revisa reglas de Firebase).";
        $debug_info .= "RESPUESTA CRUDA: <pre>" . htmlspecialchars($res) . "</pre>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DEBUG LOGIN</title>
    <style>
        body { background:#000; color:#0f0; font-family:monospace; padding:20px; }
        .box { border:1px solid #0f0; padding:20px; max-width:400px; margin:auto; background:#0a0a0a; }
        input { width:100%; padding:10px; margin:5px 0; background:#000; border:1px solid #0f0; color:#0f0; box-sizing:border-box; }
        button { width:100%; padding:10px; background:#0f0; border:none; cursor:pointer; font-weight:bold; }
        .debug { background:#111; border:1px dashed #555; padding:10px; margin-top:20px; font-size:12px; color:#aaa; overflow-x:auto; }
        .err { color:#ff0000; font-weight:bold; margin-bottom:10px; }
    </style>
</head>
<body>

<div class="box">
    <h3>SISTEMA DE ACCESO</h3>
    <?php if($error) echo "<div class='err'>$error</div>"; ?>
    
    <form method="POST">
        <input type="text" name="usuario" placeholder="Document ID (jmatamorosd)" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <button type="submit">ENTRAR</button>
    </form>

    <?php if($debug_info): ?>
    <div class="debug">
        <strong>DEBUG INFO:</strong><br>
        <?php echo $debug_info; ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
