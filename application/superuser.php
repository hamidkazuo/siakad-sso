<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('location: ../login.php');
    exit;
}

// Membuat koneksi ke database
$conn = mysqli_connect('localhost', 'root', '', 'db_login_google') or die('Gagal tersambung');

// Ambil data pengguna dari database untuk hak akses
$query_select = 'SELECT * FROM tb_users WHERE email = "'.$_SESSION['email'].'"';
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
$allowed_roles = array(1); // Sesuaikan dengan peran yang diizinkan untuk superuser.php

// Periksa apakah pengguna memiliki peran yang diizinkan
if (!in_array($role, $allowed_roles)) {
    header('location: ../unauthorized.php');
    exit;
}

// Ambil data pengguna dari database untuk manajemen users
$query_select = 'SELECT * FROM tb_users';
$run_query_select = mysqli_query($conn, $query_select);
$users = [];
if ($run_query_select) {
    $users = mysqli_fetch_all($run_query_select, MYSQLI_ASSOC);
}

// Menangani form tambah, edit, dan hapus
if (isset($_POST['submit'])) {
    if ($_POST['action'] === 'tambah') {
        // Proses tambah data
        $namalengkap = mysqli_real_escape_string($conn, $_POST['namalengkap']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $nim = mysqli_real_escape_string($conn, $_POST['nim']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        // Pengecekan email, NIM, dan OAuth ID
        $query_check = 'SELECT * FROM tb_users WHERE email = ? OR nim = ?';
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, 'ss', $email, $nim);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $existing_user = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if ($existing_user) {
            if ($existing_user['email'] === $email && $existing_user['nim'] === $nim) {
                $error_message = "Email dan NIM/Username sudah terdaftar. Harap gunakan email dan NIM/Username yang berbeda.";
            } elseif ($existing_user['email'] === $email) {
                $error_message = "Email sudah terdaftar. Harap gunakan email yang berbeda.";
            } elseif ($existing_user['nim'] === $nim) {
                $error_message = "NIM/Username sudah terdaftar. Harap gunakan NIM yang berbeda.";
            } else {
                $error_message = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
    else {
        $photo_name = $nim . '.jpg'; // Sesuaikan dengan nama file yang diinginkan, misalnya nim atau username
        $target_dir = '../profile-picture/';
        $target_file = $target_dir . $photo_name;

        // Pindahkan file foto ke direktori target
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            // Path yang akan disimpan di database
            $db_target_file = 'profile-picture/' . $photo_name;

            // Insert nama file foto ke dalam database
            $query_insert = "INSERT INTO tb_users (fullname, email, nim, password, role, picture) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query_insert);
            mysqli_stmt_bind_param($stmt, 'ssssss', $namalengkap, $email, $nim, $password, $role, $db_target_file);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: superuser.php');
            exit;
        } 
        else {
            $error_message = "Gagal mengunggah foto. Silakan coba lagi.";
        }
    }
}
    elseif ($_POST['action'] === 'edit') {
        // Proses edit data
        $userid = $_POST['userid'];
        $namalengkap = mysqli_real_escape_string($conn, $_POST['namalengkap']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $nim = mysqli_real_escape_string($conn, $_POST['nim']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $oauth_id = mysqli_real_escape_string($conn, $_POST['oauth_id']);
    
        // Pengecekan email, NIM, dan OAuth ID dengan pengecualian user yang sedang di-edit
        $query_check = 'SELECT * FROM tb_users WHERE (email = ? OR nim = ?) AND userid != ?';
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, 'ssi', $email, $nim, $userid);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $existing_user = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);
        
        if ($existing_user) {
            if ($existing_user['email'] === $email && $existing_user['nim'] === $nim) {
                $error_message = "Email dan NIM/Username sudah terdaftar. Harap gunakan email dan NIM yang berbeda.";
            } elseif ($existing_user['email'] === $email) {
                $error_message = "Email sudah terdaftar. Harap gunakan email yang berbeda.";
            } elseif ($existing_user['nim'] === $nim) {
                $error_message = "NIM/Username sudah terdaftar. Harap gunakan NIM yang berbeda.";
            } else {
                $error_message = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }

        else {
            // Update data pengguna ke dalam database
            $query_update = "UPDATE tb_users SET fullname=?, email=?, nim=?, password=?, role=?, oauth_id=? WHERE userid=?";
            $stmt = mysqli_prepare($conn, $query_update);
            mysqli_stmt_bind_param($stmt, 'ssssssi', $namalengkap, $email, $nim, $password, $role, $oauth_id, $userid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
    
            // Cek apakah ada upload foto baru
            if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Hapus foto lama jika ada
                $query_select_old_photo = "SELECT picture FROM tb_users WHERE userid=?";
                $stmt_old_photo = mysqli_prepare($conn, $query_select_old_photo);
                mysqli_stmt_bind_param($stmt_old_photo, 'i', $userid);
                mysqli_stmt_execute($stmt_old_photo);
                mysqli_stmt_bind_result($stmt_old_photo, $old_photo);
                mysqli_stmt_fetch($stmt_old_photo);
                mysqli_stmt_close($stmt_old_photo);
    
                if ($old_photo) {
                    // Hapus foto lama dari direktori
                    $old_photo_path = '../' . $old_photo;
                    if (file_exists($old_photo_path)) {
                        unlink($old_photo_path);
                    }
                }
    
                // Pindahkan foto baru ke direktori
                $photo_name = $nim . '.jpg'; // Sesuaikan dengan nama file yang diinginkan, misalnya nim atau username
                $target_dir = '../profile-picture/';
                $target_file = $target_dir . $photo_name;
    
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                    // Update path foto baru ke dalam database
                    $db_target_file = 'profile-picture/' . $photo_name;
                    $query_update_photo = "UPDATE tb_users SET picture=? WHERE userid=?";
                    $stmt_photo = mysqli_prepare($conn, $query_update_photo);
                    mysqli_stmt_bind_param($stmt_photo, 'si', $db_target_file, $userid);
                    mysqli_stmt_execute($stmt_photo);
                    mysqli_stmt_close($stmt_photo);
                } else {
                    $error_message = "Gagal mengunggah foto. Silakan coba lagi.";
                }
            }
            header('Location: superuser.php');
            exit;
        }
    } 
    elseif ($_POST['action'] === 'hapus') {
        // Proses hapus data
        $userid = $_POST['userid'];
    
        // Ambil path foto pengguna yang akan dihapus
        $query_select_photo = "SELECT picture FROM tb_users WHERE userid=?";
        $stmt_select_photo = mysqli_prepare($conn, $query_select_photo);
        mysqli_stmt_bind_param($stmt_select_photo, 'i', $userid);
        mysqli_stmt_execute($stmt_select_photo);
        mysqli_stmt_bind_result($stmt_select_photo, $photo_path);
        mysqli_stmt_fetch($stmt_select_photo);
        mysqli_stmt_close($stmt_select_photo);
    
        // Hapus data pengguna dari database
        $query_delete = "DELETE FROM tb_users WHERE userid=?";
        $stmt_delete = mysqli_prepare($conn, $query_delete);
        mysqli_stmt_bind_param($stmt_delete, 'i', $userid);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
    
        // Hapus foto pengguna jika ada
        if ($photo_path) {
            $full_photo_path = '../' . $photo_path;
            if (file_exists($full_photo_path)) {
                unlink($full_photo_path);
            }
        }
        header('Location: superuser.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUPERUSER</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
</head>
<body>
    <h2 class="d-flex justify-content-center">Data Pengguna</h2>
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
                <?php echo $error_message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <!-- Tombol Tambah -->
         <div class="d-flex justify-content-start">
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#tambahModal">
            <i class="fas fa-user-plus"></i> Tambah Pengguna
        </button>
        </div>

        <!-- Tabel Data Pengguna -->
        <table id="myTable" class="table">
    <thead>
        <tr>
            <th>No.</th>
            <th>Photo</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>NIM/Username</th>
            <th>Password</th>
            <th>Role</th>
            <th>ID Akun Google</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $key => $user) : ?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td style="text-align:center;">
                    <a href="<?php echo $user['userid']; ?>" data-toggle="modal">
                    <?php if (empty($user['picture'])) : ?>
                        <!-- Jika tidak ada gambar yang ditentukan -->
                        <img src="../assets/img/default.png" class="pp-profile-picture-su">
                    <?php elseif (strpos($user['picture'], 'http') === 0) : ?>
                        <!-- Jika gambar berasal dari URL Google -->
                        <img src="<?php echo $user['picture']; ?>" class="pp-profile-picture-su">
                    <?php else : ?>
                        <!-- Jika gambar berasal dari direktori lokal -->
                        <?php
                            $photo_path = '../' . $user['picture'];
                            $photo_name = basename($photo_path);
                            $photo_url = $photo_path . '?timestamp=' . filemtime($photo_path);
                        ?>
                        <img src="<?php echo $photo_url; ?>" class="pp-profile-picture-su">
                    <?php endif; ?>
                    </a>
                </td>
                <td><?= $user['fullname'] ?></td>
                <td><?= $user['email'] ?></td>
                <td><?= $user['nim'] ?></td>
                <td>*******</td>
                <td>
                    <?php
                    // Menampilkan peran pengguna sesuai dengan role ID
                    $role = "";
                    switch ($user['role']) {
                        case 1:
                            $role = "Administrator";
                            break;
                        case 2:
                            $role = "Dosen";
                            break;
                        case 3:
                            $role = "Mahasiswa";
                            break;
                        case 4:
                            $role = "Staff Kepegawaian";
                            break;
                        case 5:
                            $role = "Rektor";
                            break;
                        case 6:
                            $role = "BAAK";
                            break;
                        case 7:
                            $role = "BAU";
                            break;
                        case 8:
                            $role = "Bagian Akademik";
                            break;
                        case 9:
                            $role = "Bagian Keuangan";
                            break;
                        case 10:
                            $role = "Bendahara";
                            break;
                        case 11:
                            $role = "Ketua";
                            break;
                        case 12:
                            $role = "Admin Prodi";
                            break;
                        case 13:
                            $role = "Dekan";
                            break;
                        case 14:
                            $role = "Kepala Prodi";
                            break;
                        case 15:
                            $role = "Kepala Biro";
                            break;
                        case 16:
                            $role = "Panitia PMB";
                            break;
                        case 17:
                            $role = "Panitia Wisuda";
                            break;
                        case 18:
                            $role = "Panitia Yudisium";
                            break;
                        case 19:
                            $role = "Petugas Perpustakaan";
                            break;
                        case 20:
                            $role = "Wali/Orangtua Mahasiswa";
                            break;
                        case 21:
                            $role = "Lembaga Penjaminan Mutu";
                            break;
                        case 22:
                            $role = "Pengguna Biasa";
                            break;
                        default:
                            $role = "Pengguna Biasa";
                            break;
                    }
                    echo $role;
                    ?>
                </td>
                <td><?= $user['oauth_id'] ?></td>
                <td>
                    <div class="btn-group" role="group" aria-label="">
                        <button type="button" class="btn btn-lg btn-warning edit-btn" data-toggle="modal" data-target="#editModal"
                            data-userid="<?= $user['userid'] ?>" data-namalengkap="<?= $user['fullname'] ?>" data-email="<?= $user['email'] ?>" data-nim="<?= $user['nim'] ?>" data-password="<?= $user['password'] ?>" data-role="<?= $user['role'] ?>" data-oauth_id="<?= $user['oauth_id'] ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-toggle="modal" data-target="#hapusModal"
                            data-userid="<?= $user['userid'] ?>">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    <!-- Modal Tambah Pengguna -->
<div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahModalLabel">Tambah Pengguna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="tambah">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="photo">Foto Profil:</label>
                            <div class="d-flex flex-column align-items-center">
                                <img id="previewPhoto" src="../assets/img/default.png" alt="Profile Picture" class="pp-profile-picture mb-3">
                                <div class="input-group" hidden>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="tambah-photo" name="photo" accept=".jpg" onchange="validatePhoto(this)">
                                        <label class="custom-file-label" for="photo">Pilih foto...(.jpg)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="namalengkap">Nama Lengkap:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="namalengkap" id="namalengkap" placeholder="Nama Lengkap" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nim">NIM/Username:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="nim" name="nim" placeholder="NIM/Username" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                    <div class="input-group-append">
                                        <button type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="passwordIcon" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="role">Role:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                    </div>
                                    <select class="form-control" id="role" name="role" aria-placeholder="Role" required>
                                        <option value="1">1. Administrator</option>
                                        <option value="2">2. Dosen</option>
                                        <option value="3">3. Mahasiswa</option>
                                        <option value="4">4. Staff Kepegawaian</option>
                                        <option value="5">5. Rektor</option>
                                        <option value="6">6. BAAK</option>
                                        <option value="7">7. BAU</option>
                                        <option value="8">8. Bagian Akademik</option>
                                        <option value="9">9. Bagian Keuangan</option>
                                        <option value="10">10. Bendahara</option>
                                        <option value="11">11. Ketua</option>
                                        <option value="12">12. Admin Prodi</option>
                                        <option value="13">13. Dekan</option>
                                        <option value="14">14. Kepala Prodi</option>
                                        <option value="15">15. Kepala Biro</option>
                                        <option value="16">16. Panitia PMB</option>
                                        <option value="17">17. Panitia Wisuda</option>
                                        <option value="18">18. Panitia Yudisium</option>
                                        <option value="19">19. Petugas Perpustakaan</option>
                                        <option value="20">20. Wali/Orangtua Mahasiswa</option>
                                        <option value="21">21. Lembaga Penjaminan Mutu</option>
                                        <option value="22">22. Pengguna Biasa</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="cropped_photo" id="cropped_photo">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Modal Edit Pengguna -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Pengguna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit-userid" name="userid">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="edit-photo">Foto Profil:</label>
                            <div class="d-flex flex-column align-items-center">
                                <img id="edit-photo-preview" src="../assets/img/default.png" alt="Profile Picture" class="pp-profile-picture mb-3">
                                <div class="input-group" hidden>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="edit-photo" name="photo" accept=".jpg" onchange="validatePhoto(this)">
                                        <label class="custom-file-label" for="photo">Pilih foto...(.jpg)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="edit-namalengkap">Nama Lengkap:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="edit-namalengkap" name="namalengkap" placeholder="Nama Lengkap" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-email">Email:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="edit-email" name="email" placeholder="Email" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-nim">NIM/Username:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="edit-nim" name="nim" placeholder="NIM/Username" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-password">Password:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="edit-password" name="password" placeholder="Password" required>
                                    <div class="input-group-append">
                                        <button type="button" id="toggleEditPassword">
                                            <i class="fas fa-eye" id="editPasswordIcon" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-role">Role:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                    </div>
                                    <select class="form-control" id="edit-role" name="role" aria-placeholder="Role" required>
                                        <option value="1">1. Administrator</option>
                                        <option value="2">2. Dosen</option>
                                        <option value="3">3. Mahasiswa</option>
                                        <option value="4">4. Staff Kepegawaian</option>
                                        <option value="5">5. Rektor</option>
                                        <option value="6">6. BAAK</option>
                                        <option value="7">7. BAU</option>
                                        <option value="8">8. Bagian Akademik</option>
                                        <option value="9">9. Bagian Keuangan</option>
                                        <option value="10">10. Bendahara</option>
                                        <option value="11">11. Ketua</option>
                                        <option value="12">12. Admin Prodi</option>
                                        <option value="13">13. Dekan</option>
                                        <option value="14">14. Kepala Prodi</option>
                                        <option value="15">15. Kepala Biro</option>
                                        <option value="16">16. Panitia PMB</option>
                                        <option value="17">17. Panitia Wisuda</option>
                                        <option value="18">18. Panitia Yudisium</option>
                                        <option value="19">19. Petugas Perpustakaan</option>
                                        <option value="20">20. Wali/Orangtua Mahasiswa</option>
                                        <option value="21">21. Lembaga Penjaminan Mutu</option>
                                        <option value="22">22. Pengguna Biasa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-oauth_id">ID Akun Google:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fab fa-google"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="edit-oauth_id" name="oauth_id" placeholder="ID Akun Google" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="cropped_photo" id="edit-cropped_photo">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Pengguna -->
<div class="modal fade" id="hapusModal" tabindex="-1" role="dialog" aria-labelledby="hapusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hapusModalLabel">Hapus Pengguna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="hapus">
                    <input type="hidden" id="delete-userid" name="userid">
                    Apakah Anda yakin ingin menghapus pengguna ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" name="submit">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Set timer untuk menghilangkan alert setelah 3 detik
        setTimeout(function() {
            $("#errorAlert").alert('close');
        }, 5000);
    });
    
    // Fungsi untuk menampilkan foto dengan ukuran maksimum 200x200 pixel dan meresize sebelum ditampilkan
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.src = e.target.result;
                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');

                    var MAX_WIDTH = 200;
                    var MAX_HEIGHT = 200;
                    var width = img.width;
                    var height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else {
                        if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    $('#previewPhoto').attr('src', canvas.toDataURL());
                };
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Fungsi untuk menampilkan foto dengan ukuran maksimum 200x200 pixel pada modal edit dan meresize sebelum ditampilkan
    function readEditURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.src = e.target.result;
                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');

                    var MAX_WIDTH = 200;
                    var MAX_HEIGHT = 200;
                    var width = img.width;
                    var height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else {
                        if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    $('#edit-photo-preview').attr('src', canvas.toDataURL());
                };
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Event listener saat input file diubah pada modal tambah
    $("#tambah-photo").change(function() {
        readURL(this);
    });

    // Event listener saat input file diubah pada modal edit
    $("#edit-photo").change(function() {
        readEditURL(this);
    });
</script>

<script>
    // Mendapatkan elemen input password dan ikon mata untuk tambah password
    var passwordInput = document.getElementById("password");
    var passwordIcon = document.getElementById("passwordIcon");

    // Mendapatkan elemen input password dan ikon mata untuk edit password
    var editPasswordInput = document.getElementById("edit-password");
    var editPasswordIcon = document.getElementById("editPasswordIcon");

    document.getElementById('edit-photo-preview').addEventListener('click', function () {
        document.getElementById('edit-photo').click();
    });

    document.getElementById('previewPhoto').addEventListener('click', function () {
        document.getElementById('tambah-photo').click();
    });

    // Fungsi untuk memvalidasi jenis file yang diunggah
    function validatePhoto(input) {
        var allowedExtensions = /(\.jpg|\.)$/i; // Ekstensi file yang diizinkan
        if (!allowedExtensions.exec(input.value)) {
            alert('Hanya file dengan ekstensi .jpg yang diizinkan!');
            input.value = ''; // Hapus nilai input
            return false;
        } else {
        }
    }

    // Tambahkan event listener ke tombol mata untuk tambah password
    document.getElementById("togglePassword").addEventListener("click", function() {
        // Ubah tipe input antara password dan text untuk tambah password
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            // Ganti ikon ke ikon mata terbuka
            passwordIcon.className = "fas fa-eye-slash";
        } else {
            passwordInput.type = "password";
            // Ganti ikon ke ikon mata tertutup
            passwordIcon.className = "fas fa-eye";
        }
    });

    // Tambahkan event listener ke tombol mata untuk edit password
    document.getElementById("toggleEditPassword").addEventListener("click", function() {
        // Ubah tipe input antara password dan text untuk edit password
        if (editPasswordInput.type === "password") {
            editPasswordInput.type = "text";
            // Ganti ikon ke ikon mata terbuka
            editPasswordIcon.className = "fas fa-eye-slash";
        } else {
            editPasswordInput.type = "password";
            // Ganti ikon ke ikon mata tertutup
            editPasswordIcon.className = "fas fa-eye";
        }
    });

    $(document).ready(function() {
        $('#myTable').DataTable({
            "pageLength": 10 // Menampilkan 3 baris per halaman
        });

        // Menampilkan modal tambah ketika tombol "Tambah Pengguna" diklik
        $('#tambah-btn').click(function() {
            $('#tambahModal').modal('show');
        });

        // Saat tombol edit diklik
        $('.edit-btn').click(function() {
            var userid = $(this).data('userid');
            var namalengkap = $(this).data('namalengkap');
            var email = $(this).data('email');
            var nim = $(this).data('nim');
            var password = $(this).data('password');
            var role = $(this).data('role');
            var oauth_id = $(this).data('oauth_id');
            var picture = $(this).closest('tr').find('.pp-profile-picture-su').attr('src'); // Ambil URL gambar dari elemen terdekat

            $('#edit-photo-preview').attr('src', picture);

            $('#edit-userid').val(userid);
            $('#edit-namalengkap').val(namalengkap);
            $('#edit-email').val(email);
            $('#edit-nim').val(nim);
            $('#edit-password').val(password);
            $('#edit-role').val(role);
            $('#edit-oauth_id').val(oauth_id);

            $('#editModal').modal('show');
        });

        // Menampilkan modal hapus dan mengisi input dengan id pengguna yang dipilih ketika tombol "Hapus" diklik
        $('.delete-btn').click(function() {
            var userid = $(this).data('userid');
            $('#delete-userid').val(userid);
            $('#hapusModal').modal('show');
        });
    });
</script>
</body>
</html>