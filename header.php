<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('location: login.php');
    exit;
}

// Membuat koneksi ke database
$conn = mysqli_connect('localhost', 'root', '', 'db_login_google') or die ('Gagal tersambung');

// Ambil data pengguna dari database
$query_select = 'SELECT * FROM tb_users WHERE email = "'.$_SESSION['email'].'"';
$run_query_select = mysqli_query($conn, $query_select);
$user_data = mysqli_fetch_assoc($run_query_select);

if ($user_data) {
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

    if (empty($photo)) {
        $photo = 'assets/img/default.png';
    }
} else {
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="assets/js/script.js"></script>
    <title><?php echo $page_title; ?></title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="index.php"><i class="fas fa-home"></i> <?php echo isset($page_title) ? $page_title : "Welcome"; ?></a>
    <span class="navbar-text">|</span>
    <a class="navbar-text ml-5" href="index.php">Home</a> <!-- Added Profile button -->
    <a class="navbar-text ml-5" href="profile.php">Profile</a> <!-- Added Profile button -->
    <a class="navbar-text ml-5" href="edit.php">Setting</a> <!-- Added Profile button -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img id="profilePicture" src="<?php echo $photo ? $photo : 'assets/img/default.png'; ?>" alt="Profile Picture" class="profile-picture">
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="profile.php">Profile</a>
                    <a class="dropdown-item" href="edit.php">Setting</a>
                    <a class="dropdown-item" id="logoutIcon" href="#">Logout</a>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="logoutIcon"><i class="fas fa-sign-out-alt"></i></a>
            </li>
        </ul>
    </div>
</nav>

<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah anda ingin Logout ? Semua perubahan yang dilakukan akan disimpan dalam database.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.dropdown-toggle').dropdown();

        $('#logoutButton, #logoutIcon').click(function(e) {
            e.preventDefault();
            $('#logoutModal').modal('show');
        });
    });
</script>
</body>
</html>
