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
$allowed_roles = array(1, 22); // Sesuaikan dengan peran yang diizinkan untuk sirak.php

// Periksa apakah pengguna memiliki peran yang diizinkan
if (!in_array($role, $allowed_roles)) {
    header('location: ../unauthorized.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <!--[if gt IE 8]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <![endif]-->
    <title>SIMPEG UNIVERSITAS ISLAM MAJAPAHIT</title>
   <link rel="icon" href="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/logo-unim.png" type="image/x-icon">
    
    <link href="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/bootstrap.min.css" rel="stylesheet">
    <link href="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/templatemo_misc.css">
    <link href="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/templatemo_style.css" rel="stylesheet">
      
    <script src="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/jquery-1.9.1.min.js"></script> 
	<script src="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/jquery.lightbox.js"></script>
	<script src="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/templatemo_custom.js"></script>

</head>
<body>
  	<div class="site-header">
		<div class="main-navigation">
			<div class="responsive_menu">
				<ul>
					<li><a class="show-1 templatemo_home" href="#">Presensi</a></li>
					<li><a class="show-2 templatemo_page2" href="https://siakad.unim.ac.id/">Beranda</a></li>
					<li><a class="show-3 templatemo_page3" href="https://siakad.unim.ac.id/simpeg">Login</a></li>
				</ul>
			</div>
			<div class="container">
				<div class="row templatemo_gallerygap">
					<div class="col-md-12 responsive-menu">
						<a href="#" class="menu-toggle-btn">
				            <i class="fa fa-bars"></i>
				        </a>
					</div> <!-- /.col-md-12 -->
                    <div class="col-md-1 col-sm-12">
                    	<a rel="nofollow" href="https://siakad.unim.ac.id/"><img src="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/logo-unim.png" style="width:100px;"></a>
                    </div>
                    <div class="col-md-6 col-sm-12" style="text-align: left;margin-top:30px;">
                        <span style="text-align: left;margin-left:30px;font-size: 25px;"><b>SISTEM INFORMASI PEGAWAI</b></span><br>
                        <span style="text-align: left;margin-left:30px;font-size: 15px;">UNIVERSITAS ISLAM MAJAPAHIT</span>
                    </div>
					<div class="col-md-5 main_menu">
						<ul>
							<li><a class="show-1 templatemo_home" href="#">
                            	<span class="fa fa-users"></span>
                                Presensi</a></li>
							<li><a href="https://siakad.unim.ac.id/">
                            	<span class="fa fa-th-large"></span>
                          		  Beranda</a></li>
							<li><a href="https://siakad.unim.ac.id/simpeg">
                            	<span class="fa fa-key"></span>
                            	Login</a></li>
						</ul>
					</div> <!-- /.col-md-12 -->
				</div> <!-- /.row -->
			</div> <!-- /.container -->
		</div> <!-- /.main-navigation -->
	</div> <!-- /.site-header -->
    <div id="menu-container">
    <!-- contact start -->
    <div class="content homepage" id="menu-1">
    <div class="container">
             	<div class="row">
            <div class="col-md-4 col-sm-12">
            	<div class="templatemo_contactmap">
              <div id="peg">
                <img src="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/tidak-ada-foto-pegawai.png" style="width: 250px;">
              </div>
                <img src="SIMPEG%20UNIVERSITAS%20ISLAM%20MAJAPAHIT_files/templatemo_contactiframe_kotak.png" style="width: 250px;height:292px;">
                </div>
                </div>
            <div class="col-md-8 col-sm-12 leftalign">
                
            	<form role="form" id="validate" class="form no-margin formPresensi" accept-charset="utf-8" method="post" action="">
              	<div class="templatemo_form">
                	<div class="templatemo_contacttitle" style="margin-top:15px;">Presensi</div>
                    <div class="col-md-7 col-sm-12"><input autofocus="" type="text" name="presensi" id="presensi" class="form-control" placeholder="Your ID" maxlength="40"></div>
              	</div>
            </form>
            	
                <div class="clear"></div>
                <div id="detail">
                </div>
            </div>



<script type="text/javascript">

$(".formPresensi").submit(function(event){
    var key = $("#presensi").val();
     var data = $(this).serialize();
    $.ajax({
      url: "https://siakad.unim.ac.id/simpeg/user/simpegProses",
      type: "POST",
      cache: false,
      data: data,
      success : function(msg) {
      // alert(msg);
                $("#detail").html(msg).show()
                 $("#presensi").attr("disabled","disabled");
                 //$("#tombol").attr("disabled","disabled"); 
                 gantiGambar(key);

                 setTimeout(
                  function()
                 {
                    window.location.href = "https://siakad.unim.ac.id/simpeg/user";
                 }, 
                 3000
                 );
                
            },
      error : function() {
                $('#detail').replaceWith('Error');
            }
    })
        return false;

  });
function gantiGambar(key){
   $.ajax( {
            type :"POST",
            url: "https://siakad.unim.ac.id/simpeg/user/changeImage/"+key,
            cache :false,
            success : function(msgw) {
              //alert(msgw);
              if(msgw){
                $('#peg').replaceWith("<img src=https://siakad.unim.ac.id/upload/foto_pegawai/"+msgw+".jpg style='width: 250px;height:292px;'>");
              }
              else
              {
                $("#peg").replaceWith("<img src=https://siakad.unim.ac.id/css-js-simpeg/images/tidak-ada-foto-pegawai.png style='width: 250px;'>"); 
              }
            },
            error : function() {
                $("#peg").replaceWith("<img src=https://siakad.unim.ac.id/css-js-simpeg/images/tidak-ada-foto-pegawai.png style='width: 250px;'>");
            }
        });
    return false;
}

</script>
        	
        </div>
    	
    </div>
    </div>
    </div>
    <!-- contact end -->
	<!-- footer start -->
    <div class="templatemo_footer">
        
    	<div class="container">
    	<div class="row">
        	<div class="col-md-9 col-sm-12">Copyright 2014 © UNIVERSITAS ISLAM MAJAPAHIT</div>
        </div>
        </div>
    </div>
    <!-- footer end --> 
  

</body></html>