<?php
session_start();
date_default_timezone_set('Africa/Cairo');
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$conn = new mysqli('localhost', 'u125244766_system', 'Com@1212', 'u125244766_system');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'add' && isset($_POST['task'])) {
        $task = trim($_POST['task']);
        if ($task !== '') {
            $stmt = $conn->prepare("INSERT INTO todo_items (username, task) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $task);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'done' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE todo_items SET is_done = 1 WHERE id = ? AND username = ?");
        $stmt->bind_param("is", $id, $username);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM todo_items WHERE id = ? AND username = ?");
        $stmt->bind_param("is", $id, $username);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("SELECT id, task, is_done FROM todo_items WHERE username = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $tasks = $stmt->get_result();

    $output = [];
    while ($row = $tasks->fetch_assoc()) {
        $output[] = $row;
    }
    echo json_encode($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My To-Do List</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after {
      box-sizing: border-box;
    }
    html, body {
      margin: 0;
      padding: 0;
      overflow: hidden;
    }
    body {
      background: linear-gradient(135deg, #4b5fd3, #5a3b91);
      font-family: 'Inter', sans-serif;
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .todo-container {
      backdrop-filter: blur(20px);
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 24px;
      padding: 24px 20px;
      width: 90%;
      max-width: 420px;
      min-height: 540px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
    }
    h1 {
      text-align: center;
      font-size: 1.5rem;
      margin-bottom: 20px;
    }
    .counter {
      text-align: center;
      font-size: 0.95rem;
      margin-bottom: 10px;
      opacity: 0.85;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-bottom: 20px;
    }
    input[type="text"] {
      padding: 14px;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.3);
      background: rgba(255,255,255,0.08);
      color: #fff;
      font-size: 1rem;
      width: 100%;
    }
    input::placeholder {
      color: rgba(255,255,255,0.7);
    }
    button {
      padding: 14px;
      border: none;
      border-radius: 12px;
      background: linear-gradient(to right, #00c6ff, #0072ff);
      box-shadow: 0 4px 12px rgba(0, 114, 255, 0.4);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      color: #fff;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      width: 100%;
    }
    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(0, 114, 255, 0.5);
    }
    #todo-list {
      flex-grow: 1;
      overflow-y: auto;
      max-height: 100%;
      padding-right: 6px;
    }
    #todo-list::-webkit-scrollbar {
      width: 6px;
    }
    #todo-list::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.3);
      border-radius: 6px;
    }
    ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    li {
      padding: 12px;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 12px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: move;
    }
    .done { text-decoration: line-through; opacity: 0.6; }
    .actions {
      display: flex;
      gap: 8px;
    }
    .actions button {
      background: rgba(255,255,255,0.15);
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 0.8rem;
      padding: 5px 10px;
      cursor: pointer;
    }
    .actions .delete { background: #dc2626; }
  </style>
</head>
<body>
  <div class="todo-container">
    <h1><?= htmlspecialchars($username) ?>'s To-Do List</h1>
    <div class="counter" id="task-counter"></div>
    <form id="todo-form">
      <input type="text" name="task" id="task-input" placeholder="Enter a new task" required>
      <button type="submit">Add</button>
    </form>
    <ul id="todo-list"></ul>
  </div>

  <audio id="clickSound" src="https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3" preload="auto"></audio>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
  <script>
    const form = document.getElementById('todo-form');
    const input = document.getElementById('task-input');
    const list = document.getElementById('todo-list');
    const counter = document.getElementById('task-counter');
    const clickSound = document.getElementById('clickSound');

    form.onsubmit = e => {
      e.preventDefault();
      clickSound.play().catch(() => {});
      fetchTasks('add', { task: input.value });
      input.value = '';
    };

    function fetchTasks(action = '', data = {}) {
      const payload = new URLSearchParams({ action, ...data });
      fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload
      })
      .then(r => r.json())
      .then(renderTasks);
    }

    function renderTasks(tasks) {
      list.innerHTML = '';
      let count = 0;
      tasks.forEach(t => {
        const li = document.createElement('li');
        li.className = t.is_done == 1 ? 'done' : '';
        li.setAttribute('data-id', t.id);
        if (t.is_done == 0) count++;
        li.innerHTML = `
          ${t.task}
          <div class="actions">
            ${t.is_done == 0 ? `<button onclick=\"markDone(${t.id})\">Done</button>` : ''}
            <button class="delete" onclick=\"deleteTask(${t.id})\">Delete</button>
          </div>`;
        list.appendChild(li);
      });
      counter.textContent = `You have ${count} task${count !== 1 ? 's' : ''} remaining.`;
    }

    function markDone(id) {
      clickSound.play().catch(() => {});
      fetchTasks('done', { id });
    }
    function deleteTask(id) {
      clickSound.play().catch(() => {});
      fetchTasks('delete', { id });
    }

    const isMobile = window.innerWidth <= 800;
    if (!isMobile) {
      new Sortable(list, {
        animation: 150,
        ghostClass: 'dragging'
      });
    }

    fetchTasks();
  </script>
</body>
</html>
<?php $conn->close(); ?>
