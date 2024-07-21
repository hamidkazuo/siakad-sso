<?php
$page_title = "Profile";
include "header.php";
if(!isset($_SESSION['logged_in'])){
    header('location: login.php');
    exit;
}

// Membuat koneksi ke database
$conn = mysqli_connect('localhost', 'root', '', 'db_login_google') or die ('Gagal tersambung');

// Ambil data pengguna dari database
$query_select = 'SELECT * FROM tb_users WHERE email = "'.$_SESSION['email'].'"';
$run_query_select = mysqli_query($conn, $query_select);
$user_data = mysqli_fetch_assoc($run_query_select);

// Jika data pengguna ditemukan
if($user_data) {
    $userid = $user_data['userid'];
    $fullname = $user_data['fullname'];
    $email = $user_data['email']; 
    $nim = $user_data['nim'];
    $oauth_id = $user_data['oauth_id'];
    $last_login = $user_data['last_login'];
    $created_at = $user_data['created_at'];
    $role = $user_data['role'];
    $photo = $user_data['picture'];

    // Teks pengganti berdasarkan nilai role
    $role_text = '';
    switch ($role) {
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

    // Cek apakah NIM dan password kosong
    if (empty($nim) || empty($password)) {
        header('location: register.php');
        exit;
    }

    // Jika kolom picture kosong, gunakan foto default
    if (empty($photo)) {
        $photo = 'assets/img/default.png';
    }
    
} else {
    // Tampilkan pesan jika data pengguna tidak ditemukan
    header('location: logout.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="card-title text-center mb-4">Your Profile</h1>
    <div class="row">
        <div class="user-info-container">
            <div class="pp-profile-container mb-3">
                <?php if(isset($photo)): ?>
                    <img id="profilePicture" src="<?php echo $photo ? $photo : 'assets/profile-picture/default.png'; ?>" alt="Profile Picture" class="pp-profile-picture">
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-8">
            <div class="user-info-container">
                <p><strong>Nama Lengkap:</strong> <?= $fullname ?></p>
                <p><strong>Email:</strong> <?= $email ?></p>
                <p><strong>NIM/Username:</strong> <?= $nim ?></p>
                <p><strong>Jenis Pengguna:</strong> <?= $role ?></p>
                <p><strong>Kode Akun Google:</strong> <?= empty($oauth_id) ? '(Kosong, Anda Harus Login Menggunakan Akun Google)' : $oauth_id ?></p>
                <p><strong>Dibuat pada:</strong> <?= $created_at ?></p>
                <p><strong>Terakhir kali Login:</strong> <?= $last_login ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<?php if(isset($error_message)): ?>
    <!-- Modal HTML -->
    <div id="errorModal" class="modal fade">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="icon-box">
                        <i class="material-icons">&#xE5CD;</i>
                    </div>				
                    <h4 class="modal-title w-100">Maaf!</h4>	
                </div>
                <div class="modal-body">
                    <p class="text-center"><?php echo $error_message; ?></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger btn-block" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="assets/js/script.js"></script>
<script>

    $(document).ready(function(){
        $('#errorModal').modal('show');
    });
    
    // Fungsi untuk menampilkan foto default kalo ada error
    document.getElementById('profilePicture').onerror = function() {
        this.onerror = null; // Prevents infinite loop if default image also fails
        this.src = 'assets/img/photo-error.png';
        this.alt = 'Default Profile Picture';
    };
</script>
</body>
</html>
