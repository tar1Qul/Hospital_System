<?php 
include 'db.php'; 

// 1. INITIALIZE VARIABLES
$editData = null;
$msg = "";

// 2. DELETE REPORT
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM lab_reports WHERE id=$id");
    header("Location: lab_reports.php");
    exit;
}

// 3. GET DATA FOR EDIT
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM lab_reports WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $editData = $res->fetch_assoc();
    }
}

// 4. ADD OR UPDATE REPORT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id      = intval($_POST['patient_id']);
    $test_name       = $_POST['test_name'] ?? '';
    $result_value    = $_POST['result_value'] ?? '';
    $reference_range = $_POST['reference_range'] ?? '';
    $status          = $_POST['status'] ?? 'Normal';
    $report_id       = isset($_POST['id']) ? intval($_POST['id']) : null;

    if ($report_id) {
        $stmt = $conn->prepare("UPDATE lab_reports SET patient_id=?, test_name=?, result_value=?, reference_range=?, status=? WHERE id=?");
        $stmt->bind_param("issssi", $patient_id, $test_name, $result_value, $reference_range, $status, $report_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO lab_reports (patient_id, test_name, result_value, reference_range, status) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $patient_id, $test_name, $result_value, $reference_range, $status);
    }
    $stmt->execute();
    header("Location: lab_reports.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laboratory Diagnostics | MedCore</title>
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
      --lab-purple: #5243aa;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--clinical-bg);
      color: var(--text-main);
      line-height: 1.5;
    }

    /* --- Navigation --- */
    nav.top-bar {
      height: 64px;
      background: var(--med-blue-dark);
      color: white;
      display: flex;
      align-items: center;
      padding: 0 40px;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .brand { font-weight: 700; font-size: 18px; color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; }
    .brand span { font-weight: 300; opacity: 0.7; font-size: 12px; text-transform: uppercase; }

    /* --- Layout --- */
    .container { max-width: 1400px; margin: 0 auto; padding: 32px; }
    .grid-layout { display: grid; grid-template-columns: 380px 1fr; gap: 32px; align-items: start; }

    /* --- Panel UI --- */
    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 4px; }
    .panel-header { padding: 20px 24px; border-bottom: 1px solid var(--border); background: #fafbfc; display: flex; justify-content: space-between; align-items: center; }
    .panel-header h2 { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

    .form-body { padding: 24px; }
    .field-group { margin-bottom: 18px; }
    .field-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }

    .input-control { width: 100%; padding: 10px 12px; border: 2px solid var(--border); border-radius: 4px; font-size: 14px; outline: none; }
    .input-control:focus { border-color: var(--med-blue); }

    .btn-primary { width: 100%; background: var(--lab-purple); color: white; border: none; padding: 12px; border-radius: 4px; font-weight: 600; cursor: pointer; text-transform: uppercase; font-size: 12px; }

    /* --- Data Table --- */
    .lab-table { width: 100%; border-collapse: collapse; background: var(--surface); }
    .lab-table th { background: #fafbfc; color: var(--text-muted); text-transform: uppercase; font-size: 11px; padding: 12px 20px; border-bottom: 2px solid var(--border); text-align: left; }
    .lab-table td { padding: 16px 20px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 14px; }

    .test-name { font-weight: 700; color: var(--text-dark); }
    .result-value { font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 15px; }
    
    .status-badge { padding: 2px 8px; border-radius: 3px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .status-normal { background: #e3fcef; color: #006644; }
    .status-critical { background: #ffebe6; color: #bf2600; animation: blink 1.5s infinite; }

    @keyframes blink { 50% { opacity: 0.6; } }

    .action-links a { text-decoration: none; font-size: 12px; font-weight: 700; margin-left: 12px; }
    .edit-link { color: var(--med-blue); }
    .remove-link { color: var(--danger); }

    @media (max-width: 1000px) { .grid-layout { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<nav class="top-bar">
  <a href="index.php" class="brand">MEDCORE <span>Lab Diagnostics</span></a>
</nav>

<div class="container">
  <div class="grid-layout">
    
    <div class="panel">
      <div class="panel-header">
        <h2><?php echo $editData ? 'Modify Report' : 'New Lab Entry'; ?></h2>
      </div>
      <div class="form-body">
        <form method="post">
          <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
          <?php endif; ?>

          <div class="field-group">
            <label>Subject / Patient</label>
            <select name="patient_id" class="input-control" required>
              <option value="">Select Patient...</option>
              <?php
                $ps = $conn->query("SELECT id, name FROM patients ORDER BY name");
                while ($p = $ps->fetch_assoc()):
                  $sel = ($editData && $editData['patient_id']==$p['id']) ? 'selected' : '';
              ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="field-group">
            <label>Test Name</label>
            <input type="text" name="test_name" class="input-control" placeholder="e.g. Hemoglobin, Glucose (Fasting)" required
              value="<?php echo $editData ? htmlspecialchars($editData['test_name']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Observed Result</label>
            <input type="text" name="result_value" class="input-control" placeholder="e.g. 14.2 g/dL" required
              value="<?php echo $editData ? htmlspecialchars($editData['result_value']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Reference Range</label>
            <input type="text" name="reference_range" class="input-control" placeholder="e.g. 13.5 - 17.5 g/dL"
              value="<?php echo $editData ? htmlspecialchars($editData['reference_range']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Clinical Status</label>
            <select name="status" class="input-control">
              <option value="Normal" <?php echo ($editData && $editData['status']=='Normal') ? 'selected' : ''; ?>>Normal</option>
              <option value="Critical" <?php echo ($editData && $editData['status']=='Critical') ? 'selected' : ''; ?>>Critical Flag</option>
            </select>
          </div>

          <button type="submit" class="btn-primary">
            <?php echo $editData ? 'Commit Lab Update' : 'Authorize Lab Report'; ?>
          </button>

          <?php if ($editData): ?>
            <a href="lab_reports.php" style="display:block; text-align:center; margin-top:15px; font-size:12px; color:var(--text-muted); text-decoration:none;">Abort Entry</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2>Laboratory Registry</h2>
        <span style="font-size:11px; color:var(--lab-purple); font-weight:700;">DIAGNOSTIC ARCHIVE</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="lab-table">
          <thead>
            <tr>
              <th>Patient</th>
              <th>Test Description</th>
              <th>Result</th>
              <th>Ref. Range</th>
              <th>Flag</th>
              <th style="text-align:right">Management</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $res = $conn->query("
                SELECT lr.*, p.name AS patient_name 
                FROM lab_reports lr
                JOIN patients p ON lr.patient_id = p.id
                ORDER BY lr.reported_at DESC
              ");

              while ($row = $res->fetch_assoc()):
            ?>
              <tr>
                <td>
                  <div style="font-weight:700;"><?php echo htmlspecialchars($row['patient_name']); ?></div>
                  <div style="font-size:11px; color:var(--text-muted);"><?php echo date('Y.m.d', strtotime($row['reported_at'])); ?></div>
                </td>
                <td><span class="test-name"><?php echo htmlspecialchars($row['test_name']); ?></span></td>
                <td><span class="result-value"><?php echo htmlspecialchars($row['result_value']); ?></span></td>
                <td style="color:var(--text-muted); font-size:12px;"><?php echo htmlspecialchars($row['reference_range']); ?></td>
                <td>
                  <span class="status-badge <?php echo ($row['status'] == 'Critical') ? 'status-critical' : 'status-normal'; ?>">
                    <?php echo $row['status']; ?>
                  </span>
                </td>
                <td style="text-align:right">
                  <div class="action-links">
                    <a href="lab_reports.php?edit=<?php echo $row['id']; ?>" class="edit-link">MODIFY</a>
                    <a href="lab_reports.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Confirm permanent deletion?');" class="remove-link">DELETE</a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

</body>
</html>