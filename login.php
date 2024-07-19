<?php
    //memulai sesi
    session_start();
    
    //set timezone default untuk syntax query stampel login
    date_default_timezone_set('Asia/Jakarta');

    // Jika ada request dari JavaScript untuk memeriksa status login
    if (isset($_POST['action']) && $_POST['action'] == 'check_login') {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            echo 'loggedin';
        } else {
            echo 'notloggedin';
        }
        exit; // Hentikan eksekusi lebih lanjut setelah mengirim respons
    }

    //membuat koneksi
    $conn = mysqli_connect('localhost', 'root', '', 'db_login_google') or die ('gagal tersambung');

    //librari yang diperlukan untuk google sign-in
    require_once 'vendor/autoload.php';

    //key yang didapatkan setelah membuat credentials OAuth 2.0 Client IDs di google cloud console
    $client_id = '(isi dengan client id anda)';
    $client_secret = '(isi dengan client secret anda)';
    $redirect_uri = 'http://localhost/siakad-sso/login.php'; //harus sama dengan yang ada di google cloud console
    
    //inisialisasi sign in google
    $client = new Google_Client();
    $client->setClientId($client_id);
    $client->setClientSecret($client_secret);
    $client->setRedirectUri($redirect_uri);

    //mendapatkan data user yang sign in dari google
    $client->addScope('email');
    $client->addScope('profile');

    // jika user login menggunakan email, nim, dan password
    if(isset($_POST['nim']) && isset($_POST['password'])){
        $loginCredential = mysqli_real_escape_string($conn, $_POST['nim']); // Menghindari SQL Injection
        $password = mysqli_real_escape_string($conn, $_POST['password']); // Menghindari SQL Injection

        // Query untuk memeriksa apakah email atau NIM dan password sesuai
        $query_check = 'SELECT * FROM tb_users WHERE (email = "'.$loginCredential.'" OR nim = "'.$loginCredential.'") AND password = "'.$password.'"';
        $run_query_check = mysqli_query($conn, $query_check);
        $user_data = mysqli_fetch_assoc($run_query_check);

        // Jika data pengguna ditemukan
        if($user_data){
            // Set data pengguna ke dalam sesi
            $_SESSION['logged_in'] = true;
            $_SESSION['oauth_id'] = $user_data['oauth_id'];
            $_SESSION['uname'] = $user_data['fullname'];
            $_SESSION['nim'] = $user_data['nim'];
            $_SESSION['password'] = $user_data['password'];
            $_SESSION['email'] = $user_data['email'];
            $_SESSION['picture'] = $user_data['picture'];

            // Perbarui waktu last login di database
            $current_time = date('Y-m-d H:i:s');
            $update_query = "UPDATE tb_users SET last_login = '$current_time' WHERE nim = '{$user_data['nim']}'";
            mysqli_query($conn, $update_query);

            // Arahkan ke halaman index.php
            header('location: index.php');
        } else {
            // Tampilkan pesan login gagal jika NIM/email dan password tidak cocok
            $error_message = "NIM/Username/Email atau Password Anda salah."; // Menghentikan eksekusi skrip selanjutnya
        }
    }

    // Jika user login menggunakan akun google
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (!isset($token['error'])) {
            
            $client->setAccessToken($token['access_token']);

            $service = new Google_Service_Oauth2($client);
            $profile = $service->userinfo->get();

            // Data yang hanya dibutuhkan
            $g_name = $profile['name'];
            $g_email = $profile['email'];
            $g_id = $profile['id'];
            $g_photo = $profile['picture'];

            // Mengubah ukuran gambar menjadi lebih besar, misalnya 200x200
            $g_photo = str_replace('s96-c', 's200-c', $g_photo);

            // Stempel waktu untuk last login di database
            $currtime = date('Y-m-d H:i:s');

            // Perintah untuk check query apakah user terdaftar atau tidak
            $query_check = 'SELECT * FROM tb_users WHERE BINARY email = BINARY "' . $g_email . '" OR oauth_id = "' . $g_id . '"';
            $run_query_check = mysqli_query($conn, $query_check);
            $user = mysqli_fetch_assoc($run_query_check);

            $_SESSION['logged_in'] = true;
            $_SESSION['access_token'] = $token['access_token'];
            $_SESSION['oauth_id'] = $g_id;
            $_SESSION['uname'] = $g_name;
            $_SESSION['email'] = $g_email;
            $_SESSION['picture'] = $g_photo;

            if ($user) {
                // Jika nim dan password kosong, arahkan user ke register.php
                if (empty($user['nim']) && empty($user['password'])) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['access_token'] = $token['access_token'];
                    $_SESSION['oauth_id'] = $g_id;
                    $_SESSION['uname'] = $g_name;
                    $_SESSION['email'] = $g_email;
                    $_SESSION['picture'] = $g_photo;
                    echo '<script>
                    if (window.opener) {
                        window.opener.location.href = "register.php";
                        window.close();
                    }
                    </script>';
                    exit;
                }

                // Jika email tidak sesuai, lakukan update email
                if ($user['email'] !== $g_email) {
                    $query_update = 'UPDATE tb_users SET email = "' . $g_email . '", last_login = "' . $currtime . '" WHERE oauth_id = "' . $g_id . '"';
                    mysqli_query($conn, $query_update);
                }

                // Jika oauth_id tidak sesuai, lakukan update oauth_id
                if ($user['oauth_id'] !== $g_id) {
                    $query_update = 'UPDATE tb_users SET oauth_id = "' . $g_id . '", last_login = "' . $currtime . '" WHERE email = "' . $g_email . '"';
                    mysqli_query($conn, $query_update);
                }

                 // Jika foto kosong, lakukan update foto
                if (empty($user['picture'])) {
                    $query_update = 'UPDATE tb_users SET picture = "' . $g_photo . '" WHERE email = "' . $g_email . '" OR oauth_id = "' . $g_id . '"';
                    mysqli_query($conn, $query_update);
                }

                // Jika email belum terdaftar, lakukan update email
                if (empty($user['email'])) {
                    $query_update = 'UPDATE tb_users SET email = "' . $g_email . '", last_login = "' . $currtime . '" WHERE oauth_id = "' . $g_id . '"';
                    mysqli_query($conn, $query_update);
                    $_SESSION['logged_in'] = true;
                    $_SESSION['access_token'] = $token['access_token'];
                    $_SESSION['oauth_id'] = $g_id;
                    $_SESSION['uname'] = $g_name;
                    $_SESSION['email'] = $g_email;
                    $_SESSION['picture'] = $g_photo;
                    echo '<script>
                    if (window.opener) {
                        window.opener.location.href = "index.php";
                        window.close();
                    }
                    </script>';
                }

                // Jika oauth_id belum terdaftar, lakukan update oauth_id
                if (empty($user['oauth_id'])) {
                    $query_update = 'UPDATE tb_users SET oauth_id = "' . $g_id . '", last_login = "' . $currtime . '" WHERE email = "' . $g_email . '"';
                    mysqli_query($conn, $query_update);
                    // Set sesi pengguna
                    $_SESSION['logged_in'] = true;
                    $_SESSION['access_token'] = $token['access_token'];
                    $_SESSION['oauth_id'] = $g_id;
                    $_SESSION['uname'] = $g_name;
                    $_SESSION['email'] = $g_email;
                    $_SESSION['picture'] = $g_photo;
                    echo '<script>
                    if (window.opener) {
                        window.opener.location.href = "index.php";
                        window.close();
                    }
                    </script>';
                }
            } else {
                // Jika user belum terdaftar, lakukan insert data
                $query_insert = 'INSERT INTO tb_users (fullname, email, nim, password, role, oauth_id, last_login, picture) VALUES ("' . $g_name . '", "' . $g_email . '", "", "", "22", "' . $g_id . '", "' . $currtime . '", "' . $g_photo . '")';
                $run_query_insert = mysqli_query($conn, $query_insert);
                $_SESSION['logged_in'] = true;
                $_SESSION['access_token'] = $token['access_token'];
                $_SESSION['oauth_id'] = $g_id;
                $_SESSION['uname'] = $g_name;
                $_SESSION['email'] = $g_email;
                $_SESSION['picture'] = $g_photo;
                echo '<script>
                    if (window.opener) {
                        window.opener.location.href = "register.php";
                        window.close();
                    }
                    </script>';
            }
            
            echo '<script>
            if (window.opener) {
                window.close();
            }
            </script>';
            
        } else {
            $error_message = "Login Gagal";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIAKAD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="login-container">
        <img src="assets/img/logo.png" alt="Logo Universitas" class="logo">
        <h5 class="card-title text-center mb-4">Universitas Islam Majapahit</h5>
                <form method="post" action="">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" class="form-control" name="nim" placeholder="NIM/Username/Email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                            <div class="input-group-append">
                                <button type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="passwordIcon" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-left">
                        <a href="#" onclick="return openPopup('<?= $client->createAuthUrl(); ?>');" class="text">Lupa Password?</a>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <a href="daftar.php" class="btn btn-secondary btn-block">Daftar</a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary btn-block">Login</button>
                            </div>
                        </div>
                    </div>
                </form>
                <p class="text-center mt-3">Atau login dengan</p>
                <div class="text-center mt-3">
                    <a href="#" onclick="return openPopup('<?= $client->createAuthUrl(); ?>');" class="btn btn-google btn-block">
                        <img src="assets/img/btn_google.png" alt="button google" class="img-fluid">
                    </a>
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

<script src="script.js"></script>
<script>
function openPopup(url) {
    var width = 600;
    var height = 600;
    var left = (screen.width - width) / 2;
    var top = (screen.height - height) / 2;
    var params = 'width=' + width + ', height=' + height;
    params += ', top=' + top + ', left=' + left;
    params += ', directories=no';
    params += ', location=no';
    params += ', menubar=no';
    params += ', resizable=no';
    params += ', scrollbars=no';
    params += ', status=no';
    params += ', toolbar=no';

    var newWindow = window.open(url, 'GoogleLogin', params);

    if (newWindow) {
        newWindow.location.href = url;

        if (window.focus) {
            newWindow.focus();
        }
    } else {
        // Jika pemblokir popup aktif atau gagal membuka popup
        alert('Gagal membuka popup. Pastikan pemblokir popup dinonaktifkan.');
    }

    return false;
}

    $(document).ready(function(){
        $('#errorModal').modal('show');
    });

    document.getElementById('togglePassword').addEventListener('click', function () {
        var passwordInput = document.getElementById('password');
        var passwordIcon = document.getElementById('passwordIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            passwordIcon.className = 'fas fa-eye';
        }
    });
</script>
<script>
    // Polling interval dalam milidetik (misalnya 3000ms = 3 detik)
    var pollingInterval = 3000;

    // Fungsi untuk melakukan polling
    function checkLoginStatus() {
        // Lakukan request AJAX untuk memeriksa status login
        $.ajax({
            url: 'login.php', // Panggil login.php untuk memeriksa status login
            method: 'POST', // Gunakan metode POST untuk memeriksa status login
            data: { action: 'check_login' }, // Kirim data action ke login.php
            success: function(response) {
                if (response == 'loggedin') {
                    // Jika pengguna sudah login, refresh halaman
                    location.href = 'index.php'; // Ganti dengan halaman yang sesuai setelah login
                } else {
                    // Jika belum login, lanjutkan polling setelah interval tertentu
                    setTimeout(checkLoginStatus, pollingInterval);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking login status:', error);
                // Coba polling lagi setelah interval tertentu jika terjadi kesalahan
                setTimeout(checkLoginStatus, pollingInterval);
            }
        });
    }

    // Panggil fungsi checkLoginStatus untuk pertama kali saat halaman siap
    $(document).ready(function() {
        checkLoginStatus();
    });
</script>
</body>
</html>