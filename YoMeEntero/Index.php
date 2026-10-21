<?php
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

if (isset($_SESSION['usuario_autenticado'])) {
    header("Location: Actividades.php");
    exit();
}

$mensaje = isset($_GET['msg']) ? htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Bienvenido - Colegio Técnico Palermo IED</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet"/>
    <style>
        :root { 
            --azul: #003DA5; 
            --rojo: #C8102E; 
            --blanco: #ffffff; 
            --gris-f: #f4f6fb; 
            --gradiente: linear-gradient(135deg, var(--azul) 0%, #1a3a8a 55%, var(--rojo) 100%); 
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Source Sans 3', sans-serif; background: var(--gris-f); color: #222; }
        .full-screen { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--gradiente); padding: 20px; text-align: center; }
        .card-itex { background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); padding: 40px; border-radius: 20px; max-width: 600px; width: 100%; color: white; box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
        .escudo { width: 65px; height: 65px; background: white; color: var(--azul); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-family: 'Merriweather', serif; font-size: 26px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
        .alert-msg { background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.9em; }
        .btn-group { margin-top: 25px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-white { background: white; color: var(--azul); padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: background 0.3s ease, transform 0.2s ease; }
        .btn-white:hover { background: #f0f0f0; transform: translateY(-2px); }
        .btn-outline { background: transparent; color: white; border: 1.5px solid white; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: background 0.3s ease, transform 0.2s ease; }
        .btn-outline:hover { background: rgba(255, 255, 255, 0.15); transform: translateY(-2px); }
    </style>
</head>
<body>
    <main class="full-screen">
        <div class="card-itex">
            <div class="escudo" aria-label="Logo Institucional">P</div>
            <h1 style="font-family:'Merriweather', serif; margin-bottom:15px;">Bienvenido</h1>
            <?php if (!empty($mensaje)): ?>
                <div class="alert-msg"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <p>Esta plataforma se diseñó para ayudar con la difusión de las actividades extracurriculares dentro de la comunidad educativa del Colegio Técnico Palermo IED.</p>
            <div class="btn-group">
                <a href="Login.php" class="btn-white">Iniciar Sesión</a>
                <a href="Registro.php" class="btn-outline">Registrarse</a>
            </div>
        </div>
    </main>
</body>
</html>