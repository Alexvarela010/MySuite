<?php
/**
 * Contact Page - My Suite In Cartagena
 * Internationalized version
 */

session_start();

require_once __DIR__ . '/includes/i18n/i18n.php';

// Detectar idioma desde parámetro GET o ruta
$lang = 'es'; // Por defecto
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es', 'it'])) {
    $lang = $_GET['lang'];
} else {
    // Detectar desde la ruta
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($scriptPath, '/es/') !== false) {
        $lang = 'es';
    } elseif (strpos($scriptPath, '/it/') !== false) {
        $lang = 'it';
    } elseif (strpos($scriptPath, '/en/') !== false) {
        $lang = 'en';
    }
}

// Inicializar sistema de internacionalización
I18n::init($lang);

// Obtener información del usuario si está logueado
$user_logged_in = isset($_SESSION['user_id']);
$user_name = $user_logged_in ? ($_SESSION['user_name'] ?? 'Usuario') : '';
$user_role = $user_logged_in ? ($_SESSION['user_role'] ?? 'user') : '';

$currentLang = I18n::getLanguage();
// Determinar la ruta del index según el idioma
$index_path = 'index.php?lang=' . $currentLang;
?>
<!--
Author: W3layouts
Author URL: http://w3layouts.com
License: Creative Commons Attribution 3.0 Unported
License URL: http://creativecommons.org/licenses/by/3.0/
-->

<!DOCTYPE html>
<html lang="zxx">

<head>
<link rel="shortcut icon" href="<?php echo I18n::sharedAsset('favicon.png'); ?>"/>
	<title>My Suite In Cartagena - <?php echo __('contact.title'); ?></title>
	<!-- Meta tag Keywords -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8" />
	<meta name="keywords" content="Villas Responsive web template, Bootstrap Web Templates, Flat Web Templates, Android Compatible web template, Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
	<script>
		addEventListener("load", function () {
			setTimeout(hideURLbar, 0);
		}, false);

		function hideURLbar() {
			window.scrollTo(0, 1);
		}
	</script>
	<!-- //Meta tag Keywords -->

	<!-- Custom-Files -->
	<link rel="stylesheet" href="<?php echo I18n::cssPath('bootstrap.css'); ?>">
	<!-- Bootstrap-Core-CSS -->
	<link rel="stylesheet" href="<?php echo I18n::cssPath('style.css'); ?>" type="text/css" media="all" />
	<!-- Style-CSS -->
	<link href="<?php echo I18n::cssPath('font-awesome.min.css'); ?>" rel="stylesheet">
	<!-- Font-Awesome-Icons-CSS -->
	<!-- //Custom-Files -->
	
	<!-- Estilos para imágenes del banner principal -->
	<style>
		/* Imagen de fondo para banner_w3lspvt-2 (páginas internas) */
		.banner_w3lspvt-2 {
			background-image: url('<?php echo I18n::sharedAsset('3.webp'); ?>') !important;
			background-size: cover !important;
			background-position: center !important;
			background-repeat: no-repeat !important;
		}
	</style>

	<!-- Web-Fonts -->
	<link href="//fonts.googleapis.com/css?family=Crimson+Text:400,400i,600,600i,700,700i" rel="stylesheet">
	<link href="//fonts.googleapis.com/css?family=Oxygen:300,400,700&amp;subset=latin-ext" rel="stylesheet">
	<!-- //Web-Fonts -->
</head>

