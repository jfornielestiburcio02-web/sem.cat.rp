<?php
// CONFIGURACIÓN PROBADA Y FUNCIONANDO
$P_ID = "semcatrp183721293";
$P_KEY = "AIzaSyB8UIE_aatbroEr28IB_3PtSDv3qwoPpjg";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = trim($_POST['usuario'] ?? '');
    $p = trim($_POST['contrasena'] ?? '');

    $url = "https://firestore.googleapis.com/v1/projects/$P_ID/databases/(default)/documents/usuarios/" . urlencode($u) . "?key=$P_KEY";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($res, true);

    if ($code == 200 && isset($json['fields']['contrasena']['stringValue'])) {
        $passBD = $json['fields']['contrasena']['stringValue'];

        if ($passBD === $p) {
            // LOGIN EXITOSO
            setcookie("auth_user", "ok", time() + 3600, "/");
            
            // REDIRECCIÓN "A PRUEBA DE BALAS" (Mezcla de JS y HTML)
            echo "<html><body>
                  <script>window.location.href='selectorAper.php';</script>
                  <meta http-equiv='refresh' content='0;url=selectorAper.php'>
                  <p>Redirigiendo...</p>
                  </body></html>";
            exit();
        } else {
            $error = "La contraseña no coincide.";
        }
    } else {
        $error = "Usuario no encontrado o error de red.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Sistema</title>
    <style>
        body { background:#000; color:#0f0; font-family:monospace; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
        .login-box { border:1px solid #0f0; padding:30px; background:#050505; box-shadow:0 0 15px #0f0; width:300px; }
        input { width:100%; padding:10px; margin:10px 0; background:#000; border:1px solid #0f0; color:#0f0; box-sizing:border-box; outline:none; }
        button { width:100%; padding:10px; background:#0f0; color:#000; border:none; font-weight:bold; cursor:pointer; }
        button:hover { background:#000; color:#0f0; }
        .err { color:#ff0000; text-align:center; margin-bottom:10px; font-size:12px; }
    </style>
</head>
<body>
    <div class="login-card">
        <form method="POST">
            <h2 style="text-align:center">LOGIN_ROOT</h2>
            <?php if($error) echo "<div class='err'>$error</div>"; ?>
            <input type="text" name="usuario" placeholder="USUARIO" required>
            <input type="password" name="contrasena" placeholder="PASSWORD" required>
            <button type="submit">ACCEDER</button>
        </form>
    </div>
</body>
</html>
