<?php
// CONFIGURACIÓN DE TU FIRESTORE
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

    // 1. VERIFICAMOS SI LA RESPUESTA ES EXITOSA (200)
    if ($code == 200) {
        
        // 2. VERIFICAMOS SI EL CAMPO 'contrasena' EXISTE DENTRO DEL JSON
        if (isset($json['fields']['contrasena']['stringValue'])) {
            
            $passBD = $json['fields']['contrasena']['stringValue'];

            // 3. COMPARACIÓN REAL
            if ($passBD === $p) {
                setcookie("auth_user", "ok", time() + 3600, "/");
                // Redirección por JS para asegurar que el navegador la ejecute
                echo "<script>window.location.href='selectorAper.php';</script>";
                exit();
            } else {
                $error = "CONTRASEÑA INCORRECTA";
            }
        } else {
            $error = "EL DOCUMENTO EXISTE PERO NO TIENE EL CAMPO 'contrasena'";
        }
    } else {
        // Si el código no es 200, es que el usuario no existe (404) o no hay permisos (403)
        $error = "USUARIO NO ENCONTRADO (Error: $code)";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>LOGIN</title>
    <style>
        body { background:#000; color:#0f0; font-family:monospace; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
        .box { border:1px solid #0f0; padding:20px; background:#111; text-align:center; }
        input { display:block; width:250px; padding:10px; margin:10px 0; background:#000; border:1px solid #0f0; color:#0f0; }
        button { width:100%; padding:10px; background:#0f0; border:none; cursor:pointer; font-weight:bold; }
        .err { color:red; margin-bottom:10px; font-weight:bold; font-size:12px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>SISTEMA LOG</h2>
        <?php if($error) echo "<div class='err'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="ID DOCUMENTO" required>
            <input type="password" name="contrasena" placeholder="PASSWORD" required>
            <button type="submit">ENTRAR</button>
        </form>
    </div>
</body>
</html>
