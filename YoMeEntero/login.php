<?php
session_start();
require_once 'config/conexion.php';

if (isset($_SESSION['usuario_autenticado'])) {
    header("Location: Actividades.php");
    exit();
}

$mensaje_error = "";
$registro_exito = isset($_GET['registro']) && $_GET['registro'] === 'exito';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['usuario'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (!empty($user) && !empty($pass)) {
        $stmt = $mysqli->prepare("SELECT usuarioid, username, password FROM usuario WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($row = $resultado->fetch_assoc()) {
            if (password_verify($pass, $row['password']) || $pass === $row['password']) {
                $_SESSION['usuario_autenticado'] = $row['username'];
                $_SESSION['usuario_id'] = $row['usuarioid'];
                header("Location: Actividades.php");
                exit();
            } else {
                $mensaje_error = "Usuario o contraseña incorrectos.";
            }
        } else {
            $mensaje_error = "Usuario o contraseña incorrectos.";
        }
        $stmt->close();
    } else {
        $mensaje_error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Acceso - Colegio Técnico Palermo IED</title>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet"/>
    <style>
        :root { --azul: #003DA5; --rojo: #C8102E; --blanco: #ffffff; --gris-f: #f4f6fb; --gris-b: #d0d8e8; --gris-t: #718096; --gradiente: linear-gradient(135deg, var(--azul) 0%, #1a3a8a 55%, var(--rojo) 100%); }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Source Sans 3', sans-serif; background: var(--gris-f); color: #222; }
        .full-screen { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--gradiente); padding: 20px; }
        .login-box { background: var(--blanco); border-radius: 12px; padding: 35px; width: 100%; max-width: 380px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); text-align: center; }
        .escudo { width: 65px; height: 65px; background: var(--gradiente); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-family: 'Merriweather'; font-size: 26px; }
        .campo { text-align: left; margin-bottom: 15px; }
        .campo label { display: block; font-size: 13px; font-weight: 600; color: var(--azul); margin-bottom: 5px; }
        .campo input { width: 100%; padding: 11px; border: 1.5px solid var(--gris-b); border-radius: 6px; outline: none; }
        .btn-azul { background: var(--azul); color: white; width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .alert { font-size: 13px; color: var(--rojo); margin-bottom: 12px; }
        .alert-ok { font-size: 13px; color: #166534; margin-bottom: 12px; }
        .link { display: block; margin-top: 15px; font-size: 13px; color: var(--azul); text-decoration: underline; }
    </style>
</head>
<body>
    <div class="full-screen">
        <div class="login-box">
            <div class="escudo">P</div>
            <h1>Acceso Institucional</h1>
            <p style="font-size: 12px; color: var(--gris-t); margin-bottom: 20px;">Colegio Técnico Palermo IED</p>

            <?php if ($registro_exito): ?>
                <div class="alert-ok">¡Cuenta creada con éxito! Inicia sesión.</div>
            <?php endif; ?>

            <?php if (!empty($mensaje_error)): ?>
                <div class="alert"><?php echo htmlspecialchars($mensaje_error); ?></div>
            <?php endif; ?>

            <form method="POST" action="Login.php">
                <div class="campo">
                    <label>Usuario</label>
                    <input type="text" name="usuario" required placeholder="palermo">
                </div>
                <div class="campo">
                    <label>Contraseña</label>
                    <input type="password" name="password" required placeholder="••••">
                </div>
                <button type="submit" class="btn-azul">Entrar</button>
            </form>
            <a href="Registro.php" class="link">¿No tienes cuenta? Regístrate aquí</a>
        </div>
    </div>
</body>
</html>