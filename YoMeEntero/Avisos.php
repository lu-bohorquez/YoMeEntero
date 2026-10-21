<?php
session_start();
if (!isset($_SESSION['usuario_autenticado'])) {
    header("Location: Login.php");
    exit();
}
require_once 'config/conexion.php';

$mensaje_estado = "";
$tipo_mensaje = "";

// 1. ELIMINAR AVISO (Delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_del = intval($_GET['id']);
    $stmt = $mysqli->prepare("DELETE FROM aviso WHERE avisoid = ?");
    $stmt->bind_param("i", $id_del);
    if ($stmt->execute()) {
        $mensaje_estado = "Aviso eliminado correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje_estado = "Error al eliminar el aviso: " . $mysqli->error;
        $tipo_mensaje = "error";
    }
    $stmt->close();
}

// 2. CREAR O EDITAR AVISO (Create / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $avisoid = intval($_POST['avisoid'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $emisor = trim($_POST['emisor'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');

    if (!empty($titulo) && !empty($contenido)) {
        if (empty($emisor)) {
            $emisor = 'Rectoría';
        }

        if ($avisoid > 0) {
            // Actualizar aviso existente
            $stmt = $mysqli->prepare("UPDATE aviso SET titulo = ?, emisor = ?, contenido = ? WHERE avisoid = ?");
            $stmt->bind_param("sssi", $titulo, $emisor, $contenido, $avisoid);
            if ($stmt->execute()) {
                $mensaje_estado = "Aviso actualizado con éxito.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje_estado = "Error al actualizar el aviso.";
                $tipo_mensaje = "error";
            }
            $stmt->close();
        } else {
            // Insertar nuevo aviso
            $stmt = $mysqli->prepare("INSERT INTO aviso (titulo, emisor, contenido) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $titulo, $emisor, $contenido);
            if ($stmt->execute()) {
                $mensaje_estado = "Aviso publicado exitosamente.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje_estado = "Error al guardar el aviso.";
                $tipo_mensaje = "error";
            }
            $stmt->close();
        }
    } else {
        $mensaje_estado = "Por favor completa el título y el contenido del aviso.";
        $tipo_mensaje = "error";
    }
}

// 3. CONSULTAR AVISO A EDITAR
$aviso_editar = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_edit = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT avisoid, titulo, emisor, contenido FROM aviso WHERE avisoid = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $aviso_editar = $res->fetch_assoc();
    }
    $stmt->close();
}

// 4. CONSULTAR TODOS LOS AVISOS
$sql = "SELECT avisoid, titulo, contenido, emisor, fecha_publicacion FROM aviso ORDER BY fecha_publicacion DESC";
$resultado = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Colegio Técnico Palermo - Avisos Administrable</title>
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
  .contenido { max-width: 860px; margin: 0 auto; padding: 24px 16px; }

  .card-admin { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
  .card-admin h3 { color: #1a3a5c; font-size: 16px; margin-bottom: 12px; }
  .fila-form { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 12px; }
  .campo { margin-bottom: 10px; }
  .campo label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; color: #333; }
  .campo input, .campo textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; font-family: inherit; }
  .btn-guardar { background: #1a3a5c; color: white; border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; }
  .btn-cancelar { background: #666; color: white; border: none; padding: 9px 18px; border-radius: 4px; text-decoration: none; font-size: 13px; display: inline-block; }

  .aviso-item { background: #fff; border-left: 4px solid #C8102E; border-radius: 4px; padding: 16px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .aviso-header { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 12px; color: #777; }
  .aviso-titulo { font-size: 16px; font-weight: bold; color: #1a3a5c; margin-bottom: 6px; }
  .aviso-body { font-size: 13px; color: #444; line-height: 1.5; margin-bottom: 10px; }
  
  .acciones { display: flex; gap: 12px; font-size: 12px; border-top: 1px solid #f0f0f0; padding-top: 8px; }
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
  <button class="activo" onclick="location.href='Avisos.php'">Avisos</button>
  <button onclick="location.href='Calendario.php'">Calendario</button>
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
    <h3><?php echo $aviso_editar ? 'Editar Aviso Publicado' : 'Publicar Nuevo Aviso'; ?></h3>
    <form method="POST" action="Avisos.php">
      <input type="hidden" name="avisoid" value="<?php echo $aviso_editar['avisoid'] ?? 0; ?>"/>
      
      <div class="fila-form">
        <div class="campo">
          <label>Título del Aviso *</label>
          <input type="text" name="titulo" required value="<?php echo htmlspecialchars($aviso_editar['titulo'] ?? ''); ?>" placeholder="Ej: Suspensión de actividades extraescolares"/>
        </div>
        <div class="campo">
          <label>Emisor / Dependencia</label>
          <input type="text" name="emisor" value="<?php echo htmlspecialchars($aviso_editar['emisor'] ?? 'Rectoría'); ?>" placeholder="Ej: Rectoría / Coordinación"/>
        </div>
      </div>

      <div class="campo">
        <label>Contenido del Aviso *</label>
        <textarea name="contenido" rows="4" required placeholder="Escribe aquí el comunicado completo..."><?php echo htmlspecialchars($aviso_editar['contenido'] ?? ''); ?></textarea>
      </div>

      <div>
        <button type="submit" class="btn-guardar"><?php echo $aviso_editar ? 'Actualizar Aviso' : 'Publicar Aviso'; ?></button>
        <?php if ($aviso_editar): ?>
          <a href="Avisos.php" class="btn-cancelar">Cancelar Edición</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- LISTADO DE AVISOS -->
  <h2 style="margin-bottom: 15px; color: #1a3a5c; font-size: 18px;">Comunicados Institucionales</h2>
  <?php if ($resultado && $resultado->num_rows > 0): ?>
    <?php while ($aviso = $resultado->fetch_assoc()): ?>
      <div class="aviso-item">
        <div class="aviso-header">
          <span><strong>Emisor:</strong> <?php echo htmlspecialchars($aviso['emisor'] ?? 'Rectoría'); ?></span>
          <span><?php echo date("d/m/Y H:i", strtotime($aviso['fecha_publicacion'])); ?></span>
        </div>
        <div class="aviso-titulo"><?php echo htmlspecialchars($aviso['titulo']); ?></div>
        <div class="aviso-body"><?php echo nl2br(htmlspecialchars($aviso['contenido'])); ?></div>
        <div class="acciones">
          <a href="Avisos.php?action=edit&id=<?php echo $aviso['avisoid']; ?>" class="btn-edit">Editar</a>
          <a href="Avisos.php?action=delete&id=<?php echo $aviso['avisoid']; ?>" class="btn-del" onclick="return confirm('¿Seguro que deseas eliminar este aviso?')">Eliminar</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="card-admin"><p style="font-size: 13px; color: #666;">No hay avisos o comunicados publicados.</p></div>
  <?php endif; ?>

</div>

</body>
</html>