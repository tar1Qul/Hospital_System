<?php 
include 'db.php'; 

// 1. INITIALIZE VARIABLES (This fixes the "Undefined variable" warnings)
$editData = null;
$msg = "";

// 2. DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM appointments WHERE id=$id");
    header("Location: appointments.php");
    exit;
}

// 3. GET DATA FOR EDITING
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM appointments WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $editData = $res->fetch_assoc();
    }
}

// 4. ADD OR UPDATE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = intval($_POST['patient_id']);
    $doctor_id  = intval($_POST['doctor_id']);
    $dt         = $_POST['appointment_datetime'];
    $reason     = $_POST['reason'] ?? '';
    $appt_id    = isset($_POST['id']) ? intval($_POST['id']) : null;

    // Check for double-booking
    $check_sql = "SELECT COUNT(*) AS c FROM appointments WHERE doctor_id=? AND appointment_datetime=?";
    if ($appt_id) { $check_sql .= " AND id<>$appt_id"; }

    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("is", $doctor_id, $dt);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res['c'] > 0) {
        $msg = "Selected doctor already has an appointment at that datetime.";
    } else {
        if ($appt_id) {
            $stmt = $conn->prepare("UPDATE appointments SET patient_id=?, doctor_id=?, appointment_datetime=?, reason=? WHERE id=?");
            $stmt->bind_param("iissi", $patient_id, $doctor_id, $dt, $reason, $appt_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_datetime, reason) VALUES (?,?,?,?)");
            $stmt->bind_param("iiss", $patient_id, $doctor_id, $dt, $reason);
        }
        $stmt->execute();
        header("Location: appointments.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clinical Scheduling | MedCore</title>
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
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--clinical-bg);
      color: var(--text-main);
      line-height: 1.5;
    }

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

    .brand { font-weight: 700; font-size: 18px; color: white; text-decoration: none; }
    .brand span { font-weight: 300; opacity: 0.7; font-size: 12px; }

    .container { max-width: 1400px; margin: 0 auto; padding: 32px; }
    .grid-layout { display: grid; grid-template-columns: 380px 1fr; gap: 32px; align-items: start; }

    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 4px; }
    .panel-header { padding: 20px 24px; border-bottom: 1px solid var(--border); background: #fafbfc; }
    .panel-header h2 { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

    .form-body { padding: 24px; }
    .field-group { margin-bottom: 20px; }
    .field-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }

    .input-control { width: 100%; padding: 10px 12px; border: 2px solid var(--border); border-radius: 4px; font-size: 14px; outline: none; }
    .input-control:focus { border-color: var(--med-blue); }

    .btn-submit { width: 100%; background: var(--med-blue); color: white; border: none; padding: 12px; border-radius: 4px; font-weight: 600; cursor: pointer; }
    
    .registry-table { width: 100%; border-collapse: collapse; background: var(--surface); }
    .registry-table th { background: #fafbfc; color: var(--text-muted); text-transform: uppercase; font-size: 11px; padding: 12px 20px; border-bottom: 2px solid var(--border); text-align: left; }
    .registry-table td { padding: 16px 20px; border-bottom: 1px solid var(--border); font-size: 14px; }
    .timestamp { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: var(--med-blue); }

    .badge { padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 700; text-transform: uppercase; display: inline-block; }
    .badge-confirmed { background: #e3fcef; color: #006644; }
    .badge-past { background: #fffae6; color: #826a1c; }

    .action-link { text-decoration: none; font-size: 12px; font-weight: 700; margin-left: 10px; }
    .edit-link { color: var(--med-blue); }
    .delete-link { color: var(--danger); }

    .alert-error { background: #ffebe6; color: #bf2600; padding: 12px; border-radius: 4px; font-size: 13px; margin-bottom: 20px; border-left: 4px solid var(--danger); }

    @media (max-width: 1000px) { .grid-layout { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<nav class="top-bar">
  <a href="index.php" class="brand">MEDCORE <span>ADMINISTRATION</span></a>
</nav>

<div class="container">
  <div class="grid-layout">
    
    <div class="panel">
      <div class="panel-header">
        <h2><?php echo $editData ? 'Modify Appointment' : 'New Registration'; ?></h2>
      </div>
      <div class="form-body">
        <?php if (!empty($msg)): ?>
          <div class="alert-error"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <form method="post">
          <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
          <?php endif; ?>

          <div class="field-group">
            <label>Patient</label>
            <select name="patient_id" class="input-control" required>
              <option value="">Select Record</option>
              <?php
                $ps = $conn->query("SELECT id, name FROM patients ORDER BY name");
                while ($p = $ps->fetch_assoc()):
                  $selected = ($editData && $editData['patient_id'] == $p['id']) ? 'selected' : '';
              ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="field-group">
            <label>Physician</label>
            <select name="doctor_id" class="input-control" required>
              <option value="">Select Staff</option>
              <?php
                $ds = $conn->query("SELECT id, name FROM doctors ORDER BY name");
                while ($d = $ds->fetch_assoc()):
                  $selected = ($editData && $editData['doctor_id'] == $d['id']) ? 'selected' : '';
              ?>
                <option value="<?php echo $d['id']; ?>" <?php echo $selected; ?>>Dr. <?php echo htmlspecialchars($d['name']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="field-group">
            <label>Schedule</label>
            <input type="datetime-local" name="appointment_datetime" class="input-control" required
              value="<?php echo ($editData) ? date('Y-m-d\TH:i', strtotime($editData['appointment_datetime'])) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Clinical Notes</label>
            <input type="text" name="reason" class="input-control" placeholder="Reason for visit"
              value="<?php echo ($editData) ? htmlspecialchars($editData['reason']) : ''; ?>">
          </div>

          <button type="submit" class="btn-submit">
            <?php echo $editData ? 'UPDATE RECORD' : 'CONFIRM APPOINTMENT'; ?>
          </button>
          
          <?php if ($editData): ?>
            <a href="appointments.php" style="display:block; text-align:center; margin-top:15px; font-size:12px; color:var(--text-muted); text-decoration:none;">Cancel Edit</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header" style="display:flex; justify-content:space-between;">
        <h2>Registry Archive</h2>
        <span style="font-size:11px; color:var(--success); font-weight:700;">● SYSTEM LIVE</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="registry-table">
          <thead>
            <tr>
              <th>Patient / Staff</th>
              <th>Schedule</th>
              <th>Status</th>
              <th style="text-align:right">Management</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $res = $conn->query("SELECT a.*, p.name AS patient_name, d.name AS doctor_name
                                   FROM appointments a
                                   JOIN patients p ON a.patient_id=p.id
                                   JOIN doctors d ON a.doctor_id=d.id
                                   ORDER BY a.appointment_datetime DESC");

              while ($r = $res->fetch_assoc()):
                $isPast = strtotime($r['appointment_datetime']) < time();
            ?>
              <tr>
                <td>
                  <div style="font-weight:700;"><?php echo htmlspecialchars($r['patient_name']); ?></div>
                  <div style="font-size:12px; color:var(--text-muted);">Dr. <?php echo htmlspecialchars($r['doctor_name']); ?></div>
                </td>
                <td>
                  <div class="timestamp"><?php echo date('Y.m.d | H:i', strtotime($r['appointment_datetime'])); ?></div>
                </td>
                <td>
                  <span class="badge <?php echo $isPast ? 'badge-past' : 'badge-confirmed'; ?>">
                    <?php echo $isPast ? 'COMPLETED' : 'CONFIRMED'; ?>
                  </span>
                </td>
                <td style="text-align:right">
                  <a href="appointments.php?edit=<?php echo $r['id']; ?>" class="action-link edit-link">EDIT</a>
                  <a href="appointments.php?delete=<?php echo $r['id']; ?>" onclick="return confirm('Delete record?');" class="action-link delete-link">DELETE</a>
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