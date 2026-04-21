<?php
// CONFIGURACIÓN REAL (LADO DEL SERVIDOR - NADIE LA VE)
$C = [
    "pid" => "semcatrp183721293",
    "key" => "AIzaSyB8UIE_aatbroEr28IB_3PtSDv3qwoPpjg",
    "col" => "usuarios"
];

session_start();
$err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['usuario'] ?? '');
    $pass = trim($_POST['contrasena'] ?? '');

    if (!empty($user) && !empty($pass)) {
        // URL de la API de Firestore (usando tu Project ID y API Key)
        $url = "https://firestore.googleapis.com/v1/projects/{$C['pid']}/databases/(default)/documents/{$C['col']}/" . urlencode($user) . "?key=" . $C['key'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evita fallos de certificados en el servidor
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($res, true);

        // Verificamos si el usuario existe y si la contraseña coincide exactamente
        if ($http == 200 && isset($data['fields']['contrasena']['stringValue'])) {
            $passEnBD = $data['fields']['contrasena']['stringValue'];

            if ($passEnBD === $pass) {
                // LOGIN OK -> Crear cookie y redirigir
                // Ponemos una cookie de sesión local (se borra al cerrar el navegador)
                setcookie("auth_user", bin2hex(random_bytes(16)), 0, "/");
                
                header("Location: selectorAper.php");
                exit();
            } else {
                $err = "CONTRASEÑA INCORRECTA";
            }
        } else {
            $err = "USUARIO NO ENCONTRADO O ERROR DE CONEXIÓN";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SISTEMA</title>
    <style>
        body { background:#000; color:#0f0; font-family:monospace; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
        .card { border:1px solid #0f0; padding:20px; background:#0a0a0a; box-shadow:0 0 10px #0f0; width:280px; }
        input { width:100%; padding:10px; margin:10px 0; background:#000; border:1px solid #0f0; color:#0f0; box-sizing:border-box; }
        button { width:100%; padding:10px; background:#0f0; color:#000; border:none; cursor:pointer; font-weight:bold; }
        .msg { color:#f00; font-size:12px; text-align:center; margin-bottom:10px; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="text-align:center">ACCESS_REQUIRED</h2>
        <?php if($err) echo "<div class='msg'>$err</div>"; ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="USUARIO" required>
            <input type="password" name="contrasena" placeholder="PASSWORD" required>
            <button type="submit">ENTRAR</button>
        </form>
    </div>
</body>
</html>
