<?php
/**
 * Rates Page - My Suite In Cartagena
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
	<title>My Suite In Cartagena - <?php echo __('rates.title'); ?></title>
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
	<link href="<?php echo I18n::cssPath('single.css'); ?>" rel='stylesheet' type='text/css' />
	<!-- single page -->
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
		
		/* Imágenes de fondo para cada slide del carrusel */
		.banner_w3lspvt .csslider > ul > li:first-child {
			background-image: url('<?php echo I18n::sharedAsset('1.webp'); ?>') !important;
		}
		
		.banner_w3lspvt .csslider > ul > li:nth-child(2) {
			background-image: url('<?php echo I18n::sharedAsset('2.webp'); ?>') !important;
		}
		
		.banner_w3lspvt .csslider > ul > li:nth-child(3) {
			background-image: url('<?php echo I18n::sharedAsset('3.webp'); ?>') !important;
		}
		
		.banner_w3lspvt .csslider > ul > li:nth-child(4) {
			background-image: url('<?php echo I18n::sharedAsset('4.webp'); ?>') !important;
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
								<li><a href="tarifas.php" class="active"><?php echo __('nav.rates'); ?></a></li>
								<li><a href="contactenos.php"><?php echo __('nav.contact'); ?></a></li>

								<?php 
								// Mostrar banderas de otros idiomas (no el actual)
								if ($currentLang !== 'es'): ?>
								<li><a href="tarifas.php?lang=es"><img src="<?php echo I18n::sharedAsset('bcolombia.png'); ?>" target="self"></a></li>
								<?php endif; ?>
								<?php if ($currentLang !== 'en'): ?>
								<li><a href="tarifas.php?lang=en"><img src="<?php echo I18n::sharedAsset('busa.png'); ?>" target="self"></a></li>
								<?php endif; ?>
								<?php if ($currentLang !== 'it'): ?>
								<li><a href="tarifas.php?lang=it"><img src="<?php echo I18n::sharedAsset('bitalia.png'); ?>" target="self"></a></li>
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
			<li class="breadcrumb-item active" aria-current="page"><?php echo __('rates.breadcrumb'); ?></li>
		</ol>
	</div>
	<!-- //page details -->

	<!-- single -->
	


	<!-- price -->
	<div class="rooms-w3ls bg-li py-5" id="price">
		<div class="container-fluid py-xl-5 py-lg-3">
			<h3 class="tittle text-center text-bl font-weight-bold"><?php echo __('rates.title'); ?></h3>
			<p class="sub-tittle text-center mt-2 mb-sm-5 mb-4 pb-xl-3"><?php echo __('rates.subtitle'); ?></p>
			<div class="row">
				<div class="col-lg-4 price-mobamus">
					<div class="price-top">
						<img src="<?php echo I18n::sharedAsset('price1.webp'); ?>" alt="" class="img-fluid" />
					</div>
					<div class="price-w3ls-bottom p-4">
						<strong><h4 class="my-2"><?php echo __('rates.high_season_a'); ?></h4></strong>
						<div class="lm-item-price">
							<h6>
							</h6>
						</div>
						<ul class="style-lists">
							<li><?php echo __('rates.high_season_a_dates'); ?></li>
							<li><?php echo __('rates.high_season_a_price_1_4'); ?>
 </li>
							<li><?php echo __('rates.high_season_a_price_5_6'); ?>
</li>
							<li><?php echo __('rates.high_season_a_price_7_8'); ?>
</li>
							<li><?php echo __('rates.high_season_a_bracelets_1_6'); ?>
</li>
						</ul>
					</div>
				</div>
				<div class="col-lg-4 price-mobamus my-lg-0 my-5">
					<div class="price-top">
						<img src="<?php echo I18n::sharedAsset('price2.webp'); ?>" alt="" class="img-fluid" />
					</div>
					<div class="price-w3ls-bottom p-4">
						<strong><h4 class="my-2"><?php echo __('rates.high_season_b'); ?></h4></strong>
						<div class="lm-item-price">
							<h6>
							</h6>
						</div>
						<ul class="style-lists">
							<li><?php echo __('rates.high_season_b_dates'); ?></li>
					<img src="<?php echo I18n::langAsset('festivos' . strtoupper($currentLang) . '.png'); ?>" width="400" height="215" border="0" alt="">
							<li><?php echo __('rates.high_season_b_price_1_2'); ?>
</li>
							<li><?php echo __('rates.high_season_b_price_3_6'); ?>
</li>
							<li><?php echo __('rates.high_season_b_price_7_8'); ?>

</li>
							<li><?php echo __('rates.high_season_b_bracelets_1_6'); ?>

</li>
						</ul>
					</div>
				</div>
				<div class="col-lg-4 price-mobamus">
					<div class="price-top">
							<img src="<?php echo I18n::sharedAsset('price3.webp'); ?>" alt="" class="img-fluid" />
					</div>
					<div class="price-w3ls-bottom p-4">
						<strong><h4 class="my-2"><?php echo __('rates.low_season'); ?></h4></strong>
						<div class="lm-item-price">
							<h6>
							</h6>
						</div>
						<ul class="style-lists">
							<li><?php echo __('rates.low_season_dates'); ?>
 </li>
							<li><?php echo __('rates.low_season_price_1_4'); ?>
</li>
							<li><?php echo __('rates.low_season_price_5_6'); ?>
</li>
							<li><?php echo __('rates.low_season_price_7_8'); ?>

</li>
							<li><?php echo __('rates.low_season_bracelets_1_6'); ?>


</li>
<br><br>
	<h4><p><center><strong><?php echo __('rates.cancellations'); ?></strong></center><br></p></h4>

	<p><center><strong><?php echo __('rates.cancellation_policy'); ?></strong>
<li><?php echo __('rates.cancellation_1_month'); ?> </li>
<li><?php echo __('rates.cancellation_2_weeks'); ?>
<li><?php echo __('rates.cancellation_48h'); ?></li>
</center></p>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- //price -->

	<!-- footer -->
	<footer class="py-5">
		<div class="container pt-xl-4">
			<div class="row footer-top">
				
				<div class="col-lg-3 col-md-6 footer-grid_section_1its mt-lg-0 mt-4">
					<!-- social icons -->
					<div class="mobamuinfo_social_icons">
						
						<h3 class="sub-con-fo text-li my-4"><?php echo __('rates.social_networks'); ?></h3>
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

	<!-- //copyright -->
	<div >
		<center><p><?php echo __('rates.copyright'); ?>
		</p></center>
	</div>
	<div class="cpy-right text-center py-3">
<p><?php echo __('rates.rights'); ?>

		</p>
	</div>
	<!-- //copyright -->

	<!-- move top icon -->
	<a href="#home" class="move-top text-center"></a>
	<!-- //move top icon -->

</body>

</html>

