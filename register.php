<?php
$page_title = "Registrasi";
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
    $fullname = $user_data['fullname'];
    $nim = $user_data['nim'];
    $password = $user_data['password'];
    $email = $user_data['email'];
    $role = $user_data['role'];
    $photo = $user_data['picture'];

    if (!empty($nim) && !empty($password)) {
        header('location: index.php');
        exit;
    }
} else {
    // Tampilkan pesan jika data pengguna tidak ditemukan
    header('location: logout.php');
    exit;
}

// Memeriksa apakah form sudah disubmit
if (isset($_POST['submit'])) {
    // Ambil data dari form
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']); // Menghindari SQL Injection
    $new_nim = mysqli_real_escape_string($conn, $_POST['nim']); // Menghindari SQL Injection
    $new_password = mysqli_real_escape_string($conn, $_POST['password']); // Menghindari SQL Injection
    $delete_photo = $_POST['delete_photo'] === '1';

    // Periksa apakah NIM baru sudah ada di database
    $query_check_nim = 'SELECT * FROM tb_users WHERE nim = "' . $new_nim . '" AND oauth_id != "' . $_SESSION['oauth_id'] . '"';
    $run_query_check_nim = mysqli_query($conn, $query_check_nim);
    $existing_user = mysqli_fetch_assoc($run_query_check_nim);

    if ($existing_user) {
        // Jika NIM sudah ada, tampilkan pesan error
        $error_message = "NIM/Username sudah terdaftar. Harap gunakan NIM/Username yang berbeda atau Hubungi Administrator untuk Mendapatkan NIM.";
    } else {
        $update_photo = false;

        // Prioritaskan foto baru jika ada, meskipun user menekan tombol hapus sebelumnya
        if (isset($_POST['cropped_photo']) && !empty($_POST['cropped_photo'])) {
            $cropped_photo_data = $_POST['cropped_photo'];
            $cropped_photo_data = str_replace('data:image/jpeg;base64,', '', $cropped_photo_data);
            $cropped_photo_data = base64_decode($cropped_photo_data);
            $photo_path = 'profile-picture/' . $new_nim . '.jpg';
            file_put_contents($photo_path, $cropped_photo_data);

            // Update data ke database dengan foto baru
            $query_update = 'UPDATE tb_users SET fullname = "' . $fullname . '", nim = "' . $new_nim . '", password = "' . $new_password . '", picture = "' . $photo_path . '" WHERE oauth_id = "' . $_SESSION['oauth_id'] . '"';
            $update_photo = true;
        } elseif ($delete_photo) {
            // Hapus foto yang ada dari directory
            if (file_exists($user_data['picture'])) {
                unlink($user_data['picture']);
            }

            // Update data ke database tanpa foto (kosongkan kolom picture)
            $query_update = 'UPDATE tb_users SET fullname = "' . $fullname . '", nim = "' . $new_nim . '", password = "' . $new_password . '", picture = NULL WHERE oauth_id = "' . $_SESSION['oauth_id'] . '"';
            $update_photo = true;
        } else {
            // Update data ke database tanpa mengubah foto
            $query_update = 'UPDATE tb_users SET fullname = "' . $fullname . '", nim = "' . $new_nim . '", password = "' . $new_password . '" WHERE oauth_id = "' . $_SESSION['oauth_id'] . '"';
        }

        if ($update_photo) {
            $run_query_update = mysqli_query($conn, $query_update);

            if ($run_query_update) {
                // Arahkan kembali ke halaman profile.php setelah update berhasil
                header('location: profile.php');
                exit;
            } else {
                // Simpan pesan error jika gagal
                $error_message = "Gagal menyimpan data. Silakan coba lagi.";
            }
        } else {
            // Tidak ada perubahan pada foto, hanya update data lainnya
            $query_update = 'UPDATE tb_users SET fullname = "' . $fullname . '", nim = "' . $new_nim . '", password = "' . $new_password . '" WHERE oauth_id = "' . $_SESSION['oauth_id'] . '"';
            $run_query_update = mysqli_query($conn, $query_update);

            if ($run_query_update) {
                // Arahkan kembali ke halaman index.php setelah update berhasil
                header('location: index.php');
                exit;
            } else {
                // Simpan pesan error jika gagal
                $error_message = "Gagal menyimpan data. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="card-title text-center mb-4">Registrasi Pengguna</h2>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group row">
                    <div class="col-md-4">
                    <div class="user-info-container">
                        <label for="photo">Foto Profil:</label>
                        <div class="d-flex flex-column align-items-center">
                            <img id="previewPhoto" src="<?php echo $photo ? $photo : 'assets/img/default.png'; ?>" alt="Profile Picture" class="pp-profile-picture-edit mb-3">
                            <div class="input-group" hidden>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="photo" name="photo" accept=".jpg" onchange="validatePhoto(this)">
                                    <label class="custom-file-label" for="photo">Pilih foto...(.jpg)</label>
                                </div>
                            </div>
                            <button class="btn btn-outline-secondary mt-2" type="button" id="defaultPhoto">Default</button>
                            <button class="btn btn-danger mt-2" type="button" id="deletePhoto">Hapus Foto</button>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <button type="submit" class="btn btn-primary mt-5" name="submit">Simpan Perubahan</button>
                        </div>
                    </div>
                    <div class="col-md-8">
                    <div class="user-info-container">
                        <div class="form-group">
                            <label for="fullname">Nama Lengkap :</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" name="fullname" id="fullname" value="<?php echo $fullname; ?>" placeholder="Nama Lengkap" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email :</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" name="email" id="email" value="<?php echo $email; ?>" placeholder="Email" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nim">NIM/Username :</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                                </div>
                                <input type="text" class="form-control" name="nim" id="nim" value="<?php echo $nim; ?>" placeholder="NIM/Username" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password :</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" name="password" id="password" value="<?php echo $password; ?>" placeholder="Password" required>
                                <div class="input-group-append">
                                    <button type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="passwordIcon" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                <input type="hidden" name="cropped_photo" id="cropped_photo">
                <input type="hidden" name="delete_photo" id="delete_photo" value="0">
            </form>
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

<!-- Modal untuk crop gambar -->
<div  class="modal fade"  id="cropModal"  tabindex="-1"  role="dialog"  aria-labelledby="cropModalLabel"  aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div  class="modal-dialog"  role="document">
		<div  class="modal-content">
			<div  class="modal-header">
				<h5  class="modal-title"  id="cropModalLabel">Crop Gambar</h5>
				<button  type="button"  class="close"  data-dismiss="modal"  aria-label="Close">
					<span  aria-hidden="true">&times;</span>
				</button>
			</div>
			<div  class="modal-body">
				<div  class="img-container">
					<img  id="imageToCrop"  src="#">
				</div>
			</div>
			<div  class="modal-footer">
				<button  type="button"  class="btn btn-secondary"  data-dismiss="modal">Batal</button>
				<button  type="button"  class="btn btn-primary"  id="cropButton">Crop</button>
			</div>
		</div>
	</div>
</div>

<script>
    var cropper;
    function validatePhoto(input) {
        var allowedExtensions = /(\.jpg|\.)$/i; // Ekstensi file yang diizinkan
        if (!allowedExtensions.exec(input.value)) {
            alert('Hanya file dengan ekstensi .jpg yang diizinkan!');
            input.value = ''; // Hapus nilai input
            return false;
        } else {
            var file = input.files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#cropModal').modal('show');
                var image = document.getElementById('imageToCrop');
                image.src = e.target.result;

                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    preview: '.preview',
                    minContainerWidth: 300,
                    minContainerHeight: 300,
                });
                
                // Reset status penghapusan foto
                document.getElementById('delete_photo').value = '0';
            }
            reader.readAsDataURL(file);
        }
    }

    $('#cropButton').on('click', function () {
        var canvas = cropper.getCroppedCanvas({
            width: 200,
            height: 200,
        });
        var croppedPhoto = canvas.toDataURL('image/jpeg');
        document.getElementById('cropped_photo').value = croppedPhoto;

        $('#previewPhoto').attr('src', croppedPhoto);
        $('#cropModal').modal('hide');
    });

    $(document).ready(function(){
        $('#errorModal').modal('show');
    });

    document.getElementById('defaultPhoto').addEventListener('click', function () {
        document.getElementById('photo').value = '';
        document.getElementById('cropped_photo').value = '';
        document.getElementById('previewPhoto').src = "<?php echo $photo ? $photo : 'assets/img/default.png'; ?>";

        // Reset status penghapusan foto
        document.getElementById('delete_photo').value = '0';
    });

    document.getElementById('deletePhoto').addEventListener('click', function () {
        document.getElementById('photo').value = '';
        document.getElementById('cropped_photo').value = '';
        document.getElementById('previewPhoto').src = 'assets/img/default.png';
        document.getElementById('delete_photo').value = '1';
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

    document.getElementById('previewPhoto').addEventListener('click', function () {
        document.getElementById('photo').click();
    });

    document.getElementById('previewPhoto').onerror = function () {
        this.onerror = null;
        this.src = 'assets/img/photo-error.png';
        this.alt = 'Default Profile Picture';
    };
</script>
</body>
</html>