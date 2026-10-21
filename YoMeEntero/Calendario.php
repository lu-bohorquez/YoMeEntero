<?php
session_start();
if (!isset($_SESSION['usuario_autenticado'])) {
    header("Location: Login.php");
    exit();
}
require_once 'config/conexion.php';

$mensaje_estado = "";
$tipo_mensaje = "";

// 1. ELIMINAR EVENTO (Delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_del = intval($_GET['id']);
    $stmt = $mysqli->prepare("DELETE FROM evento_calendario WHERE eventoid = ?");
    $stmt->bind_param("i", $id_del);
    if ($stmt->execute()) {
        $mensaje_estado = "Evento eliminado correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje_estado = "Error al eliminar el evento: " . $mysqli->error;
        $tipo_mensaje = "error";
    }
    $stmt->close();
}

// 2. CREAR O EDITAR EVENTO (Create / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventoid = intval($_POST['eventoid'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
    $lugar = trim($_POST['lugar'] ?? '');

    if (!empty($titulo) && !empty($fecha_inicio)) {
        if ($eventoid > 0) {
            // Actualizar evento existente
            $stmt = $mysqli->prepare("UPDATE evento_calendario SET titulo = ?, descripcion = ?, fecha_inicio = ?, lugar = ? WHERE eventoid = ?");
            $stmt->bind_param("ssssi", $titulo, $descripcion, $fecha_inicio, $lugar, $eventoid);
            if ($stmt->execute()) {
                $mensaje_estado = "Evento actualizado con éxito.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje_estado = "Error al actualizar el evento.";
                $tipo_mensaje = "error";
            }
            $stmt->close();
        } else {
            // Insertar nuevo evento
            $stmt = $mysqli->prepare("INSERT INTO evento_calendario (titulo, descripcion, fecha_inicio, lugar) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $titulo, $descripcion, $fecha_inicio, $lugar);
            if ($stmt->execute()) {
                $mensaje_estado = "Evento programado exitosamente.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje_estado = "Error al guardar el evento.";
                $tipo_mensaje = "error";
            }
            $stmt->close();
        }
    } else {
        $mensaje_estado = "Por favor completa los campos obligatorios (*).";
        $tipo_mensaje = "error";
    }
}

// 3. CONSULTAR EVENTO A EDITAR (Si aplica)
$evento_editar = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_edit = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT eventoid, titulo, descripcion, fecha_inicio, lugar FROM evento_calendario WHERE eventoid = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $evento_editar = $res->fetch_assoc();
    }
    $stmt->close();
}

