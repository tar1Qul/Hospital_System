<?php 
include 'db.php'; 

// 1. INITIALIZE VARIABLES
$editData = null;
$msg = "";

// -------------------- DELETE PRESCRIPTION --------------------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM prescriptions WHERE id=$id");
    header("Location: prescriptions.php");
    exit;
}

// -------------------- EDIT MODE --------------------
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM prescriptions WHERE id=$id");
    if ($result && $result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    }
}

// -------------------- UPDATE PRESCRIPTION --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_prescription'])) {
    $id       = intval($_POST['id']);
    $medicines = $_POST['medicines'];
    $notes     = $_POST['notes'];
    $stmt = $conn->prepare("UPDATE prescriptions SET medicines=?, notes=? WHERE id=?");
    $stmt->bind_param("ssi", $medicines, $notes, $id);
    $stmt->execute();
    header("Location: prescriptions.php");
    exit;
}

// -------------------- ADD NEW PRESCRIPTION --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_prescription'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $medicines      = $_POST['medicines'] ?? '';
    $notes          = $_POST['notes']      ?? '';
    $stmt = $conn->prepare("INSERT INTO prescriptions (appointment_id, medicines, notes) VALUES (?,?,?)");
    $stmt->bind_param("iss", $appointment_id, $medicines, $notes);
    $stmt->execute();
    header('Location: prescriptions.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clinical Prescriptions | MedCore</title>
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
      --danger: #ff5630;
      --rx-accent: #8b3a52;
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
    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 4px; margin-bottom: 24px; }
    .panel-header { padding: 20px 24px; border-bottom: 1px solid var(--border); background: #fafbfc; display: flex; justify-content: space-between; align-items: center; }
    .panel-header h2 { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

    .form-body { padding: 24px; }
    .field-group { margin-bottom: 20px; }
    .field-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }

    .input-control { width: 100%; padding: 10px 12px; border: 2px solid var(--border); border-radius: 4px; font-size: 14px; outline: none; font-family: inherit; }
    .input-control:focus { border-color: var(--med-blue); }

    .btn-submit { width: 100%; background: var(--med-blue); color: white; border: none; padding: 12px; border-radius: 4px; font-weight: 600; cursor: pointer; text-transform: uppercase; font-size: 12px; }

    /* --- Rx Feed Cards --- */
    .rx-card { background: var(--surface); border: 1px solid var(--border); border-radius: 4px; margin-bottom: 16px; border-left: 4px solid var(--rx-accent); }
    .rx-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: flex-start; }
    
    .rx-title { font-weight: 700; font-size: 16px; color: var(--text-main); }
    .rx-subtitle { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    
    .rx-body { padding: 20px; }
    .meds-list { background: #f9f9fb; border: 1px solid var(--border); padding: 15px; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 13px; white-space: pre-wrap; color: #172b4d; line-height: 1.6; }
    
    .clinical-notes { margin-top: 15px; padding-left: 12px; border-left: 2px solid #ebecf0; font-size: 13px; color: var(--text-muted); font-style: italic; }

    .rx-footer { padding: 12px 20px; background: #fafbfc; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .rx-meta { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }

    /* --- Action Links --- */
    .action-links a { text-decoration: none; font-size: 12px; font-weight: 700; margin-left: 15px; }
    .edit-link { color: var(--med-blue); }
    .delete-link { color: var(--danger); }

    @media (max-width: 1000px) { .grid-layout { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<nav class="top-bar">
  <a href="index.php" class="brand">MEDCORE <span>Pharmacy Records</span></a>
</nav>

<div class="container">
  <div class="grid-layout">
    
    <div class="panel">
      <div class="panel-header">
        <h2><?php echo $editData ? 'Update Prescription' : 'New Rx Issuance'; ?></h2>
      </div>
      <div class="form-body">
        <form method="post">
          <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
          <?php else: ?>
            <div class="field-group">
              <label>Ref. Appointment ID</label>
              <select name="appointment_id" class="input-control" required>
                <option value="">Link to Session...</option>
                <?php
                  $aps = $conn->query("
                    SELECT a.id, p.name AS patient_name, d.name AS doctor_name, a.appointment_datetime
                    FROM appointments a
                    JOIN patients p ON a.patient_id=p.id
                    JOIN doctors d ON a.doctor_id=d.id
                    ORDER BY a.appointment_datetime DESC
                  ");
                  while ($a = $aps->fetch_assoc()):
                ?>
                  <option value="<?php echo $a['id']; ?>">
                    #<?php echo $a['id']; ?> | <?php echo htmlspecialchars($a['patient_name']); ?> (Dr. <?php echo htmlspecialchars($a['doctor_name']); ?>)
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          <?php endif; ?>

          <div class="field-group">
            <label>Medications & Dosage</label>
            <textarea name="medicines" class="input-control" rows="6" placeholder="Medicine Name | Strength | Frequency" required><?php echo $editData ? htmlspecialchars($editData['medicines']) : ''; ?></textarea>
          </div>

          <div class="field-group">
            <label>Clinical Guidance / Notes</label>
            <textarea name="notes" class="input-control" rows="3" placeholder="Special instructions..."><?php echo $editData ? htmlspecialchars($editData['notes']) : ''; ?></textarea>
          </div>

          <button type="submit" name="<?php echo $editData ? 'update_prescription' : 'add_prescription'; ?>" class="btn-submit">
            <?php echo $editData ? 'Commit Rx Update' : 'Authorize Prescription'; ?>
          </button>
          
          <?php if ($editData): ?>
            <a href="prescriptions.php" style="display:block; text-align:center; margin-top:15px; font-size:12px; color:var(--text-muted); text-decoration:none;">Abort Issuance</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div>
      <div style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
        <h2 style="font-size:14px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Historical Registry</h2>
        <span style="font-size:11px; color:var(--med-blue); font-weight:700;">ENCRYPTED EHR ACCESS</span>
      </div>

      <?php
        $res = $conn->query("
          SELECT pr.*, a.appointment_datetime, p.name AS patient_name, d.name AS doctor_name
          FROM prescriptions pr
          JOIN appointments a ON pr.appointment_id=a.id
          JOIN patients p ON a.patient_id=p.id
          JOIN doctors d ON a.doctor_id=d.id
          ORDER BY pr.issued_on DESC
        ");

        while ($r = $res->fetch_assoc()):
      ?>
        <div class="rx-card">
          <div class="rx-header">
            <div>
              <div class="rx-title"><?php echo htmlspecialchars($r['patient_name']); ?></div>
              <div class="rx-subtitle">Prescribed by Dr. <?php echo htmlspecialchars($r['doctor_name']); ?></div>
            </div>
            <div style="text-align:right">
              <div style="font-family:'JetBrains Mono'; font-size:12px; font-weight:700;">Rx-ID: #<?php echo $r['id']; ?></div>
              <div style="font-size:11px; color:var(--text-muted);"><?php echo date('Y.m.d | H:i', strtotime($r['issued_on'])); ?></div>
            </div>
          </div>
          
          <div class="rx-body">
            <div class="meds-list"><?php echo htmlspecialchars($r['medicines']); ?></div>
            <?php if ($r['notes']): ?>
              <div class="clinical-notes">Note: <?php echo htmlspecialchars($r['notes']); ?></div>
            <?php endif; ?>
          </div>

          <div class="rx-footer">
            <div class="rx-meta">Linked Appointment: #<?php echo $r['appointment_id']; ?></div>
            <div class="action-links">
              <a href="prescriptions.php?edit=<?php echo $r['id']; ?>" class="edit-link">MODIFY</a>
              <a href="prescriptions.php?delete=<?php echo $r['id']; ?>" onclick="return confirm('Permanently delete this medical record?');" class="remove-link">DELETE</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

  </div>
</div>

</body>
</html>