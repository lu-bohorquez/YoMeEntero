<?php
session_start();
if (!isset($_SESSION['usuario_autenticado'])) {
    header("Location: Login.php");
    exit();
}

require_once 'config/conexion.php';

$mensaje_estado = "";
$tipo_mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actividad_id = intval($_POST['actividad_id'] ?? 0);
    $nombre       = trim($_POST['nombre'] ?? '');
    $doc          = trim($_POST['numero_documento'] ?? '');
    $grado        = trim($_POST['grado'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');

    if ($actividad_id > 0 && !empty($nombre) && !empty($doc) && !empty($grado) && !empty($telefono)) {
        $mysqli->begin_transaction();
        try {
            // Verificar o crear estudiante
            $stmt_est = $mysqli->prepare("SELECT estudianteid FROM estudiante WHERE numero_documento = ?");
            $stmt_est->bind_param("s", $doc);
            $stmt_est->execute();
            $res_est = $stmt_est->get_result();

            if ($row_est = $res_est->fetch_assoc()) {
                $estudiante_id = $row_est['estudianteid'];
            } else {
                $stmt_ins_est = $mysqli->prepare("INSERT INTO estudiante (nombre, tipo_documento, numero_documento, grado, telefono_acudiente) VALUES (?, 'TI', ?, ?, ?)");
                $stmt_ins_est->bind_param("ssss", $nombre, $doc, $grado, $telefono);
                $stmt_ins_est->execute();
                $estudiante_id = $stmt_ins_est->insert_id;
                $stmt_ins_est->close();
            }
            $stmt_est->close();

            // Insertar Inscripción
            $stmt_ins = $mysqli->prepare("INSERT INTO inscripcion (estudianteid, actividadid, estado) VALUES (?, ?, 'Pendiente')");
            $stmt_ins->bind_param("ii", $estudiante_id, $actividad_id);
            $stmt_ins->execute();
            $stmt_ins->close();

            $mysqli->commit();
            $mensaje_estado = "¡Inscripción registrada con éxito para " . htmlspecialchars($nombre) . "!";
            $tipo_mensaje = "exito";
        } catch (Exception $e) {
            $mysqli->rollback();
            $mensaje_estado = "Error al guardar la inscripción: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje_estado = "Por favor completa todos los campos requeridos correctamente.";
        $tipo_mensaje = "error";
    }
}

// Cargar catálogo de actividades
$actividades_db = $mysqli->query("SELECT actividadid, nombre FROM actividad ORDER BY nombre ASC");
$actividad_get = $_GET['actividad'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Colegio Técnico Palermo - Inscripción</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #f5f5f5; color: #222; }
  header { background: #1a3a5c; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .logo-nombre { color: #fff; font-size: 16px; font-weight: bold; }
  .autores { color: rgba(255,255,255,0.7); font-size: 12px; display: flex; gap: 15px; align-items: center; }
  .btn-logout { background: #C8102E; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
  nav { background: #fff; border-bottom: 2px solid #e0e0e0; display: flex; overflow-x: auto; }
  nav button { padding: 12px 20px; font-size: 13px; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; color: #666; }
  nav button.activo { color: #1a3a5c; border-bottom-color: #1a3a5c; font-weight: bold; }
  .contenido { max-width: 860px; margin: 0 auto; padding: 24px 16px; }
  .formulario { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px; max-width: 520px; margin: 0 auto; }
  .formulario h2 { margin-bottom: 6px; font-size: 18px; color: #1a3a5c; }
  .formulario p.desc { font-size: 13px; color: #666; margin-bottom: 20px; }
  .campo { margin-bottom: 14px; }
  .campo label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; }
  .campo input, .campo select { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 13px; outline: none; }
  .fila { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .btn-enviar { width: 100%; padding: 10px; background: #1a3a5c; color: #fff; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; margin-top: 6px; }
  .msg-alerta { border-radius: 6px; padding: 12px; font-size: 13px; text-align: center; margin-bottom: 15px; }
  .msg-exito { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
  .msg-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
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
  <button class="activo" onclick="location.href='Inscripcion.php'">Inscripción</button>
  <button onclick="location.href='Consultas.php'">Consultas CRUD</button>
  <button onclick="location.href='Nosotros.php'">Sobre nosotros</button>
</nav>

<div class="contenido">
  <div class="formulario">
    <h2>Formulario de Inscripción</h2>
    <p class="desc">Completa tus datos para registrarte en la actividad seleccionada.</p>

    <?php if (!empty($mensaje_estado)): ?>
      <div class="msg-alerta <?php echo ($tipo_mensaje === 'exito') ? 'msg-exito' : 'msg-error'; ?>">
        <?php echo $mensaje_estado; ?>
      </div>
    <?php endif; ?>

    <form action="Inscripcion.php" method="POST">
      <div class="campo">
        <label for="sel-act">Actividad *</label>
        <select id="sel-act" name="actividad_id" required>
          <option value="">Selecciona una actividad</option>
          <?php if ($actividades_db): ?>
            <?php while ($a = $actividades_db->fetch_assoc()): ?>
              <option value="<?php echo $a['actividadid']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
            <?php endwhile; ?>
          <?php endif; ?>
        </select>
      </div>

      <div class="campo">
        <label>Nombre Completo del Estudiante *</label>
        <input type="text" name="nombre" required placeholder="Ej: Juan Pérez"/>
      </div>

      <div class="campo">
        <label>Número de Documento *</label>
        <input type="text" name="numero_documento" required placeholder="Ej: 1010123456"/>
      </div>

      <div class="fila">
        <div class="campo">
          <label>Grado *</label>
          <select name="grado" required>
            <option value="">Selecciona</option>
            <option value="Grado 6">Grado 6</option>
            <option value="Grado 7">Grado 7</option>
            <option value="Grado 8">Grado 8</option>
            <option value="Grado 9">Grado 9</option>
            <option value="Grado 10">Grado 10</option>
            <option value="Grado 11">Grado 11</option>
          </select>
        </div>
        <div class="campo">
          <label>Teléfono *</label>
          <input type="tel" name="telefono" required pattern="[0-9]{7,10}" placeholder="3100000000"/>
        </div>
      </div>

      <button type="submit" class="btn-enviar">Enviar Inscripción</button>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const selector = document.getElementById('sel-act');
    const actGet = "<?php echo htmlspecialchars($actividad_get, ENT_QUOTES, 'UTF-8'); ?>";
    const actGuardada = sessionStorage.getItem('actividadSeleccionada');
    const objetivo = actGet || actGuardada;

    if (objetivo) {
      for (let option of selector.options) {
        if (option.text.trim() === objetivo.trim()) {
          option.selected = true;
          break;
        }
      }
      sessionStorage.removeItem('actividadSeleccionada');
    }
  });
</script>
</body>
</html>