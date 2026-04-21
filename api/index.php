<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userInput = $_POST['usuario'] ?? '';
    $passInput = $_POST['contrasena'] ?? '';

    // Leer config desde el JSON
    $config = json_decode(file_get_contents('config.json'), true);
    $projectId = $config['projectId'];

    // Endpoint de Firestore para obtener el documento del usuario específico
    // URL: https://firestore.googleapis.com/v1/projects/{project}/databases/(default)/documents/usuarios/{usuario}
    $url = "https://firestore.googleapis.com/v1/projects/$projectId/databases/(default)/documents/usuarios/" . urlencode($userInput);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $data = json_decode($response, true);
        
        // Extraer el string de la contraseña desde la estructura de Firestore
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
    } else {
        $error = "El usuario no existe o error de conexión.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Firestore</title>
</head>
<body>
    <h2>Acceso al Sistema</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="usuario" placeholder="Usuario" required><br><br>
        <input type="password" name="contrasena" placeholder="Contraseña" required><br><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