<body>
	<!-- main banner -->
	<div class="main-top" id="home">
		<!-- header -->
		<header>
			<div class="container-fluid">
				<div class="header d-md-flex justify-content-between align-items-center py-3 px-xl-5 px-lg-3 px-2">
					<!-- logo -->
					<div id="logo">
						<h1><a href="<?php echo $index_path; ?>">My Suite In Cartagena</a></h1>
					</div>
					<!-- //logo -->
					<!-- nav -->
					<div class="nav_w3ls">
						<nav>
							<label for="drop" class="toggle">Menu</label>
							<input type="checkbox" id="drop" />
							<ul class="menu">
								<li><a href="<?php echo $index_path; ?>"><?php echo __('nav.home'); ?></a></li>
								<li><a href="elapto.php"><?php echo __('nav.apartment'); ?></a></li>
								<li><a href="lasinstalaciones.php"><?php echo __('nav.facilities'); ?></a></li>
								<li><a href="tarifas.php"><?php echo __('nav.rates'); ?></a></li>
								<li><a href="contactenos.php" class="active"><?php echo __('nav.contact'); ?></a></li>

								<?php 
								// Mostrar banderas de otros idiomas (no el actual)
								if ($currentLang !== 'es'): ?>
								<li><a href="contactenos.php?lang=es"><img src="<?php echo I18n::sharedAsset('bcolombia.png'); ?>" target="self"></a></li>
								<?php endif; ?>
								<?php if ($currentLang !== 'en'): ?>
								<li><a href="contactenos.php?lang=en"><img src="<?php echo I18n::sharedAsset('busa.png'); ?>" target="self"></a></li>
								<?php endif; ?>
								<?php if ($currentLang !== 'it'): ?>
								<li><a href="contactenos.php?lang=it"><img src="<?php echo I18n::sharedAsset('bitalia.png'); ?>" target="self"></a></li>
								<?php endif; ?>
							</ul>
						</nav>
					</div>
					<!-- //nav -->
				</div>
			</div>
		</header>
		<!-- //header -->

		<!-- banner -->
		<div class="banner_w3lspvt-2">

		</div>
		<!-- //banner -->
	</div>
	<!-- //main banner -->

	<!-- page details -->
	<div class="breadcrumb-mobamu">
		<ol class="breadcrumb">
			<li class="breadcrumb-item">
				<a href="<?php echo $index_path; ?>"><?php echo __('nav.home'); ?></a>
			</li>
			<li class="breadcrumb-item active" aria-current="page"><?php echo __('contact.breadcrumb'); ?></li>
		</ol>
	</div>
	<!-- //page details -->

	<!-- contact -->
	<section class="contact py-5" id="contact">
		<div class="container py-xl-5 py-lg-3">
			<h3 class="tittle text-center text-bl font-weight-bold"><?php echo __('contact.title'); ?></h3>
			<ul class="list-unstyled row text-left pt-4 mb-lg-5">
				<li class="col-lg-4 adress-info mb-lg-0 mb-5">
					<div class="row">
						<div class="col-3 text-lg-center text-sm-right text-center adress-icon">
							<span class="fa fa-map-marker"></span>
						</div>
						<div class="col-9 text-left">
							<h6><?php echo __('contact.location'); ?></h6>
							<p><?php echo __('contact.location_text'); ?></p>
						</div>
					</div>
				</li>
				<li class="col-lg-4 adress-info mb-lg-0 mb-5">
					<div class="row">
						<div class="col-3 text-lg-center text-sm-right text-center adress-icon">
							<span class="fa fa-envelope-open-o"></span>
						</div>
						<div class="col-9 text-left">
							<h6><?php echo __('contact.email'); ?></h6>
							<a>mysuiteincartagena@gmail.com</a>
							<br>
						</div>
					</div>
				</li>
				<li class="col-lg-4 adress-info mb-lg-0 mb-5">
					<div class="row">
						<div class="col-3 text-lg-center text-sm-right text-center adress-icon">
							<span class="fa fa-phone"></span>
						</div>
						<div class="col-9 text-left">
							<h6><?php echo __('contact.phone'); ?></h6>
							<p><?php echo __('contact.phone_text'); ?></p>
						</div>
					</div>
				</li>
			</ul>

<center><!-- Map -->
					<div class="map-fo">
					<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3924.335428131712!2d-75.5641105754294!3d10.394911116115894!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8ef62f3cb2f5168f%3A0xebc198b96f1bdf8d!2sTorres%20Del%20Lago!5e0!3m2!1ses!2sco!4v1735315900004!5m2!1ses!2sco" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
					</div>
					<!-- //Map --></center>
		</div>
	</section>
	<!-- //contact -->


	<!-- footer -->
	<footer class="py-5">
		<div class="container pt-xl-4">
			<div class="row footer-top">
				
				<div class="col-lg-3 col-md-6 footer-grid_section_1its mt-lg-0 mt-4">
					<!-- social icons -->
					<div class="mobamuinfo_social_icons">
						
						<h3 class="sub-con-fo text-li my-4"><?php echo __('contact.social_networks'); ?></h3>
												<ul class="mobamuits_social_list list-unstyled">
							<li class="w3_mobamu_facebook">
								<a href="https://www.facebook.com/profile.php?id=61569691859314" target="_blank">
									<span class="fa fa-facebook-f"></span>
								</a>
							</li>
							<li class="w3_mobamu_dribble">
								<a href="https://www.instagram.com/mysuiteincartagena/" target="_blank">
									<span class="fa fa-instagram"></span>
								</a>
								<li>
								<a href="https://wa.me/+573015193163" target="_blank">
									<img src="<?php echo I18n::sharedAsset('whatsapp.webp'); ?>" width="35" height="35" >
								</a>
							</li>
							</li>
						</ul>
					</div>
					<!-- social icons -->
				</div>
			</div>
		</div>
	</footer>
	<!-- //footer -->
	<!-- copyright -->
	<div >
		<center><p><?php echo __('contact.copyright'); ?>
		</p></center>
	</div>
	<div class="cpy-right text-center py-3">
<p><?php echo __('contact.rights'); ?>
		</p>
	</div>
	<!-- //copyright -->

	<!-- move top icon -->
	<a href="#home" class="move-top text-center"></a>
	<!-- //move top icon -->

</body>

</html>

