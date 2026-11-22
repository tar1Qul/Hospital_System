<?php
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* SEARCH CODE */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchResults = [];

if($search != ''){

    $safe = $conn->real_escape_string($search);

    $sql = "
        SELECT 
            id,
            name,
            phone,
            email,
            address
        FROM patients
        WHERE 
            name LIKE '%$safe%'
            OR id LIKE '%$safe%'
            OR phone LIKE '%$safe%'
            OR email LIKE '%$safe%'
        ORDER BY id DESC
    ";

    $result = $conn->query($sql);

    while($row = $result->fetch_assoc()){
        $searchResults[] = $row;
    }
}

// Data Fetching
$p = $conn->query('SELECT COUNT(*) AS c FROM patients')->fetch_assoc();
$d = $conn->query('SELECT COUNT(*) AS c FROM doctors')->fetch_assoc();
$a = $conn->query('SELECT COUNT(*) AS c FROM appointments WHERE appointment_datetime >= NOW()')->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MedCore | Clinical Administration</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
  
  <style>
    :root {
      /* Clinical Blue Palette */
      --med-blue: #0052cc;
      --med-blue-dark: #0747a6;
      --med-teal: #00b8d9;
      --clinical-bg: #f4f5f7;
      --surface: #ffffff;
      --text-dark: #172b4d;
      --text-light: #6b778c;
      --border: #dfe1e6;
      --success: #36b37e;
      --sidebar-width: 260px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--clinical-bg);
      color: var(--text-dark);
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
    }

    .brand {
      padding: 30px 24px;
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(0,0,0,0.1);
    }

    .brand-logo {
      width: 32px;
      height: 32px;
      background: white;
      border-radius: 6px;
      display: grid; place-items: center;
      color: var(--med-blue);
      font-weight: 800;
    }

    .nav-menu {
      padding: 20px 12px;
      flex: 1;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      color: rgba(255,255,255,0.7);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      border-radius: 6px;
      margin-bottom: 4px;
      transition: all 0.2s;
    }

    .nav-item:hover, .nav-item.active {
      background: rgba(255,255,255,0.1);
      color: white;
    }

    /* --- Main Content --- */
    main {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
    }

    .top-bar {
      height: 64px;
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      position: sticky;
      top: 0;
      z-index: 90;
    }

    .search-input {
      background: #ebecf0;
      border: 1px solid var(--border);
      padding: 8px 16px;
      border-radius: 4px;
      width: 300px;
      font-size: 13px;
    }

    .content-area {
      padding: 32px;
      max-width: 1400px;
    }

    /* --- Clinical Header --- */
    .page-header {
      margin-bottom: 24px;
    }

    .page-header h1 {
      font-size: 24px;
      font-weight: 700;
      color: var(--text-dark);
    }

    .status-tag {
      font-size: 11px;
      text-transform: uppercase;
      background: var(--success);
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-weight: 700;
    }

    /* --- KPI Grid --- */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }

    .kpi-card {
      background: var(--surface);
      padding: 20px;
      border-radius: 4px;
      border-bottom: 3px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .kpi-card.blue { border-bottom-color: var(--med-blue); }
    .kpi-card.teal { border-bottom-color: var(--med-teal); }
    .kpi-card.green { border-bottom-color: var(--success); }

    .kpi-value {
      font-family: 'JetBrains Mono', monospace;
      font-size: 28px;
      font-weight: 700;
      display: block;
    }

    .kpi-label {
      font-size: 12px;
      color: var(--text-light);
      text-transform: uppercase;
      font-weight: 600;
    }

    /* --- Data Tables/Lists --- */
    .section-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 24px;
    }

    .data-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 4px;
    }

    .card-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      font-weight: 700;
      font-size: 14px;
      display: flex;
      justify-content: space-between;
    }

    .clinical-table {
      width: 100%;
      border-collapse: collapse;
    }

    .clinical-table th {
      text-align: left;
      font-size: 12px;
      color: var(--text-light);
      padding: 12px 20px;
      background: #fafbfc;
      text-transform: uppercase;
    }

    .clinical-table td {
      padding: 12px 20px;
      border-bottom: 1px solid #f4f5f7;
      font-size: 14px;
    }

    .btn-primary {
      background: var(--med-blue);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 3px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      font-size: 13px;
    }

    .action-link {
      color: var(--med-blue);
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
    }

    @media (max-width: 900px) {
      .section-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<aside>
  <div class="brand">
    <div class="brand-logo">M</div>
    <div style="font-weight: 700; font-size: 18px; letter-spacing: -0.5px;">MEDCORE <span style="font-weight: 300; font-size: 12px; opacity: 0.8; display: block;">by Team Hunter</span></div>
  </div>
  
  <nav class="nav-menu">
    <a href="index.php" class="nav-item active">Dashboard Overview</a>
    <a href="patients.php" class="nav-item">Patient Registration</a>
    <a href="doctors.php" class="nav-item">Doctors Registration</a>
    <a href="appointments.php" class="nav-item">Central Scheduling</a>
    <a href="prescriptions.php" class="nav-item">Pharmacy & Rx</a>
    <a href="departments.php" class="nav-item">Departments</a>
    <a href="lab_reports.php" class="nav-item">Laboratory Reports</a>
  </nav>

  <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
    <a href="logout.php" class="nav-item">SIGN-OUT</a>
  </div>
</aside>

<main>
  <div class="top-bar">
    <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
      <input 
          type="text"
          name="search"
          class="search-input"
          placeholder="Search Patient Name / ID / Phone"
          value="<?php echo htmlspecialchars($search); ?>">

      <button type="submit" class="btn-primary">
          Search
      </button>
    </form>

    <div style="display: flex; align-items: center; gap: 15px;">
      <div style="text-align: right;">
        <span style="display: block; font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        <span style="font-size: 11px; color: var(--text-light);">System Administrator</span>
      </div>
      <div style="width: 36px; height: 36px; background: #dfe1e6; border-radius: 50%;"></div>
    </div>
  </div>

  <div class="content-area">
<?php
if ($search != '') {
?>

<div class="data-card" style="margin-bottom:25px;">

    <div class="card-header">
        Search Results
    </div>

    <table class="clinical-table">

        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
            </tr>
        </thead>

        <tbody>

        <?php
        if (count($searchResults) > 0) {

            foreach ($searchResults as $row) {
        ?>

            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['address']; ?></td>
            </tr>

        <?php
            }

        } else {
        ?>

            <tr>
                <td colspan="5">No Patient Found</td>
            </tr>

        <?php
        }
        ?>

        </tbody>
    </table>

</div>

<?php
}   // ✅ THIS closes: if ($search != '')
?>
    <div class="page-header">
      <span class="status-tag">System Live</span>
      <h1 style="margin-top: 8px;">Clinical Command Center</h1>
    </div>

    <div class="kpi-grid">
      <div class="kpi-card blue">
        <div>
          <span class="kpi-label">Active Records</span>
          <span class="kpi-value"><?php echo number_format($p['c']); ?></span>
        </div>
        <div style="color: var(--med-blue); opacity: 0.2;">
           <svg width="40" height="40" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
        </div>
      </div>
      <div class="kpi-card teal">
        <div>
          <span class="kpi-label">On-Duty Physicians</span>
          <span class="kpi-value"><?php echo number_format($d['c']); ?></span>
        </div>
        <div style="color: var(--med-teal); opacity: 0.2;">
           <svg width="40" height="40" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zm3 14a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
        </div>
      </div>
      <div class="kpi-card green">
        <div>
          <span class="kpi-label">Pending Consults</span>
          <span class="kpi-value"><?php echo number_format($a['c']); ?></span>
        </div>
        <div style="color: var(--success); opacity: 0.2;">
           <svg width="40" height="40" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
        </div>
      </div>
    </div>

    <div class="section-grid">
      <div class="data-card">
        <div class="card-header">
          Recent Appointments
          <a href="appointments.php" class="action-link">View All Schedule</a>
        </div>
        <table class="clinical-table">
          <thead>
            <tr>
              <th>Time</th>
              <th>Patient</th>
              <th>Physician</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>09:00 AM</td>
              <td style="font-weight: 600;">Hassan Ali</td>
              <td>Dr. Robert Chen</td>
              <td><span style="color: var(--med-blue);">● Confirmed</span></td>
            </tr>
            <tr>
              <td>10:30 AM</td>
              <td style="font-weight: 600;">Sarah Jenkins</td>
              <td>Dr. Emily Blunt</td>
              <td><span style="color: var(--success);">● Checked In</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="data-card">
        <div class="card-header">Quick Actions</div>
        <div style="padding: 20px;">
          <a href="patients.php" class="btn-primary" style="display: block; text-align: center; margin-bottom: 10px;">Register New Patient</a>
          <a href="appointments.php" class="btn-primary" style="display: block; text-align: center; background: var(--surface); color: var(--med-blue); border: 1px solid var(--med-blue);">Schedule Appointment</a>
          
          <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
             <p style="font-size: 12px; color: var(--text-light); margin-bottom: 8px;">SYSTEM ALERTS</p>
             <div style="background: #fff0f0; border-left: 4px solid #ff5630; padding: 10px; font-size: 13px;">
                Pharmacy inventory low for: <strong>Amoxicillin</strong>
             </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

</body>
</html>