<?php
// TUS CONFIGS REALES
$P_ID = "semcatrp183721293";
$P_KEY = "AIzaSyB8UIE_aatbroEr28IB_3PtSDv3qwoPpjg";

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = trim($_POST['usuario'] ?? '');
    $p = trim($_POST['contrasena'] ?? '');

    // Llamada limpia a Firestore
    $url = "https://firestore.googleapis.com/v1/projects/$P_ID/databases/(default)/documents/usuarios/" . urlencode($u) . "?key=$P_KEY";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($res, true);

    if ($code == 200) {
        $passBD = $json['fields']['contrasena']['stringValue'] ?? '';

        if ($passBD === $p) {
            // LOGIN CORRECTO
            // Creamos la cookie local
            setcookie("auth_user", "ok", time() + 3600, "/");
            
            // REDIRECCIÓN DIRECTA (Asegúrate de que el archivo se llame exactamente así)
            header("Location: selectorAper.php");
            exit();
        } else {
            $error_msg = "CONTRASEÑA INCORRECTA";
        }
    } else {
        $error_msg = "USUARIO '$u' NO ENCONTRADO (HTTP $code)";
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
        .login-card { border:1px solid #0f0; padding:25px; background:#050505; width:300px; text-align:center; }
        input { width:100%; padding:10px; margin:10px 0; background:#000; border:1px solid #0f0; color:#0f0; box-sizing:border-box; }
        button { width:100%; padding:10px; background:#0f0; color:#000; border:none; cursor:pointer; font-weight:bold; }
        .error { color:red; margin-bottom:10px; font-size:12px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>SISTEMA</h2>
        <?php if($error_msg) echo "<div class='error'>$error_msg</div>"; ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="USUARIO" required>
            <input type="password" name="contrasena" placeholder="PASSWORD" required>
            <button type="submit">ENTRAR</button>
        </form>
    </div>
</body>
</html>
