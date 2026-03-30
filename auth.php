<?php
session_start();

$USERS = [
    'student@example.com' => ['password' => 'student123', 'name' => 'Student User', 'role' => 'student'],
    'tutor@example.com'   => ['password' => 'tutor12345', 'name' => 'Tutor User',   'role' => 'tutor'],
];

$COOKIE_NAME     = 'remember_user';
$COOKIE_DURATION = 60 * 60 * 24 * 7; // 7 hari

function returnJSON(array $data): void {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

//RESTORE SESSION DARI COOKIE
function restoreFromCookie(): void {
    global $USERS, $COOKIE_NAME;
    if (!isset($_COOKIE[$COOKIE_NAME])) return;

    $decoded = base64_decode($_COOKIE[$COOKIE_NAME]);
    [$email, $token] = explode('|', $decoded, 2) + [null, null];
    if (!$email || !$token || !isset($USERS[$email])) return;

    $expected = hash('sha256', $email . $USERS[$email]['password']);
    if (!hash_equals($expected, $token)) return;

    $_SESSION['user'] = [
        'email' => $email,
        'name'  => $USERS[$email]['name'],
        'role'  => $USERS[$email]['role'],
    ];
}

//LOGIN
if (($_POST['action'] ?? '') === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!isset($USERS[$email]) || $USERS[$email]['password'] !== $password) {
        returnJSON(['success' => false, 'message' => 'Email atau password salah.']);
    }

    // Simpan session
    $_SESSION['user'] = [
        'email' => $email,
        'name'  => $USERS[$email]['name'],
        'role'  => $USERS[$email]['role'],
    ];

    // Set cookie jika remember me dicentang
    if (!empty($_POST['remember'])) {
        $token  = hash('sha256', $email . $USERS[$email]['password']);
        $cookie = base64_encode($email . '|' . $token);
        setcookie($COOKIE_NAME, $cookie, time() + $COOKIE_DURATION, '/', '', false, false);
    }

    returnJSON([
        'success'  => true,
        'message'  => 'Login berhasil! Selamat datang, ' . $USERS[$email]['name'] . '.',
        'redirect' => 'dashboard.html',
    ]);
}

//SIGNUP STUDENT
if (($_POST['action'] ?? '') === 'signup_student') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        returnJSON(['success' => false, 'message' => 'Semua field harus diisi.']);
    }

    // Simpan session (simulasi signup berhasil)
    $_SESSION['user'] = ['email' => $email, 'name' => $name, 'role' => 'student'];

    returnJSON([
        'success'  => true,
        'message'  => 'Sign up berhasil! Selamat datang, ' . $name . '.',
        'redirect' => 'dashboard.html',
    ]);
}


//SIGNUP TUTOR
if (($_POST['action'] ?? '') === 'signup_tutor') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        returnJSON(['success' => false, 'message' => 'Semua field harus diisi.']);
    }

    $_SESSION['user'] = ['email' => $email, 'name' => $name, 'role' => 'tutor'];

    returnJSON([
        'success'  => true,
        'message'  => 'Sign up berhasil! Selamat datang, ' . $name . '.',
        'redirect' => 'dashboard.html',
    ]);
}


//LOGOUT
if (($_POST['action'] ?? '') === 'logout') {
    $_SESSION = [];

    //Hapus cookie PHPSESSID dari browser
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }


    session_destroy();

    // Hapus cookie remember_user jika ada
    if (isset($_COOKIE[$COOKIE_NAME])) {
        setcookie($COOKIE_NAME, '', time() - 3600, '/');
    }

    returnJSON(['success' => true, 'message' => 'Logout berhasil.', 'redirect' => 'index.html']);
}

if (($_GET['action'] ?? '') === 'check' || ($_POST['action'] ?? '') === 'check') {
    if (empty($_SESSION['user'])) {
        restoreFromCookie();
    }

    if (!empty($_SESSION['user'])) {
        returnJSON(['loggedIn' => true, 'user' => $_SESSION['user'], 'hasCookie' => isset($_COOKIE[$COOKIE_NAME])]);
    } else {
        returnJSON(['loggedIn' => false]);
    }
}

returnJSON(['success' => false, 'message' => 'Action tidak dikenal.']);