// 4. CONSULTAR TODOS LOS EVENTOS
$sql = "SELECT eventoid, titulo, descripcion, fecha_inicio, lugar FROM evento_calendario ORDER BY fecha_inicio ASC";
$resultado = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Colegio Técnico Palermo - Calendario Administrable</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #f5f5f5; color: #222; }
  header { background: #1a3a5c; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .logo-nombre { color: #fff; font-size: 16px; font-weight: bold; }
  .autores { color: rgba(255,255,255,0.7); font-size: 12px; display: flex; gap: 15px; align-items: center; }
  .btn-logout { background: #C8102E; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
  nav { background: #fff; border-bottom: 2px solid #e0e0e0; display: flex; overflow-x: auto; }
  nav button { padding: 12px 20px; font-size: 13px; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; color: #666; white-space: nowrap; }
  nav button.activo { color: #1a3a5c; border-bottom-color: #1a3a5c; font-weight: bold; }
  .contenido { max-width: 900px; margin: 0 auto; padding: 24px 16px; }
  
  .card-admin { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
  .card-admin h3 { color: #1a3a5c; font-size: 16px; margin-bottom: 12px; }
  .fila-form { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
  .campo { margin-bottom: 10px; }
  .campo label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333; }
  .campo input, .campo textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; }
  .btn-guardar { background: #1a3a5c; color: white; border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; }
  .btn-cancelar { background: #666; color: white; border: none; padding: 9px 18px; border-radius: 4px; text-decoration: none; font-size: 13px; display: inline-block; }
  
  .evento { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 12px; display: flex; gap: 15px; align-items: center; justify-content: space-between; }
  .evento-left { display: flex; gap: 15px; align-items: center; }
  .fecha-badge { background: #003DA5; color: white; padding: 10px; border-radius: 6px; text-align: center; min-width: 75px; }
  .fecha-badge .dia { font-size: 18px; font-weight: bold; }
  .fecha-badge .mes { font-size: 11px; text-transform: uppercase; }
  .fecha-badge .hora { font-size: 10px; opacity: 0.85; margin-top: 2px; }
  .evento-info h4 { font-size: 15px; color: #1a3a5c; }
  .evento-info p { font-size: 12px; color: #555; margin-top: 3px; }
  .evento-info .lugar { font-size: 11px; color: #166534; font-weight: bold; margin-top: 4px; }
  .acciones { display: flex; gap: 10px; font-size: 12px; }
  .btn-edit { color: #003DA5; text-decoration: none; font-weight: bold; }
  .btn-del { color: #C8102E; text-decoration: none; font-weight: bold; }

  .msg-alerta { padding: 10px; border-radius: 4px; font-size: 13px; margin-bottom: 15px; }
  .msg-exito { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
  .msg-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
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
  <button class="activo" onclick="location.href='Calendario.php'">Calendario</button>
  <button onclick="location.href='Inscripcion.php'">Inscripción</button>
  <button onclick="location.href='Consultas.php'">Consultas CRUD</button>
  <button onclick="location.href='Nosotros.php'">Sobre nosotros</button>
</nav>

<div class="contenido">

  <?php if (!empty($mensaje_estado)): ?>
    <div class="msg-alerta <?php echo ($tipo_mensaje === 'exito') ? 'msg-exito' : 'msg-error'; ?>">
      <?php echo htmlspecialchars($mensaje_estado); ?>
    </div>
  <?php endif; ?>

  <!-- FORMULARIO ADMINISTRATIVO (CREAR / EDITAR) -->
  <div class="card-admin">
    <h3><?php echo $evento_editar ? 'Editar Evento de la Agenda' : 'Programar Nuevo Evento'; ?></h3>
    <form method="POST" action="Calendario.php">
      <input type="hidden" name="eventoid" value="<?php echo $evento_editar['eventoid'] ?? 0; ?>"/>
      
      <div class="fila-form">
        <div class="campo">
          <label>Título del Evento *</label>
          <input type="text" name="titulo" required value="<?php echo htmlspecialchars($evento_editar['titulo'] ?? ''); ?>" placeholder="Ej: Torneo Deportivo"/>
        </div>
        <div class="campo">
          <label>Fecha y Hora *</label>
          <input type="datetime-local" name="fecha_inicio" required value="<?php echo isset($evento_editar['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($evento_editar['fecha_inicio'])) : ''; ?>"/>
        </div>
      </div>

      <div class="fila-form">
        <div class="campo">
          <label>Lugar / Ubicación</label>
          <input type="text" name="lugar" value="<?php echo htmlspecialchars($evento_editar['lugar'] ?? ''); ?>" placeholder="Ej: Cancha Principal / Auditorio"/>
        </div>
        <div class="campo">
          <label>Descripción</label>
          <input type="text" name="descripcion" value="<?php echo htmlspecialchars($evento_editar['descripcion'] ?? ''); ?>" placeholder="Detalle del evento"/>
        </div>
      </div>

      <div>
        <button type="submit" class="btn-guardar"><?php echo $evento_editar ? 'Actualizar Evento' : 'Guardar Evento'; ?></button>
        <?php if ($evento_editar): ?>
          <a href="Calendario.php" class="btn-cancelar">Cancelar Edición</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- LISTADO DE EVENTOS PROGRAMADOS -->
  <h2 style="margin-bottom: 15px; color: #1a3a5c; font-size: 18px;">Eventos Registrados</h2>
  <?php if ($resultado && $resultado->num_rows > 0): ?>
    <?php while ($ev = $resultado->fetch_assoc()): 
      $time = strtotime($ev['fecha_inicio']);
    ?>
      <div class="evento">
        <div class="evento-left">
          <div class="fecha-badge">
            <div class="dia"><?php echo date("d", $time); ?></div>
            <div class="mes"><?php echo date("M", $time); ?></div>
            <div class="hora"><?php echo date("h:i a", $time); ?></div>
          </div>
          <div class="evento-info">
            <h4><?php echo htmlspecialchars($ev['titulo']); ?></h4>
            <p><?php echo htmlspecialchars($ev['descripcion'] ?? ''); ?></p>
            <?php if (!empty($ev['lugar'])): ?>
              <div class="lugar">📍 <?php echo htmlspecialchars($ev['lugar']); ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="acciones">
          <a href="Calendario.php?action=edit&id=<?php echo $ev['eventoid']; ?>" class="btn-edit">Editar</a>
          <a href="Calendario.php?action=delete&id=<?php echo $ev['eventoid']; ?>" class="btn-del" onclick="return confirm('¿Seguro que deseas eliminar este evento del calendario?')">Eliminar</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="card-admin"><p style="font-size: 13px; color: #666;">No existen eventos programados en la agenda.</p></div>
  <?php endif; ?>

</div>

</body>
</html>