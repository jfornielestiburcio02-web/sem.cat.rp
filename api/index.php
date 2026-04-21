<?php
session_start();

// 1. CONFIGURACIÓN "OCULTA" (Directamente en el código)
$firebaseConfig = [
    "projectId" => "semcatrp183721293",
    "apiKey"    => "AIzaSyB8UIE_aatbroEr28IB_3PtSDv3qwoPpjg"
];

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userInput = $_POST['usuario'] ?? '';
    $passInput = $_POST['contrasena'] ?? '';

    if (!empty($userInput) && !empty($passInput)) {
        
        $projectId = $firebaseConfig['projectId'];
        
        // 2. CONSTRUCCIÓN DE LA URL
        // Importante: urlencode para evitar errores con carácteres especiales en el nombre de usuario
        $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios/" . urlencode($userInput);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Útil si el servidor tiene certificados viejos

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($response, true);
            
            // 3. EXTRACCIÓN DE LA CONTRASEÑA
            // Firestore devuelve los datos en un formato específico: fields -> contrasena -> stringValue
            $passwordInFirestore = $data['fields']['contrasena']['stringValue'] ?? null;

            if ($passwordInFirestore !== null && $passwordInFirestore === $passInput) {
                // LOGIN OK
                $_SESSION['autenticado'] = true;
                $_SESSION['usuario'] = $userInput;
                
                header("Location: selectorAper.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } elseif ($httpCode == 404) {
            $error = "El usuario '$userInput' no existe en Firestore.";
        } else {
            $error = "Error de conexión con base de datos (Código: $httpCode).";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Firestore</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f0f2f5; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 350px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: #d9534f; font-size: 0.9rem; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Acceso al Sistema</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="usuario" placeholder="Nombre de usuario" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
