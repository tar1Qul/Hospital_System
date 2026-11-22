<?php 
include 'db.php'; 

// 1. INITIALIZE VARIABLES (Prevents Undefined Variable Warnings)
$editData = null;
$msg = "";

// 2. DELETE PATIENT
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM patients WHERE id=$id");
    header("Location: patients.php");
    exit;
}

// 3. GET DATA FOR EDIT
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM patients WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $editData = $res->fetch_assoc();
    }
}

// 4. ADD OR UPDATE PATIENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name']    ?? '';
    $dob     = $_POST['dob']     ?? null;
    $phone   = $_POST['phone']   ?? '';
    $email   = $_POST['email']   ?? '';
    $address = $_POST['address'] ?? '';
    $pat_id  = isset($_POST['id']) ? intval($_POST['id']) : null;

    if ($pat_id) {
        $stmt = $conn->prepare("UPDATE patients SET name=?, dob=?, phone=?, email=?, address=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $dob, $phone, $email, $address, $pat_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO patients (name,dob,phone,email,address) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $dob, $phone, $email, $address);
    }
    $stmt->execute();
    header("Location: patients.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Registry | MedCore</title>
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

    .input-control { width: 100%; padding: 10px 12px; border: 2px solid var(--border); border-radius: 4px; font-size: 14px; transition: border-color 0.2s; outline: none; }
    .input-control:focus { border-color: var(--med-blue); }

    .btn-primary { width: 100%; background: var(--med-blue); color: white; border: none; padding: 12px; border-radius: 4px; font-weight: 600; cursor: pointer; text-transform: uppercase; font-size: 12px; }
    .btn-primary:hover { background: var(--med-blue-dark); }

    /* --- Patient Table --- */
    .patient-table { width: 100%; border-collapse: collapse; background: var(--surface); }
    .patient-table th { background: #fafbfc; color: var(--text-muted); text-transform: uppercase; font-size: 11px; padding: 12px 20px; border-bottom: 2px solid var(--border); text-align: left; }
    .patient-table td { padding: 16px 20px; border-bottom: 1px solid var(--border); vertical-align: middle; }

    .patient-profile { display: flex; align-items: center; gap: 12px; }
    .patient-avatar { width: 40px; height: 40px; background: #ebecf0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--text-main); font-size: 14px; }
    
    .patient-name { font-weight: 700; font-size: 14px; color: var(--text-dark); }
    .age-tag { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--med-blue); font-weight: 600; }

    .contact-info { font-size: 13px; }
    .address-cell { font-size: 12px; color: var(--text-muted); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* --- Actions --- */
    .action-links { display: flex; gap: 12px; justify-content: flex-end; }
    .action-links a { text-decoration: none; font-size: 12px; font-weight: 700; }
    .edit-link { color: var(--med-blue); }
    .remove-link { color: var(--danger); }

    @media (max-width: 1000px) { .grid-layout { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<nav class="top-bar">
  <a href="index.php" class="brand">MEDCORE <span>Patient Registry</span></a>
</nav>

<div class="container">
  <div class="grid-layout">
    
    <div class="panel">
      <div class="panel-header">
        <h2><?php echo $editData ? 'Update Record' : 'New Admission'; ?></h2>
      </div>
      <div class="form-body">
        <form method="post">
          <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
          <?php endif; ?>

          <div class="field-group">
            <label>Full Legal Name</label>
            <input type="text" name="name" class="input-control" placeholder="Last Name, First Name" required
              value="<?php echo $editData ? htmlspecialchars($editData['name']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Date of Birth</label>
            <input type="date" name="dob" class="input-control" required
              value="<?php echo $editData ? $editData['dob'] : ''; ?>">
          </div>

          <div class="field-group">
            <label>Contact Phone</label>
            <input type="text" name="phone" class="input-control" placeholder="+880..."
              value="<?php echo $editData ? htmlspecialchars($editData['phone']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Email Address</label>
            <input type="email" name="email" class="input-control" placeholder="patient@example.com"
              value="<?php echo $editData ? htmlspecialchars($editData['email']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Residential Address</label>
            <textarea name="address" class="input-control" rows="2" style="resize:none;"><?php echo $editData ? htmlspecialchars($editData['address']) : ''; ?></textarea>
          </div>

          <button type="submit" class="btn-primary">
            <?php echo $editData ? 'Commit Record Update' : 'Register'; ?>
          </button>

          <?php if ($editData): ?>
            <a href="patients.php" style="display:block; text-align:center; margin-top:15px; font-size:12px; color:var(--text-muted); text-decoration:none;">Cancel modification</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2>Electronic Health Records</h2>
        <span style="font-size:11px; color:var(--med-blue); font-weight:700;">ACTIVE DATABASE</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="patient-table">
          <thead>
            <tr>
              <th>Patient Identity</th>
              <th>Contact</th>
              <th>Location</th>
              <th style="text-align:right">Management</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $res = $conn->query("SELECT * FROM patients ORDER BY id DESC");
              while ($row = $res->fetch_assoc()):
                $initial = strtoupper(substr(trim($row['name']), 0, 1));
                $age = $row['dob'] ? (date_diff(date_create($row['dob']), date_create('today'))->y) . 'Y' : 'N/A';
            ?>
              <tr>
                <td>
                  <div class="patient-profile">
                    <div class="patient-avatar"><?php echo $initial; ?></div>
                    <div>
                      <div class="patient-name"><?php echo htmlspecialchars($row['name']); ?></div>
                      <div class="age-tag">DOB: <?php echo $row['dob']; ?> | AGE: <?php echo $age; ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="contact-info"><?php echo htmlspecialchars($row['phone'] ?: 'No Phone'); ?></div>
                  <div style="font-size:11px; color:var(--text-muted);"><?php echo htmlspecialchars($row['email'] ?: 'No Email'); ?></div>
                </td>
                <td>
                  <div class="address-cell" title="<?php echo htmlspecialchars($row['address']); ?>">
                    <?php echo $row['address'] ? htmlspecialchars($row['address']) : '—'; ?>
                  </div>
                </td>
                <td>
                  <div class="action-links">
                    <a href="patients.php?edit=<?php echo $row['id']; ?>" class="edit-link">MODIFY</a>
                    <a href="patients.php?delete=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Archive/Delete this clinical record?');" 
                       class="remove-link">DELETE</a>
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