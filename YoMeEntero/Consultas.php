<?php
session_start();
if (!isset($_SESSION['usuario_autenticado'])) {
    header("Location: Login.php");
    exit();
}
require_once 'config/conexion.php';

$msg = "";
$err = "";

// Eliminar inscripción
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_del = intval($_GET['id']);
    $stmt = $mysqli->prepare("DELETE FROM inscripcion WHERE inscripcionid = ?");
    $stmt->bind_param("i", $id_del);
    if ($stmt->execute()) {
        $msg = "Inscripción cancelada/eliminada correctamente.";
    } else {
        $err = "Error al eliminar: " . $mysqli->error;
    }
    $stmt->close();
}

// Actualizar Estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $inscripcionid = intval($_POST['inscripcionid']);
    $estado = $_POST['estado'];
    $stmt = $mysqli->prepare("UPDATE inscripcion SET estado = ? WHERE inscripcionid = ?");
    $stmt->bind_param("si", $estado, $inscripcionid);
    if ($stmt->execute()) {
        $msg = "Estado actualizado exitosamente.";
    } else {
        $err = "Error al actualizar el estado.";
    }
    $stmt->close();
}

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$sql = "SELECT i.inscripcionid, e.nombre AS estudiante, e.numero_documento, e.grado, e.telefono_acudiente, 
               a.nombre AS actividad, c.nombre AS categoria, i.estado, i.fecha_inscripcion
        FROM inscripcion i
        INNER JOIN estudiante e ON i.estudianteid = e.estudianteid
        INNER JOIN actividad a ON i.actividadid = a.actividadid
        INNER JOIN categoria_actividad c ON a.categoriaid = c.categoriaid";

if (!empty($busqueda)) {
    $sql .= " WHERE e.nombre LIKE ? OR e.numero_documento LIKE ? OR a.nombre LIKE ?";
}
$sql .= " ORDER BY i.fecha_inscripcion DESC";

$stmt = $mysqli->prepare($sql);
if (!empty($busqueda)) {
    $param = "%{$busqueda}%";
    $stmt->bind_param("sss", $param, $param, $param);
}
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Colegio Técnico Palermo - Consultas y CRUD</title>
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
  .contenido { max-width: 1000px; margin: 0 auto; padding: 24px 16px; }
  .card { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
  th, td { border: 1px solid #e0e0e0; padding: 10px; text-align: left; }
  th { background: #1a3a5c; color: #fff; }
  .btn-del { color: #C8102E; font-weight: bold; text-decoration: none; }
  .form-search { display: flex; gap: 10px; margin-bottom: 15px; }
  .form-search input { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
  .form-search button { padding: 8px 15px; background: #1a3a5c; color: white; border: none; border-radius: 4px; cursor: pointer; }
  .msg-ok { background: #dcfce7; color: #166534; padding: 10px; border-radius: 4px; margin-bottom: 10px; font-size: 13px; }
  .msg-err { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 10px; font-size: 13px; }
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
  <button class="activo" onclick="location.href='Consultas.php'">Consultas CRUD</button>
  <button onclick="location.href='Nosotros.php'">Sobre nosotros</button>
</nav>

<div class="contenido">
  <div class="card">
    <h2 style="margin-bottom: 10px; color: #1a3a5c;">Gestión de Inscripciones y Consultas</h2>
    <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Panel para consultar, actualizar estado y eliminar inscripciones registradas.</p>

    <?php if (!empty($msg)): ?><div class="msg-ok"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if (!empty($err)): ?><div class="msg-err"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>

    <form class="form-search" method="GET" action="Consultas.php">
      <input type="text" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar por estudiante, documento o actividad..."/>
      <button type="submit">Buscar</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>Estudiante</th>
          <th>Documento</th>
          <th>Grado</th>
          <th>Actividad</th>
          <th>Categoría</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resultado && $resultado->num_rows > 0): ?>
          <?php while ($r = $resultado->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['estudiante']); ?></td>
              <td><?php echo htmlspecialchars($r['numero_documento']); ?></td>
              <td><?php echo htmlspecialchars($r['grado']); ?></td>
              <td><?php echo htmlspecialchars($r['actividad']); ?></td>
              <td><?php echo htmlspecialchars($r['categoria']); ?></td>
              <td>
                <form method="POST" action="Consultas.php" style="display:inline;">
                  <input type="hidden" name="inscripcionid" value="<?php echo $r['inscripcionid']; ?>"/>
                  <select name="estado" onchange="this.form.submit()" style="padding: 3px; font-size:12px;">
                    <option value="Pendiente" <?php echo $r['estado'] === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="Confirmado" <?php echo $r['estado'] === 'Confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                    <option value="Cancelado" <?php echo $r['estado'] === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                  </select>
                  <input type="hidden" name="update_status" value="1"/>
                </form>
              </td>
              <td>
                <a href="Consultas.php?action=delete&id=<?php echo $r['inscripcionid']; ?>" class="btn-del" onclick="return confirm('¿Seguro de eliminar esta inscripción?')">Eliminar</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" style="text-align: center;">No se encontraron inscripciones.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>