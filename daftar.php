<?php
session_start();

if(isset($_SESSION['logged_in'])){
    header('location: index.php');
    exit;
}

// Membuat koneksi ke database
$conn = mysqli_connect('localhost', 'root', '', 'db_login_google') or die ('Gagal tersambung');

if(isset($_POST['submit'])){
    // Ambil data dari form
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']); // Menghindari SQL Injection
    $email = mysqli_real_escape_string($conn, $_POST['email']); // Menghindari SQL Injection
    $nim = mysqli_real_escape_string($conn, $_POST['nim']); // Menghindari SQL Injection
    $password = mysqli_real_escape_string($conn, $_POST['password']); // Menghindari SQL Injection

    // Cek apakah email sudah ada di database
    $query_check_email = 'SELECT * FROM tb_users WHERE email = "'.$email.'"';
    $run_query_check_email = mysqli_query($conn, $query_check_email);
    $existing_email = mysqli_fetch_assoc($run_query_check_email);

    // Cek apakah NIM sudah ada di database
    $query_check_nim = 'SELECT * FROM tb_users WHERE nim = "'.$nim.'"';
    $run_query_check_nim = mysqli_query($conn, $query_check_nim);
    $existing_nim = mysqli_fetch_assoc($run_query_check_nim);

    // Jika email atau NIM sudah ada di database, tampilkan pesan kesalahan
    if ($existing_email && $existing_nim) {
        $error_message = "Email dan Username telah terdaftar. Harap gunakan Email atau Username yang berbeda.";
    } elseif ($existing_email) {
        // Jika hanya email yang sudah ada di database
        $error_message = "Email telah terdaftar. Harap gunakan email yang berbeda.";
    } elseif ($existing_nim) {
        $error_message = "Username telah terdaftar. Harap gunakan Username yang berbeda.";
    } else {
        // Proses upload foto
        $photo_path = '';
        if(isset($_FILES['photo']) && $_FILES['photo']['name']){
            $photo_data = $_POST['cropped_photo'];
            list($type, $photo_data) = explode(';', $photo_data);
            list(, $photo_data)      = explode(',', $photo_data);
            $photo_data = base64_decode($photo_data);
            
            // Nama file baru berdasarkan NIM
            $new_photo_name = $nim . '.jpg';
            
            // Path lengkap untuk foto
            $photo_path = 'profile-picture/' . $new_photo_name;
            
            file_put_contents($photo_path, $photo_data);
        }

        // Stempel waktu untuk last login di database
        $currtime = date('Y-m-d H:i:s');

        // Insert data ke database
        $query_insert = 'INSERT INTO tb_users (fullname, email, nim, password, role, oauth_id, picture, last_login) VALUES ("'.$fullname.'", "'.$email.'", "'.$nim.'", "'.$password.'", "'."22".'", "'."".'", "'.$photo_path.'", "'.$currtime.'")';
        $run_query_insert = mysqli_query($conn, $query_insert);

        if($run_query_insert){
            // Set session untuk login
            $_SESSION['logged_in'] = true;
            $_SESSION['uname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['nim'] = $nim;
            $_SESSION['picture'] = $photo_path;
            // Arahkan ke halaman index.php setelah registrasi berhasil
            header('location: index.php');
        } else {
            // Tampilkan pesan error jika gagal
            $error_message = "Gagal menyimpan data. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script  src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="card-title text-center mb-4">Daftar Pengguna</h2>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group row">
                    <div class="col-md-4">
                    <div class="user-info-container">
                        <label for="photo">Foto Profil:</label>
                        <div class="d-flex flex-column align-items-center">
                            <img id="previewPhoto" src="assets/img/default.png" alt="Profile Picture" class="pp-profile-picture-edit mb-3">
                            <div class="input-group" hidden>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="photo" name="photo" accept=".jpg" onchange="validatePhoto(this)">
                                    <label class="custom-file-label" for="photo">Pilih foto...(.jpg)</label>
                                </div>
                            </div>
                            <button class="btn btn-danger mt-2" type="button" id="deletePhoto">Hapus Foto</button>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <button type="submit" class="btn btn-primary mt-5" name="submit">Daftar</button>
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
                                <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Nama Lengkap" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email :</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nim">NIM/Username :</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                                </div>
                                <input type="text" class="form-control" name="nim" id="nim" placeholder="NIM/Username" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password :</label>
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
                    </div>
                </div>
                </div>
                <input type="hidden" name="cropped_photo" id="cropped_photo">
            </form>
        </div>
    </div>
</div>

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
					<img  id="cropImage"  src="#">
				</div>
			</div>
			<div  class="modal-footer">
				<button  type="button"  class="btn btn-secondary"  data-dismiss="modal">Batal</button>
				<button  type="button"  class="btn btn-primary"  id="cropButton">Crop</button>
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
    
    <script>
        let cropper;
        const photoInput = document.getElementById('photo');
        const previewPhoto = document.getElementById('previewPhoto');
        const cropImage = document.getElementById('cropImage');
        const cropModal = $('#cropModal');

        document.getElementById('previewPhoto').addEventListener('click', function () {
            document.getElementById('photo').click();
        });

        photoInput.addEventListener('change', function(event) {
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    cropImage.src = e.target.result;
                    cropModal.modal({
                        backdrop: 'static', // Mencegah modal tertutup saat mengklik di luar
                        keyboard: false // Mencegah modal tertutup saat menekan tombol Escape
                    });
                };
                reader.readAsDataURL(event.target.files[0]);
            }
        });

        cropModal.on('shown.bs.modal', function() {
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                preview: '.preview',
                minContainerWidth: 300,
                minContainerHeight: 300,
            });
        }).on('hidden.bs.modal', function() {
            cropper.destroy();
            cropper = null;
        });

        document.getElementById('cropButton').addEventListener('click', function() {
            const canvas = cropper.getCroppedCanvas({
                width: 200,
                height: 200,
            });
            previewPhoto.src = canvas.toDataURL('image/jpeg');
            document.getElementById('cropped_photo').value = canvas.toDataURL('image/jpeg');
            cropModal.modal('hide');
        });

        // Fungsi untuk memvalidasi jenis file yang diunggah
        function validatePhoto(input) {
            var allowedExtensions = /(\.jpg|\.)$/i; // Ekstensi file yang diizinkan
            if (!allowedExtensions.exec(input.value)) {
                alert('Hanya file dengan ekstensi .jpg yang diizinkan!');
                input.value = ''; // Hapus nilai input
                return false;
            } else {
                updatePhotoLabel(input);
            }
        }

        // Fungsi untuk mengubah teks pada label foto ketika dipilih
        function updatePhotoLabel(input) {
            var photoLabel = input.nextElementSibling;
            var fileName = input.files[0].name;
            photoLabel.innerHTML = fileName;

            // Tampilkan preview foto yang diunggah oleh pengguna
            var previewPhoto = document.getElementById("previewPhoto");
            if (previewPhoto && input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var img = new Image();
                    img.src = e.target.result;
                    img.onload = function() {
                        var canvas = document.createElement("canvas");
                        var ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0);

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
                        var ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0, width, height);

                        previewPhoto.src = canvas.toDataURL("image/jpeg");
                        previewPhoto.style.display = "block";
                    };
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(function(){
            $('#errorModal').modal('show');
        });

        // Fungsi untuk menghapus pilihan foto
        document.getElementById("deletePhoto").addEventListener("click", function() {
            var photoInput = document.getElementById("photo");
            photoInput.value = ""; // Clear the input value
            var photoLabel = photoInput.nextElementSibling;
            photoLabel.innerHTML = "Pilih foto...(.jpg)"; // Reset the label text
            var previewPhoto = document.getElementById("previewPhoto");
            previewPhoto.src = "assets/img/default.png";
            previewPhoto.style.display = "block";
        });

        document.getElementById("togglePassword").addEventListener("click", function() {
        var passwordInput = document.getElementById("password");
        var passwordIcon = document.getElementById("passwordIcon");
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            passwordIcon.className = "fas fa-eye-slash";
            passwordInput.classList.add("shown"); // Tambahkan class 'shown' ke input password
        } else {
            passwordInput.type = "password";
            passwordIcon.className = "fas fa-eye";
            passwordInput.classList.remove("shown"); // Hapus class 'shown' dari input password
        }
    });
    </script>
</body>
</html>
