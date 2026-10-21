<?php
session_start();
if (!isset($_SESSION['usuario_autenticado'])) {
    header("Location: Login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Colegio Técnico Palermo - Nosotros</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #f5f5f5; color: #222; }
  header { background: #1a3a5c; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; }
  .logo-nombre { color: #fff; font-size: 16px; font-weight: bold; }
  .autores { color: rgba(255,255,255,0.7); font-size: 12px; display: flex; gap: 15px; align-items: center; }
  .btn-logout { background: #C8102E; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
  nav { background: #fff; border-bottom: 2px solid #e0e0e0; display: flex; overflow-x: auto; }
  nav button { padding: 12px 20px; font-size: 13px; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; color: #666; white-space: nowrap; }
  nav button.activo { color: #1a3a5c; border-bottom-color: #1a3a5c; font-weight: bold; }
  nav button:hover { background: #f0f4fb; color: #1a3a5c; }
  .contenido { max-width: 860px; margin: 0 auto; padding: 24px 16px; }
  .tarjeta { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
  h2 { font-size: 18px; color: #1a3a5c; margin-bottom: 12px; }
  p { font-size: 14px; line-height: 1.6; color: #444; }
  ul { margin-top: 10px; padding-left: 20px; font-size: 13px; color: #666; }
</style>
</head>
<body>

<header>
  <div class="logo-nombre">Colegio Técnico Palermo</div>
  <div class="autores">
    <span>Usuario: <?php echo htmlspecialchars($_SESSION['usuario_autenticado']); ?></span>
    <a href="Logout.php" class="btn-logout">Salir</a>
  </div>
</header>

<nav>
  <button onclick="location.href='Actividades.php'">Actividades</button>
  <button onclick="location.href='Avisos.php'">Avisos</button>
  <button onclick="location.href='Calendario.php'">Calendario</button>
  <button onclick="location.href='Inscripcion.php'">Inscripción</button>
  <button onclick="location.href='Consultas.php'">Consultas CRUD</button>
  <button class="activo" onclick="location.href='Nosotros.php'">Sobre nosotros</button>
</nav>

<div class="contenido">
  <div class="tarjeta">
    <h2>Sobre el Proyecto</h2>
    <p>Plataforma impulsada para centralizar la comunicación de actividades institucionales en el Colegio Técnico Palermo IED.</p>
    <hr style="margin: 15px 0; border: none; border-top: 1px solid #eee;">
    <h3>Equipo de Desarrollo</h3>
    <ul>
      <li>Felipe Gómez</li>
      <li>Laura Bohórquez</li>
      <li>Sebastián Rojas</li>
    </ul>
  </div>
</div>

</body>
</html>