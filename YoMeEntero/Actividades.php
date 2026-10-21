<?php
session_start();
if (!isset($_SESSION['usuario_autenticado'])) {
    header("Location: Login.php");
    exit();
}

require_once 'config/conexion.php';

$query = "SELECT a.actividadid, a.nombre, a.descripcion, a.proponente, c.codigo AS cat_codigo, c.nombre AS cat_nombre 
          FROM actividad a 
          INNER JOIN categoria_actividad c ON a.categoriaid = c.categoriaid";
$resultado = $mysqli->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Colegio Técnico Palermo - Actividades</title>
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
  .contenido { max-width: 860px; margin: 0 auto; padding: 24px 16px; }
  h2 { font-size: 18px; color: #1a3a5c; margin-bottom: 16px; }
  .filtros { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
  .filtro { padding: 5px 14px; font-size: 12px; border: 1px solid #ccc; border-radius: 20px; cursor: pointer; background: #fff; color: #555; }
  .filtro.on { background: #1a3a5c; color: #fff; border-color: #1a3a5c; }
  .grilla { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 14px; }
  .tarjeta { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; }
  .etiqueta { display: inline-block; font-size: 11px; font-weight: bold; padding: 3px 10px; border-radius: 20px; margin-bottom: 8px; text-transform: uppercase; align-self: flex-start; }
  .dep { background: #dbeafe; color: #1e40af; }
  .cult { background: #dcfce7; color: #166534; }
  .acad { background: #fef3c7; color: #92400e; }
  .ext { background: #fce7f3; color: #9d174d; }
  .tarjeta h3 { font-size: 14px; margin-bottom: 8px; color: #111; }
  .tarjeta p { font-size: 12px; color: #666; line-height: 1.6; flex-grow: 1; }
  .proponente { font-size: 12px; color: #1a3a5c; font-weight: bold; margin-top: 10px; border-top: 1px solid #eee; padding-top: 8px; }
  .btn-ins { margin-top: 10px; width: 100%; padding: 7px; border: 1px solid #1a3a5c; background: #fff; color: #1a3a5c; font-size: 12px; border-radius: 6px; cursor: pointer; }
  .btn-ins:hover { background: #1a3a5c; color: #fff; }
  .sin-resultados { display: none; grid-column: 1 / -1; text-align: center; color: #777; padding: 30px; font-size: 14px; }
</style>
</head>
<body>

<header>
  <div class="logo-nombre">Colegio Técnico Palermo</div>
  <div class="autores">
    <span>Usuario: <?php echo htmlspecialchars($_SESSION['usuario_autenticado'], ENT_QUOTES, 'UTF-8'); ?></span>
    <a href="Logout.php" class="btn-logout">Salir</a>
  </div>
</header>

<nav id="nav">
  <button class="activo" onclick="location.href='Actividades.php'">Actividades</button>
  <button onclick="location.href='Avisos.php'">Avisos</button>
  <button onclick="location.href='Calendario.php'">Calendario</button>
  <button onclick="location.href='Inscripcion.php'">Inscripción</button>
  <button onclick="location.href='Consultas.php'">Consultas CRUD</button>
  <button onclick="location.href='Nosotros.php'">Sobre nosotros</button>
</nav>

<div class="contenido">
  <h2>Actividades Extracurriculares</h2>
  <div class="filtros">
    <span class="filtro on" onclick="filtrar('todas', this)">Todas</span>
    <span class="filtro" onclick="filtrar('dep', this)">Deportivas</span>
    <span class="filtro" onclick="filtrar('cult', this)">Culturales</span>
    <span class="filtro" onclick="filtrar('acad', this)">Académicas</span>
    <span class="filtro" onclick="filtrar('ext', this)">Extracurriculares</span>
  </div>
  
  <div class="grilla" id="grilla">
    <?php if ($resultado && $resultado->num_rows > 0): ?>
      <?php while ($act = $resultado->fetch_assoc()): ?>
        <div class="tarjeta" data-cat="<?php echo htmlspecialchars($act['cat_codigo']); ?>">
          <div>
            <span class="etiqueta <?php echo htmlspecialchars($act['cat_codigo']); ?>"><?php echo htmlspecialchars($act['cat_nombre']); ?></span>
            <h3><?php echo htmlspecialchars($act['nombre']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($act['descripcion'])); ?></p>
          </div>
          <div>
            <div class="proponente"><?php echo htmlspecialchars($act['proponente']); ?></div>
            <button class="btn-ins" onclick="irForm('<?php echo htmlspecialchars($act['nombre'], ENT_QUOTES); ?>')">Inscribirse</button>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="grid-column: 1/-1;">No hay actividades registradas en la base de datos.</p>
    <?php endif; ?>
    <div class="sin-resultados" id="sinResultados">No se encontraron actividades disponibles en esta categoría.</div>
  </div>
</div>

<script>
  function filtrar(cat, el) {
    document.querySelectorAll('.filtro').forEach(f => f.classList.remove('on'));
    el.classList.add('on');
    let visibles = 0;
    document.querySelectorAll('.tarjeta').forEach(t => {
      if (cat === 'todas' || t.dataset.cat === cat) {
        t.style.display = 'flex';
        visibles++;
      } else {
        t.style.display = 'none';
      }
    });
    document.getElementById('sinResultados').style.display = (visibles === 0) ? 'block' : 'none';
  }

  function irForm(actividad) {
    if (!actividad) return;
    sessionStorage.setItem('actividadSeleccionada', actividad);
    window.location.href = 'Inscripcion.php?actividad=' + encodeURIComponent(actividad);
  }
</script>
</body>
</html>