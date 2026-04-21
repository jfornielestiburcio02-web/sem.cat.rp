<?php
// TUS CONFIGS
$P_ID = "semcatrp183721293";
$P_KEY = "AIzaSyB8UIE_aatbroEr28IB_3PtSDv3qwoPpjg";
$COL  = "usuarios";

$dump = ""; // Aquí guardaremos todo lo que "cace" el script

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = trim($_POST['usuario'] ?? '');
    $p = trim($_POST['contrasena'] ?? '');

    $url = "https://firestore.googleapis.com/v1/projects/$P_ID/databases/(default)/documents/$COL/" . urlencode($u) . "?key=$P_KEY";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($res, true);

    // --- CONSOLA DE DEBUG ---
    $dump .= "<h3>--- RESULTADO DEL ESCANEO ---</h3>";
    $dump .= "<b>URL consultada:</b> $url <br>";
    $dump .= "<b>Código HTTP:</b> $http <br>";
    
    if ($http == 200) {
        $dump .= "<b style='color:cyan'>[OK] Conexión establecida con el documento.</b><br>";
        
        // Verificamos qué campos existen realmente
        if (isset($json['fields'])) {
            $dump .= "<b>Campos encontrados en Firestore:</b><pre>" . print_r($json['fields'], true) . "</pre>";
            
            $pass_firebase = $json['fields']['contrasena']['stringValue'] ?? null;
            
            if ($pass_firebase !== null) {
                $dump .= "<b>Contraseña en BD:</b> <span style='color:yellow'>$pass_firebase</span><br>";
                $dump .= "<b>Contraseña enviada:</b> <span style='color:yellow'>$p</span><br>";
                
                if ($pass_firebase === $p) {
                    $dump .= "<h2 style='color:#0f0'>¡COINCIDENCIA TOTAL! El login funcionaría.</h2>";
                } else {
                    $dump .= "<h2 style='color:#f00'>ERROR: Las contraseñas NO coinciden.</h2>";
                }
            } else {
                $dump .= "<h2 style='color:#f00'>ERROR: El campo 'contrasena' no existe en este documento.</h2>";
            }
        }
    } else if ($http == 404) {
        $dump .= "<h2 style='color:#f00'>ERROR 404: El usuario '$u' no existe como ID de documento.</h2>";
        $dump .= "<b>Respuesta cruda:</b> <pre>$res</pre>";
    } else {
        $dump .= "<h2 style='color:#f00'>ERROR CRÍTICO: Código $http</h2>";
        $dump .= "<b>Respuesta cruda:</b> <pre>" . htmlspecialchars($res) . "</pre>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>INSPECTOR FIRESTORE</title>
    <style>
        body { background:#1a1a1a; color:#ccc; font-family:monospace; padding:40px; }
        .form-debug { background:#000; border:2px solid #555; padding:20px; margin-bottom:20px; }
        input { background:#222; border:1px solid #444; color:#fff; padding:8px; margin-right:10px; }
        button { padding:8px 20px; cursor:pointer; background:#444; color:#fff; border:1px solid #666; }
        pre { background:#000; padding:10px; border:1px solid #333; color:#0f0; overflow:auto; }
        h3 { color: #00d1b2; border-bottom:1px solid #333; padding-bottom:10px; }
    </style>
</head>
<body>

    <div class="form-debug">
        <strong>Introduce datos para testear:</strong><br><br>
        <form method="POST">
            <input type="text" name="usuario" placeholder="ID Usuario" required>
            <input type="text" name="contrasena" placeholder="Contraseña a probar" required>
            <button type="submit">ESCANEAR</button>
        </form>
    </div>

    <?php if($dump) echo $dump; ?>

</body>
</html>
