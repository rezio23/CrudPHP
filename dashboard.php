<?php
$host    = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "php_apicrud";
$table   = "student_data";
$port   = "3308";

$conn = new mysqli($host, $db_user, $db_pass, $db_name, $port);
if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$error   = "";
$success = "";

// Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name    = trim($conn->real_escape_string($_POST['name']   ?? ''));
    $age     = (int)($_POST['age']    ?? 0);
    $gender  = $conn->real_escape_string($_POST['gender']  ?? '');
    $email   = trim($conn->real_escape_string($_POST['email']  ?? ''));
    $address = trim($conn->real_escape_string($_POST['address'] ?? ''));

    if ($name && $age > 0 && $gender && $email && $address) {
        $sql = "INSERT INTO `$table` (Name, Age, Gender, Email, Address)
                VALUES ('$name', $age, '$gender', '$email', '$address')";
        if ($conn->query($sql)) {
            $success = "Student added successfully!";
        } else {
            $error = "Insert failed: " . $conn->error;
        }
    } else {
        $error = "All fields are required.";
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $conn->query("DELETE FROM `$table` WHERE ID = $id");
    }
}

// Display
$res      = $conn->query("SELECT * FROM `$table` ORDER BY ID ASC");
$students = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Management</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:       #f8f9fa;
    --surface:  #ffffff;
    --surface2: #f1f3f5;
    --border:   #dee2e6;
    --accent:   #4c45e5;
    --glow:     rgba(76,69,229,0.12);
    --danger:   #e03131;
    --danger-g: rgba(224,49,49,0.08);
    --text:     #212529;
    --muted:    #495057;
    --r:        12px;
    --t:        .22s ease;
  }

  body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    padding: 2rem 1.5rem;
  }

  /* ── Header ── */
  .header { text-align: center; margin-bottom: 2.2rem; }
  .header h1 {
    font-size: 1.9rem; font-weight: 700;
    background: linear-gradient(135deg,#4c45e5,#7c3aed);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  .header p { color: var(--muted); font-size: .9rem; margin-top: .35rem; }

  /* ── Card ── */
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 1.6rem;
    margin-bottom: 1.8rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
  }
  .card-title {
    font-size: .95rem; font-weight: 600;
    margin-bottom: 1.2rem;
    display: flex; align-items: center; gap: .5rem;
    color: var(--text);
  }
  .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--accent);
    box-shadow: 0 0 8px var(--glow);
    flex-shrink: 0;
  }

  /* ── Alerts ── */
  .alert {
    padding: .65rem 1rem; border-radius: 8px;
    font-size: .88rem; margin-bottom: 1rem;
  }
  .alert-success { background: #d3f9d8; border:1px solid #b2f2bb; color:#2b8a3e; }
  .alert-error   { background: #fff5f5; border:1px solid #ffe3e3; color:#c92a2a; }

  /* ── Form ── */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: .9rem;
  }
  .fg { display: flex; flex-direction: column; gap: .35rem; }
  .fg label {
    font-size: .75rem; font-weight: 600; color: var(--muted);
    text-transform: uppercase; letter-spacing: .06em;
  }
  .fg input, .fg select, .fg textarea {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-family: inherit;
    font-size: .88rem;
    padding: .6rem .8rem;
    outline: none;
    transition: border-color var(--t), box-shadow var(--t);
  }
  .fg input:focus, .fg select:focus, .fg textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--glow);
  }
  .fg select option { background: var(--surface); }
  .fg textarea { resize: vertical; min-height: 65px; }
  .fg.full { grid-column: 1 / -1; }

  /* ── Buttons ── */
  .btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .6rem 1.3rem;
    border: none; border-radius: 8px;
    font-family: inherit; font-size: .88rem; font-weight: 600;
    cursor: pointer;
    transition: transform var(--t), box-shadow var(--t), background var(--t);
  }
  .btn:hover  { transform: translateY(-2px); }
  .btn:active { transform: translateY(0); }

  .btn-add {
    background: linear-gradient(135deg,#4c45e5,#7c3aed);
    color: #fff;
    box-shadow: 0 4px 12px var(--glow);
  }
  .btn-add:hover { box-shadow: 0 6px 18px var(--glow); }

  .btn-del {
    background: transparent;
    border: 1px solid var(--danger);
    color: var(--danger);
    padding: .38rem .75rem;
    font-size: .8rem;
  }
  .btn-del:hover {
    background: var(--danger);
    color: #fff;
    box-shadow: 0 4px 12px var(--danger-g);
  }

  .submit-row { display: flex; justify-content: flex-end; margin-top: 1.1rem; }

  /* ── Table ── */
  .tbl-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; font-size: .88rem; }
  thead tr { background: #f8f9fa; }
  th {
    padding: .75rem 1rem; text-align: left;
    font-size: .74rem; font-weight: 600; color: var(--muted);
    text-transform: uppercase; letter-spacing: .06em;
    border-bottom: 1px solid var(--border); white-space: nowrap;
  }
  tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background var(--t);
  }
  tbody tr:last-child { border-bottom: none; }
  tbody tr:hover { background: #f8f9fa; }
  td { padding: .8rem 1rem; vertical-align: middle; color: #495057; }

  .badge-id {
    background: #f1f3f5; border: 1px solid var(--border);
    color: var(--accent); font-weight: 700; font-size: .76rem;
    padding: .22rem .55rem; border-radius: 6px;
  }

  .pill {
    display: inline-block; padding: .2rem .6rem;
    border-radius: 20px; font-size: .76rem; font-weight: 500;
    border: 1px solid transparent;
  }
  .pill-m { background: #e7f5ff; color:#1971c2; border-color: #a5d8ff; }
  .pill-f { background: #fff0f6; color:#d6336c; border-color: #ffdeeb; }
  .pill-o { background: #f4fce3; color:#5c940d; border-color: #d8f5a2; }

  /* ── Empty ── */
  .empty { text-align: center; padding: 2.8rem 1rem; color: #adb5bd; }
  .empty svg { opacity: .5; margin-bottom: .9rem; }

  /* ── Count badge ── */
  .cbadge {
    background: #eef2ff; border: 1px solid #c7d2fe;
    color: #4338ca; font-size: .73rem; font-weight: 700;
    padding: .12rem .45rem; border-radius: 99px; margin-left: .4rem;
  }

  @media(max-width:560px) {
    body { padding: 1rem; }
    .form-grid { grid-template-columns: 1fr; }
  }

</style>
</head>
<body>

<!-- Form Insert -->
<div class="card">
  <div class="card-title"><span class="dot"></span> Add New Student</div>

  <?php if ($success): ?>
    <div class="alert alert-success">✓ <?= $success ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error">✕ <?= $error ?></div>
  <?php endif; ?>

  <form method="POST" action="dashboard.php">
    <input type="hidden" name="action" value="add" />
    <div class="form-grid">

      <div class="fg">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name"
               placeholder="e.g. Sombath" required />
      </div>

      <div class="fg">
        <label for="age">Age</label>
        <input type="number" id="age" name="age"
               min="1" max="120" placeholder="e.g. 20" required />
      </div>

      <div class="fg">
        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
          <option value="" disabled selected>Select…</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
      </div>

      <div class="fg">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               placeholder="e.g. username@gmail.com" required />
      </div>

      <div class="fg full">
        <label for="address">Address</label>
        <textarea id="address" name="address"
                  placeholder="e.g. Phnom Penh" required></textarea>
      </div>

    </div>
    <div class="submit-row">
      <button type="submit" class="btn btn-add">Add Student</button>
    </div>
  </form>
</div>

<!-- Table Display -->
<div class="card">
  <div class="card-title">
    <span class="dot"></span> Student Records
    <span class="cbadge"><?= count($students) ?></span>
  </div>

  <div class="tbl-wrap">
    <?php if (empty($students)): ?>
      <div class="empty">
        <svg width="44" height="44" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 4.75a7.25 7.25 0 100 14.5A7.25 7.25 0 0012 4.75zM12 8v4m0 4h.01"/>
        </svg>
        <p>No students yet — add one above!</p>
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Email</th>
            <th>Address</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s):
            $pillClass = match($s['gender'] ?? '') {
              'Male'   => 'pill-m',
              'Female' => 'pill-f',
              default  => 'pill-o',
            };
          ?>
          <tr>
            <td><span class="badge-id">#<?= (int)$s['ID'] ?></span></td>
            <td><?= $s['Name'] ?></td>
            <td><?= (int)$s['Age'] ?></td>
            <td>
              <span class="pill <?= $pillClass ?>">
                <?= $s['Gender'] ?>
              </span>
            </td>
            <td><?= $s['Email'] ?></td>
            <td><?= $s['Address'] ?></td>
            <td>
              <form method="POST" action="dashboard.php"
                onsubmit="return confirm('Delete student #<?= (int)$s['ID'] ?>?')">
                <input type="hidden" name="action" value="delete" />
                <input type="hidden" name="id"     value="<?= (int)$s['ID'] ?>" />
                <button type="submit" class="btn btn-del">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
