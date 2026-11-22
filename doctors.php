<?php 
include 'db.php'; 

// 1. INITIALIZE VARIABLES
$editData = null;
$msg = "";

// 2. DELETE DOCTOR
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM doctors WHERE id = $id");
    header("Location: doctors.php");
    exit;
}

// 3. GET DATA FOR EDITING
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM doctors WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $editData = $res->fetch_assoc();
    }
}

// 4. ADD OR UPDATE DOCTOR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = $_POST['name']         ?? '';
    $dept         = $_POST['department_id'] ?? null;
    $phone        = $_POST['phone']        ?? '';
    $email        = $_POST['email']        ?? '';
    $availability = $_POST['availability'] ?? '';
    $doc_id       = isset($_POST['id']) ? intval($_POST['id']) : null;

    if ($doc_id) {
        $stmt = $conn->prepare("UPDATE doctors SET name=?, department_id=?, phone=?, email=?, availability=? WHERE id=?");
        $stmt->bind_param("sisssi", $name, $dept, $phone, $email, $availability, $doc_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO doctors (name,department_id,phone,email,availability) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sisss", $name, $dept, $phone, $email, $availability);
    }
    $stmt->execute();
    header("Location: doctors.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctors Directory | MedCore</title>
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
      --dept-tag: #deebff;
      --dept-text: #0747a6;
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

    .btn-primary { width: 100%; background: var(--med-blue); color: white; border: none; padding: 12px; border-radius: 4px; font-weight: 600; cursor: pointer; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
    .btn-primary:hover { background: var(--med-blue-dark); }

    /* --- Directory Table --- */
    .staff-table { width: 100%; border-collapse: collapse; background: var(--surface); }
    .staff-table th { background: #fafbfc; color: var(--text-muted); text-transform: uppercase; font-size: 11px; padding: 12px 20px; border-bottom: 2px solid var(--border); text-align: left; }
    .staff-table td { padding: 16px 20px; border-bottom: 1px solid var(--border); vertical-align: middle; }

    .staff-profile { display: flex; align-items: center; gap: 12px; }
    .staff-avatar { width: 40px; height: 40px; background: var(--clinical-bg); border: 1px solid var(--border); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--med-blue); }
    
    .staff-name { font-weight: 700; font-size: 14px; color: var(--text-dark); }
    .dept-tag { display: inline-block; font-size: 10px; font-weight: 700; background: var(--dept-tag); color: var(--dept-text); padding: 2px 8px; border-radius: 4px; margin-top: 4px; text-transform: uppercase; }

    .contact-info { font-size: 13px; color: var(--text-main); }
    .contact-sub { font-size: 12px; color: var(--text-muted); }

    .availability-box { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--success); background: #e3fcef; padding: 4px 8px; border-radius: 4px; display: inline-block; }

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
  <a href="index.php" class="brand">MEDCORE <span>Doctors Directory</span></a>
</nav>

<div class="container">
  <div class="grid-layout">
    
    <div class="panel">
      <div class="panel-header">
        <h2><?php echo $editData ? 'Update Credentials' : 'Staff Registration'; ?></h2>
      </div>
      <div class="form-body">
        <form method="post">
          <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
          <?php endif; ?>

          <div class="field-group">
            <label>Full Name & Title</label>
            <input type="text" name="name" class="input-control" placeholder="e.g. Dr. Shoayeb Mizi" required
              value="<?php echo $editData ? htmlspecialchars($editData['name']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Department Unit</label>
            <select name="department_id" class="input-control" required>
              <option value="">Select Unit...</option>
              <?php
                $deps = $conn->query("SELECT * FROM departments ORDER BY name");
                while ($d = $deps->fetch_assoc()):
                  $sel = ($editData && $editData['department_id']==$d['id']) ? 'selected' : '';
              ?>
                <option value="<?php echo $d['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($d['name']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="field-group">
            <label>Communication (Phone)</label>
            <input type="text" name="phone" class="input-control" placeholder="+880..."
              value="<?php echo $editData ? htmlspecialchars($editData['phone']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Clinical Email</label>
            <input type="email" name="email" class="input-control" placeholder="staff@medcore.com"
              value="<?php echo $editData ? htmlspecialchars($editData['email']) : ''; ?>">
          </div>

          <div class="field-group">
            <label>Duty Hours / Availability</label>
            <input type="text" name="availability" class="input-control" placeholder="e.g. 09:00 - 17:00 (MTWTF)"
              value="<?php echo $editData ? htmlspecialchars($editData['availability']) : ''; ?>">
          </div>

          <button type="submit" class="btn-primary">
            <?php echo $editData ? 'Commit Update' : 'Authorize Staff'; ?>
          </button>

          <?php if ($editData): ?>
            <a href="doctors.php" style="display:block; text-align:center; margin-top:15px; font-size:12px; color:var(--text-muted); text-decoration:none;">Abort Entry</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2>Active Medical Staff</h2>
        <span style="font-size:11px; color:var(--success); font-weight:700;">REGISTRY VERIFIED</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="staff-table">
          <thead>
            <tr>
              <th>Practitioner</th>
              <th>Contact Details</th>
              <th>Duty Cycle</th>
              <th style="text-align:right">Management</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $res = $conn->query("
                SELECT doctors.*, departments.name AS dept_name
                FROM doctors
                LEFT JOIN departments ON doctors.department_id = departments.id
                ORDER BY doctors.id DESC
              ");

              while ($row = $res->fetch_assoc()):
                $initial = strtoupper(substr(trim($row['name']), 0, 1));
            ?>
              <tr>
                <td>
                  <div class="staff-profile">
                    <div class="staff-avatar"><?php echo $initial; ?></div>
                    <div>
                      <div class="staff-name"><?php echo htmlspecialchars($row['name']); ?></div>
                      <div class="dept-tag"><?php echo htmlspecialchars($row['dept_name'] ?? 'General'); ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="contact-info"><?php echo htmlspecialchars($row['email'] ?: 'No Email'); ?></div>
                  <div class="contact-sub"><?php echo htmlspecialchars($row['phone'] ?: 'No Phone'); ?></div>
                </td>
                <td>
                  <?php if ($row['availability']): ?>
                    <span class="availability-box"><?php echo htmlspecialchars($row['availability']); ?></span>
                  <?php else: ?>
                    <span style="color:var(--text-muted); font-size:12px;">Not Defined</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="action-links">
                    <a href="doctors.php?edit=<?php echo $row['id']; ?>" class="edit-link">MODIFY</a>
                    <a href="doctors.php?delete=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Confirm permanent removal?');" 
                       class="remove-link">REMOVE</a>
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