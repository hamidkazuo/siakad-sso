<?php
$page_title = "Home";

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
    $password = $user_data['password'];
    $oauth_id = $user_data['oauth_id'];
    $last_login = $user_data['last_login'];
    $created_at = $user_data['created_at'];
    $role = $user_data['role'];
    $photo = $user_data['picture'];
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
    <script src="assets/js/script.js"></script>
</head>
<body>
<div class="welcome-container">
    <h2 class="card-title mb-4">Selamat Datang, <?php echo htmlspecialchars($fullname); ?></h2>
</div>
<div class="container-wrapper">
    <?php if($role == 1 || $role == 6 || $role == 7 || $role == 8 || $role == 12): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/sirak.php')" class="btn btn-primary btn-block">SIRAK</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 2): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/sima.php')" class="btn btn-primary btn-block">SIMA</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 3): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/siam.php')" class="btn btn-primary btn-block">SIAM</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 7 || $role == 9 || $role == 10): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/siku.php')" class="btn btn-primary btn-block">SIKU</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 5 || $role == 11 || $role == 13 || $role == 14 || $role == 15): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/simpel.php')" class="btn btn-primary btn-block">SIMPEL</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 16): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/spmb.php')" class="btn btn-primary btn-block">SPMB</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 17 || $role == 18): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/sida.php')" class="btn btn-primary btn-block">SIDA</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 3 || $role == 19): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/digilib.php')" class="btn btn-primary btn-block">DIGILIB</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 20): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/sims.php')" class="btn btn-primary btn-block">SIMS</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 21): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/sipm.php')" class="btn btn-primary btn-block">SIPM</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 3): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/angket.php')" class="btn btn-primary btn-block">ANGKET</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 4): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/simpeg.php')" class="btn btn-primary btn-block">SIMPEG</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 22): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/tracer-studi.php')" class="btn btn-primary btn-block">TRACER STUDI</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 22): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/presensi.php')" class="btn btn-primary btn-block">PRESENSI</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 22): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/sika.php')" class="btn btn-primary btn-block">SIKA</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1 || $role == 22): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/silab.php')" class="btn btn-primary btn-block">SILAB</button>
        </div>
    <?php endif; ?>
    <?php if($role == 1): ?>
        <div class="application-button">
            <button onclick="openAppInNewTab('application/superuser.php')" class="btn btn-primary btn-block">SUPERUSER</button>
        </div>
    <?php endif; ?>
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

<script>
    function openAppInNewTab(url) {
        window.open(url, '_blank');
    }

    // Fungsi untuk menampilkan foto default kalo ada error
    document.getElementById('profilePicture').onerror = function() {
        this.onerror = null; // Prevents infinite loop if default image also fails
        this.src = 'assets/img/photo-error.png';
        this.alt = 'Default Profile Picture';
    };

    $(document).ready(function(){
        $('#errorModal').modal('show');
    });
</script>
</body>
</html>
