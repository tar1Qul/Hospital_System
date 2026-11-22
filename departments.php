<?php 
include 'db.php'; 

// 1. SESSION CHECK
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// 2. ADD DEPARTMENT LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
    $name = $_POST['name'] ?? '';
    if (!empty($name)) {
        $stmt = $conn->prepare('INSERT INTO departments (name) VALUES (?)');
        $stmt->bind_param('s', $name);
        $stmt->execute();
    }
    header('Location: departments.php');
    exit;
}

// 3. DELETE DEPARTMENT LOGIC (Optional but recommended for consistency)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM departments WHERE id = $id");
    header("Location: departments.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Departments | MedCore</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
  <style>
    :root {
      --med-blue: #0052cc;
      --med-blue-dark: #0747a6;
      --clinical-bg: #f4f5f7;
      --surface: #ffffff;
      --text-main: #172b4d;
      --text-muted: #6b778c;
      --border: #dfe1e6;
      --success: #36b37e;
      --danger: #ff5630;
      --primary: #1a3a3a;
      --sidebar-w: 260px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--clinical-bg);
      color: var(--text-main);
      display: flex;
      min-height: 100vh;
    }

    /* --- Sidebar --- */
    aside {
      width: var(--sidebar-width);
      background: var(--med-blue-dark);
      color: white;
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
      z-index: 100;
      width: var(--sidebar-w);
    }

    .brand {
      padding: 30px 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(0,0,0,0.1);
    }

    .nav-menu { padding: 20px 12px; flex: 1; }
    .nav-item {
      display: flex; align-items: center; gap: 12px;
      padding: 12px 16px; color: rgba(255,255,255,0.7);
      text-decoration: none; font-size: 14px; font-weight: 500;
      border-radius: 6px; margin-bottom: 4px; transition: 0.2s;
    }
    .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.1); color: white; }

    /* --- Main Content --- */
    main { flex: 1; display: flex; flex-direction: column; }
    .top-bar {
      height: 64px; background: var(--surface); border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: flex-end; padding: 0 32px;
    }

    .content-area { padding: 32px; max-width: 1000px; }
    .page-header { margin-bottom: 24px; }
    .page-header h1 { font-size: 24px; font-weight: 700; }

    /* --- Panel & Form --- */
    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 4px; margin-bottom: 24px; }
    .panel-header { padding: 16px 20px; border-bottom: 1px solid var(--border); background: #fafbfc; }
    .panel-header h2 { font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); }

    .form-inline { display: flex; gap: 12px; padding: 20px; }
    .input-control { flex: 1; padding: 10px 12px; border: 2px solid var(--border); border-radius: 4px; outline: none; font-size: 14px; }
    .input-control:focus { border-color: var(--med-blue); }

    .btn-primary { background: var(--med-blue); color: white; border: none; padding: 0 20px; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 13px; }
    .btn-primary:hover { background: var(--med-blue-dark); }

    /* --- Table --- */
    .dept-table { width: 100%; border-collapse: collapse; }
    .dept-table th { background: #fafbfc; color: var(--text-muted); text-transform: uppercase; font-size: 11px; font-weight: 700; padding: 12px 20px; border-bottom: 2px solid var(--border); text-align: left; }
    .dept-table td { padding: 14px 20px; border-bottom: 1px solid var(--border); font-size: 14px; }
    
    .dept-id { font-family: 'JetBrains Mono', monospace; color: var(--text-muted); font-size: 12px; }
    .dept-name { font-weight: 600; color: var(--text-dark); }
    
    .status-badge { font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 3px; background: #e3fcef; color: #006644; }

    .delete-link { color: var(--danger); text-decoration: none; font-size: 12px; font-weight: 700; }
    .delete-link:hover { text-decoration: underline; }

  </style>
</head>
<body>

<aside>
  <div class="brand">
    <div style="font-weight: 700; font-size: 18px; letter-spacing: -0.5px;">MEDCORE <span style="font-weight: 300; font-size: 11px; opacity: 0.8; display: block;">INFRASTRUCTURE</span></div>
  </div>
  <nav class="nav-menu">
    <a href="index.php" class="nav-item">Dashboard Overview</a>
    <a href="patients.php" class="nav-item">Patient Registry</a>
    <a href="doctors.php" class="nav-item">Medical Staff</a>
    <a href="departments.php" class="nav-item active">Infrastructure</a>
    <a href="appointments.php" class="nav-item">Central Scheduling</a>
    <a href="lab_reports.php" class="nav-item">Lab Diagnostics</a>
  </nav>
</aside>

<main>
  <div class="top-bar">
    <div style="font-size: 13px; font-weight: 600; color: var(--text-muted);">System Node: Active</div>
  </div>

  <div class="content-area">
    <div class="page-header">
      <h1>Departmental Infrastructure</h1>
    </div>

    <div class="panel">
      <div class="panel-header"><h2>Register New Clinical Unit</h2></div>
      <form method="post" class="form-inline">
        <input type="text" name="name" class="input-control" placeholder="Enter Unit Name (e.g., Radiology, Oncology)" required>
        <button type="submit" name="add_department" class="btn-primary">AUTHORIZE UNIT</button>
      </form>
    </div>

    <div class="panel">
      <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Active Departments</h2>
        <?php $total = $conn->query('SELECT COUNT(*) AS c FROM departments')->fetch_assoc()['c']; ?>
        <span style="font-size:11px; font-weight:700; color:var(--med-blue);"><?php echo $total; ?> TOTAL NODES</span>
      </div>
      <table class="dept-table">
        <thead>
          <tr>
            <th style="width: 80px;">UID</th>
            <th>Clinical Unit Name</th>
            <th>Operational Status</th>
            <th style="text-align:right;">Management</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $res = $conn->query('SELECT * FROM departments ORDER BY name ASC');
            if ($res->num_rows > 0):
              while ($row = $res->fetch_assoc()):
          ?>
            <tr>
              <td class="dept-id">#<?php printf('%03d', $row['id']); ?></td>
              <td class="dept-name"><?php echo htmlspecialchars($row['name']); ?></td>
              <td><span class="status-badge">Functional</span></td>
              <td style="text-align:right;">
                <a href="departments.php?delete=<?php echo $row['id']; ?>" 
                   onclick="return confirm('Decommission this unit? This may affect linked staff records.');" 
                   class="delete-link">DECOMMISSION</a>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr>
              <td colspan="4" style="text-align:center; padding: 40px; color: var(--text-muted);">
                No infrastructure nodes detected. Register a unit to begin.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

</body>
</html>