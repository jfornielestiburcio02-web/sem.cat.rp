<?php
// --- CONFIGURACIÓN PRIVADA (OCULTA EN EL SERVIDOR) ---
$____ = [
    "p" => "semcatrp183721293",                       // Tu Project ID
    "k" => "AIzaSyB8UIE_aatbroEr28IB_3PtSDv3qwoPpjg", // Tu API Key
    "c" => "usuarios"                                  // Tu Colección
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = trim($_POST['usuario'] ?? '');
    $p = trim($_POST['contrasena'] ?? '');

    if (!empty($u) && !empty($p)) {
        // Endpoint directo a tu documento
        $url = "https://firestore.googleapis.com/v1/projects/{$____['p']}/databases/(default)/documents/{$____['c']}/" . urlencode($u) . "?key=" . $____['k'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($res, true);

        // Verificamos si la contraseña en Firestore coincide con la escrita
        if ($code == 200 && isset($json['fields']['contrasena']['stringValue'])) {
            $dbPass = $json['fields']['contrasena']['stringValue'];

            if ($dbPass === $p) {
                // Generamos un ID de sesión aleatorio
                $sid = bin2hex(random_bytes(8));

                // PATCH: Guardamos la sesión en Firestore para validar luego
                $patchUrl = "https://firestore.googleapis.com/v1/projects/{$____['p']}/databases/(default)/documents/{$____['c']}/" . urlencode($u) . "?updateMask.fieldPaths=phpsession&key=" . $____['k'];
                $payload = json_encode(["fields" => ["phpsession" => ["stringValue" => $sid]]]);

                $chP = curl_init($patchUrl);
                curl_setopt($chP, CURLOPT_CUSTOMREQUEST, "PATCH");
                curl_setopt($chP, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($chP, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chP, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($chP, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_exec($chP);
                curl_close($chP);

                // Redirigimos a tu selector con la sesión en la cookie
                setcookie("session_local", $sid, time() + 3600, "/");
                header("Location: selectorAper.php");
                exit();
            } else { $error = "PASSWORD_INVALID"; }
        } else { $error = "USER_NOT_FOUND"; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ACCESO</title>
    <style>
        body { background:#000; color:#0f0; font-family:monospace; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
        .box { border:1px solid #0f0; padding:25px; background:#050505; box-shadow: 0 0 15px #0f0; }
        input { width:100%; padding:10px; margin:10px 0; background:#000; border:1px solid #0f0; color:#0f0; box-sizing:border-box; }
        button { width:100%; padding:10px; background:#0f0; color:#000; border:none; cursor:pointer; font-weight:bold; }
        .err { color:red; text-align:center; margin-bottom:10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2 style="text-align:center">LOGIN_SYSTEM</h2>
        <?php if(isset($error)) echo "<div class='err'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="USUARIO" required>
            <input type="password" name="contrasena" placeholder="CONTRASENA" required>
            <button type="submit">ENTRAR</button>
        </form>
    </div>
</body>
</html>
