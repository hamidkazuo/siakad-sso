<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('location: ../login.php');
    exit;
}

// Membuat koneksi ke database
$conn = mysqli_connect('localhost', 'root', '', 'db_login_google') or die ('Gagal tersambung');

// Ambil data pengguna dari database
$query_select = 'SELECT * FROM tb_users WHERE oauth_id = "'.$_SESSION['oauth_id'].'"';
$run_query_select = mysqli_query($conn, $query_select);
$user_data = mysqli_fetch_assoc($run_query_select);

// Jika data pengguna ditemukan
if($user_data) {
    $role = $user_data['role'];
} else {
    // Tampilkan pesan jika data pengguna tidak ditemukan
    echo "Data pengguna tidak ditemukan.";
    exit;
}

// Array yang berisi peran yang diizinkan untuk mengakses halaman ini
$allowed_roles = array(1, 6, 7, 8, 12); // Sesuaikan dengan peran yang diizinkan untuk sirak.php

// Periksa apakah pengguna memiliki peran yang diizinkan
if (!in_array($role, $allowed_roles)) {
    header('location: ../unauthorized.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAK - Sistem Informasi Akademik</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 text-center">
                <h1 class="mb-4">Selamat Datang di SIRAK - Sistem Informasi Akademik</h1>
                <p class="lead">Sistem pelayanan akademik yang ditujukan bagi operator akademik untuk memperbaharui informasi seputar pelaksanaan registrasi mahasiswa dan pengelolaan data akademik yang melibatkan mahasiswa, dosen, serta elemen terkait.</p>
                <p class="lead">Pengguna : Adm.Prodi, BAAK, BAU, Bag. Akademik</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
