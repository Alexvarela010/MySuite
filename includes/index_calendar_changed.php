<?php
// Asegurar que $descuentos siempre esté definido
if (!isset($descuentos) || !is_array($descuentos)) {
    $descuentos = [
        'fidelidad' => ['porcentaje' => 5.0, 'activo' => false],
        'cumpleanos' => ['porcentaje' => 30.0, 'activo' => false],
        'promocional' => ['porcentaje' => 3.0, 'activo' => true]
    ];
}
// Asegurar que $user_data siempre esté definido como null si no existe
if (!isset($user_data)) {
    $user_data = null;
}
// Asegurar que $user_logged_in siempre esté definido
if (!isset($user_logged_in)) {
    $user_logged_in = false;
}
// Asegurar que $occupied_dates siempre esté definido
if (!isset($occupied_dates) || !is_array($occupied_dates)) {
    $occupied_dates = [];
}
// Asegurar que $base_price siempre esté definido
if (!isset($base_price)) {
    $base_price = 200000;
}
// Asegurar que $total_usuarios siempre esté definido
if (!isset($total_usuarios)) {
    $total_usuarios = 0;
}
// Asegurar que $ultimo_usuario_id siempre esté definido
if (!isset($ultimo_usuario_id)) {
    $ultimo_usuario_id = null;
}
// Suprimir warnings que puedan aparecer antes del JSON
$old_error_reporting = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
$old_display_errors = ini_get('display_errors');
ini_set('display_errors', 0);
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
	<title>My Suite In Cartagena</title>
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
	<link href="<?php echo I18n::cssPath('css_slider.css'); ?>" type="text/css" rel="stylesheet" media="all">
	<!-- banner slider -->
	<link rel="stylesheet" href="<?php echo I18n::cssPath('style.css'); ?>" type="text/css" media="all" />
	<!-- Style-CSS -->
	<link href="<?php echo I18n::cssPath('font-awesome.min.css'); ?>" rel="stylesheet">
	<!-- Font-Awesome-Icons-CSS -->
	<!-- //Custom-Files -->

	<!-- Web-Fonts -->
	<link href="//fonts.googleapis.com/css?family=Crimson+Text:400,400i,600,600i,700,700i" rel="stylesheet">
	<link href="//fonts.googleapis.com/css?family=Oxygen:300,400,700&amp;subset=latin-ext" rel="stylesheet">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Abril+Fatface|Poppins">
	<!-- Fuente Migra -->
	<style>
		@font-face {
			font-family: 'Migra';
			src: url('../assets/shared/fonts/Migra-Regular.otf') format('opentype'),
			     url('../assets/shared/fonts/Migra-Regular.ttf') format('truetype');
			font-weight: normal;
			font-style: normal;
		}
		@font-face {
			font-family: 'Migra';
			src: url('../assets/shared/fonts/Migra-Bold.otf') format('opentype'),
			     url('../assets/shared/fonts/Migra-Bold.ttf') format('truetype');
			font-weight: bold;
			font-style: normal;
		}
		
		/* Fuente Calvino Grande Bold */
		@font-face {
			font-family: 'Calvino Grande Bold';
			src: url('../assets/shared/fonts/Calvino-Grande-Bold.otf') format('opentype'),
			     url('../assets/shared/fonts/Calvino-Grande-Bold.ttf') format('truetype'),
			     url('../assets/shared/fonts/CalvinoGrandeBold.otf') format('opentype'),
			     url('../assets/shared/fonts/CalvinoGrandeBold.ttf') format('truetype');
			font-weight: bold;
			font-style: normal;
		}
		@font-face {
			font-family: 'Migra';
			src: url('../assets/shared/fonts/Migra-Extrabold.otf') format('opentype'),
			     url('../assets/shared/fonts/Migra-Extrabold.ttf') format('truetype');
			font-weight: 800;
			font-style: normal;
		}
	</style>
	<!-- //Web-Fonts -->
</head>

<body>
	<!-- main banner -->
	<div class="main-top" id="home">
		<!-- header -->
		<header>
			<label for="drop" class="toggle mobile-toggle">
                                <img src="<?php echo I18n::sharedAsset('menu.webp'); ?>" alt="Menu" class="menu-icon">
			</label>
			<input type="checkbox" id="drop" />
			<div class="container-fluid">
				<div class="header d-md-flex justify-content-between align-items-center py-3 px-xl-5 px-lg-3 px-2">
					<!-- logo -->
					<div id="logo">
						<h1><a href="index.php">My Suite In Cartagena</a></h1>
					</div>
					<!-- //logo -->
					
					<!-- //Language Selector -->
					<!-- nav -->
					<div class="nav_w3ls">
						<nav>
							<ul class="menu">
								<li><a href="index.php" class="active"><?php echo __('nav.home'); ?></a></li>
								<li><a href="../elapto.php"><?php echo __('nav.apartment'); ?></a></li>
								<li><a href="../lasinstalaciones.php"><?php echo __('nav.facilities'); ?></a></li>
								<li><a href="../tarifas.php"><?php echo __('nav.rates'); ?></a></li>
								<!-- <li><a href="../tarifas.php">Testimonios</a></li> -->
								<li><a href="../contactenos.php"><?php echo __('nav.contact'); ?></a></li>
								<?php if ($user_logged_in): ?>
									<?php if ($user_role === 'admin'): ?>
										<li><a href="../admin/index.php" style="color:rgb(255, 255, 255); ">
											 Panel
										</a></li>
									<?php endif; ?>
									<li class="dropdown">
										<a href="#" class="dropdown-toggle" id="profileDropdown" style="color: #333; font-weight: bold; cursor: pointer;">
											<?php echo __('nav.hello'); ?> <?php 
												// Mostrar solo el nombre (primera palabra)
												$nombre_solo = explode(' ', $user_name)[0];
												echo htmlspecialchars($nombre_solo); 
											?>
										</a>
										<ul class="dropdown-menu" id="profileDropdownMenu" style="display: none; position: absolute; background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 10px 0; min-width: 200px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1000;">
											<li><a class="dropdown-item" href="#" onclick="event.preventDefault(); closeProfileDropdown(); showProfileInfo();" style="display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #333; text-decoration: none;">
												<img src="<?php echo I18n::sharedAsset('hombre.webp'); ?>" alt="Profile" style="width: 20px; height: 20px; object-fit: contain;">
												<?php echo __('nav.my_profile'); ?>
											</a></li>
											<li><a class="dropdown-item" href="#" onclick="event.preventDefault(); closeProfileDropdown(); showMyReservations();" style="display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #333; text-decoration: none;">
												<img src="<?php echo I18n::sharedAsset('calendario.webp'); ?>" alt="Reservations" style="width: 20px; height: 20px; object-fit: contain;">
												<?php echo __('nav.my_reservations'); ?>
											</a></li>
											<li><hr class="dropdown-divider" style="margin: 10px 0; border-top: 1px solid #ddd;"></li>
											<li><a class="dropdown-item" href="../../app/controllers/auth/logout.php?lang=<?php echo I18n::getLanguage(); ?>" style="display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #FF4136; text-decoration: none;">
												<img src="<?php echo I18n::sharedAsset('salida.webp'); ?>" alt="Logout" style="width: 20px; height: 20px; object-fit: contain;">
												<?php echo __('nav.logout'); ?>
											</a></li>
										</ul>
									</li>
								<?php else: ?>
									<li><a href="../../app/controllers/auth/login.php?lang=<?php echo I18n::getLanguage(); ?>"><?php echo __('nav.login'); ?></a></li>
								<?php endif; ?>

								<?php 
								$currentLang = I18n::getLanguage();
								// Mostrar banderas de otros idiomas (no el actual)
								if ($currentLang !== 'es'): ?>
								<li><a href="index.php?lang=es"><img src="<?php echo I18n::sharedAsset('bcolombia.png'); ?>" target="self"></a></li>
								<?php endif; ?>
								<?php if ($currentLang !== 'en'): ?>
								<li><a href="index.php?lang=en"><img src="<?php echo I18n::sharedAsset('busa.png'); ?>" target="self"></a></li>
								<?php endif; ?>
								<?php if ($currentLang !== 'it'): ?>
								<li><a href="index.php?lang=it"><img src="<?php echo I18n::sharedAsset('bitalia.png'); ?>" target="self"></a></li>
								<?php endif; ?>
							<li style="display: flex; flex-direction: column; align-items: flex-end;">														
<div id="sfcnpga1u87mu84g9kudrfy5skwu8td29lp"></div>
<div id="sfcxds958c1a9mzdg2f1bg2yfdm1p7xxkzz"></div><script type="text/javascript" src="https://counter6.optistats.ovh/private/counter.js?c=xds958c1a9mzdg2f1bg2yfdm1p7xxkzz&down=async" async></script><noscript><a href="https://www.contadorvisitasgratis.com" title="contador de visitas online"><img src="https://counter6.optistats.ovh/private/contadorvisitasgratis.php?c=xds958c1a9mzdg2f1bg2yfdm1p7xxkzz"></a></noscript>
<div style="margin-top: 5px; font-family: 'Oxygen', sans-serif; font-size: 12px; color: #333; text-align: right;">
<?php echo __('stats.registered_users'); ?>: <strong><?php echo $total_usuarios; ?></strong>
</div>
							</li>



							</ul>
						</nav>
					</div>
					<!-- //nav -->
				</div>
			</div>
		</header>
		<!-- //header -->


		<!-- banner -->
		<div class="banner_w3lspvt">
			<!-- Overlay oscuro para el banner -->
			<div class="banner-overlay"></div>
			<div class="csslider infinity" id="slider1">
				<input type="radio" name="slides" checked="checked" id="slides_1" />
				<input type="radio" name="slides" id="slides_2" />
				<input type="radio" name="slides" id="slides_3" />
				<ul class="banner_slide_bg">
					<li>
						<div class="container">
							<div class="w3ls_banner_txt banner-content-wrapper">
								<h3 class="w3ls_pvt-title text-wh text-uppercase let banner-main-title"><?php echo __('banner.relaxation'); ?></h3>
								<p class="banner-subtitle text-wh"><?php echo __('home.cta.new.subtitle'); ?></p>
								<div class="banner-buttons-wrapper">
									<a href="../../app/controllers/auth/register.php?lang=<?php echo I18n::getLanguage(); ?>" class="btn-registrate-ahora-banner">
										<?php echo __('home.cta.new.button'); ?>
									</a>
								</div>
								<p class="banner-counter text-wh"><?php echo __('home.cta.new.registered_count'); ?> <strong><?php echo $total_usuarios; ?></strong></p>
								<div class="banner-buttons-wrapper">
									<button class="btn-reservar-ahora-banner" onclick="scrollToCalendar()"><?php echo __('banner.reserve_now'); ?></button>
								</div>
							</div>
						</div>
					</li>
					<li>
						<div class="container">
							<div class="w3ls_banner_txt banner-content-wrapper">
								<h3 class="w3ls_pvt-title text-wh text-uppercase let banner-main-title"><?php echo __('banner.enjoy'); ?></h3>
								<p class="banner-subtitle text-wh"><?php echo __('home.cta.new.subtitle'); ?></p>
								<div class="banner-buttons-wrapper">
									<a href="../../app/controllers/auth/register.php?lang=<?php echo I18n::getLanguage(); ?>" class="btn-registrate-ahora-banner">
										<?php echo __('home.cta.new.button'); ?>
									</a>
								</div>
								<p class="banner-counter text-wh"><?php echo __('home.cta.new.registered_count'); ?> <strong><?php echo $total_usuarios; ?></strong></p>
								<div class="banner-buttons-wrapper">
									<button class="btn-reservar-ahora-banner" onclick="scrollToCalendar()"><?php echo __('banner.reserve_now'); ?></button>
								</div>
							</div>
						</div>
					</li>
					<li>
						<div class="container">
							<div class="w3ls_banner_txt banner-content-wrapper">
								<h3 class="w3ls_pvt-title text-wh text-uppercase let banner-main-title"><?php echo __('banner.modern'); ?></h3>
								<p class="banner-subtitle text-wh"><?php echo __('home.cta.new.subtitle'); ?></p>
								<div class="banner-buttons-wrapper">
									<a href="../../app/controllers/auth/register.php?lang=<?php echo I18n::getLanguage(); ?>" class="btn-registrate-ahora-banner">
										<?php echo __('home.cta.new.button'); ?>
									</a>
								</div>
								<p class="banner-counter text-wh"><?php echo __('home.cta.new.registered_count'); ?> <strong><?php echo $total_usuarios; ?></strong></p>
								<div class="banner-buttons-wrapper">
									<button class="btn-reservar-ahora-banner" onclick="scrollToCalendar()"><?php echo __('banner.reserve_now'); ?></button>
								</div>
							</div>
						</div>
					</li>
				</ul>
				<div class="arrows">
					<label for="slides_1"></label>
					<label for="slides_2"></label>
					<label for="slides_3"></label>
				</div>
			</div>
		</div>
		<!-- //banner -->
	</div>
	<!-- //main banner -->

<!-- Sección Texto y Carrusel -->
<div class="container-fluid py-5" style="background-color: rgb(235, 234, 223); margin-bottom: 0; padding-bottom: 0;">
    <div class="row align-items-center">	
        <!-- Texto a la izquierda -->
        <div class="col-lg-5 col-md-12 mb-4 mb-lg-0">
            <div id="texto-carrusel" class="scroll-reveal-text" style="padding: 20px;">
                <h2 style="font-family: 'Abril Fatface', serif; font-weight: bold; font-size: 5rem; margin-bottom: 20px; color: #000;">
                    <?php echo __('home.welcome.title'); ?>
                </h2>
                <p style="font-family: Garamond, serif;  font-size: 30px; line-height: 1.8; color: #333; margin-bottom: 15px;">
				<?php echo __('home.welcome.expectations'); ?>
                </p>
                <p style="font-family: Garamond, serif; font-size: 30px;  line-height: 1.8; color: #333	; margin-bottom: 15px;">
				<?php echo __('home.welcome.details'); ?>                </p>
                <p style="font-family: 'Calvino Grande Bold', serif; font-size: 32px; font-weight: 600; color: #333;">
                    <?php echo __('home.welcome.tagline'); ?>
                </p>
            </div>
        </div>
        
        <!-- Carrusel a la derecha -->
        <div class="col-lg-7 col-md-12">
            <div id="blogCarousel" class="carousel slide" data-bs-ride="false" style="max-width: 100%; margin-right: 30px;">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="<?php echo I18n::sharedAsset('blog1.webp'); ?>" class="d-block w-100" alt="Blog 1" style="border-radius: 1px; object-fit: cover; height: 700px; width: 100%;">
                    </div>
                    <div class="carousel-item">
                        <img src="<?php echo I18n::sharedAsset('blog8.webp'); ?>" class="d-block w-100" alt="Blog 8" style="border-radius: 1px; object-fit: cover; height: 700px; width: 100%;">
                    </div>
                    <div class="carousel-item">
                        <img src="<?php echo I18n::sharedAsset('blog3.webp'); ?>" class="d-block w-100" alt="Blog 3" style="border-radius: 1px; object-fit: cover; height: 700px; width: 100%;">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#blogCarousel" data-bs-slide="prev" style="background: none; border: none;">
                    <span style="font-size: 40px; font-weight: bold; color: #fff; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">‹</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#blogCarousel" data-bs-slide="next" style="background: none; border: none;">
                    <span style="font-size: 40px; font-weight: bold; color: #fff; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">›</span>
                </button>
            </div>
        </div>	
    </div>
</div>
<!-- //Sección Texto y Carrusel -->
<!-- Sección de Llamado a la Acción - CONTENIDO MOVIDO AL CARRUSEL PRINCIPAL -->
<!-- <div class="cta-section-new" style="position: relative; background-image: url('<?php echo I18n::sharedAsset('piesplaya.webp'); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: 150px 20px; text-align: center; margin-top: 0; margin-bottom: 0; min-height: 600px; display: flex; align-items: center; justify-content: center;">
    
    <div style="position: relative; z-index: 2; max-width: 900px; margin: 0 auto; width: 100%;">
        <div style="text-align: center; margin-bottom: 60px; width: 100%; position: relative; top: -100px;">
            <h2 id="texto-cta-titulo" class="scroll-reveal-text" style="font-family: 'Calvino Grande Bold', serif; font-size: 5.5rem; color: #fff !important; margin: 0 0 25px 0; line-height: 1.1; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.5); display: block !important; visibility: visible !important; opacity: 1 !important;">
                <?php echo __('home.cta.new.title'); ?>
            </h2>
            <p id="texto-cta-subtitulo" class="scroll-reveal-text" style="font-family: 'Crimson Text', serif; font-size: 1.8rem; color: #fff !important; margin: 0; line-height: 1.7; opacity: 0.98; font-style: italic; text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4); display: block !important; visibility: visible !important;">
                <?php echo __('home.cta.new.subtitle'); ?>
            </p>
            <p id="texto-cta-contador" class="scroll-reveal-text" style="font-family: 'Crimson Text', serif; font-size: 2.5rem; color: #fff !important; margin: 20px 0 0 0; line-height: 1.7; opacity: 0.98; font-style: italic; text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4); display: block !important; visibility: visible !important;">
                <?php echo __('home.cta.new.registered_count'); ?> <strong><?php echo $total_usuarios; ?></strong>
            </p>
        </div>
        <div style="text-align: center; width: 100%; position: relative;">
            <a href="../../app/controllers/auth/register.php?lang=<?php echo I18n::getLanguage(); ?>" 
               style="display: inline-block; background-color: #fff; color: #333; padding: 18px 50px; font-family: 'Oxygen', sans-serif; font-size: 1.2rem; font-weight: 600; text-decoration: none; border-radius: 50px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);">
                <?php echo __('home.cta.new.button'); ?>
            </a>
        </div>
    </div>
</div> -->
<!-- //Sección de Llamado a la Acción - CONTENIDO MOVIDO AL CARRUSEL PRINCIPAL -->

<!-- Separador entre secciones -->
<div class="section-divider" style="width: 100%; height: 80px; background: linear-gradient(to bottom, rgb(235, 234, 223) 0%, #ffffff 100%); margin: 0; padding: 0; position: relative; overflow: hidden;">
</div>

<!-- Sistema de Reservas Interactivo -->
<div class="container-fluid py-5" id="reservation-section">
    <!-- Título centrado en toda la pantalla -->
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h2 style="font-family: 'Abril Fatface', serif; font-weight: bold; font-size: 5rem; margin-bottom: 20px; color: #000; text-align: center; opacity: 1 !important; visibility: visible !important; display: block !important;">
                <?php echo __('calendar.title'); ?>
            </h2>
        </div>
    </div>
    <div class="row">
        <!-- Calendario de Reservas - Lado Izquierdo -->
        <div class="col-lg-7 col-md-12 order-1 order-lg-1 mb-4 mb-lg-0">
            <div id="calendario-container" class="calendar-container scroll-reveal-left">
                <div class="calendar-header">
                    <div class="calendar-navigation">
                        <button id="prevMonth" class="btn btn-outline-primary">‹</button>
                        <span id="currentMonth" class="month-display"></span>
                        <button id="nextMonth" class="btn btn-outline-primary">›</button>
                    </div>
                </div>
                <div id="calendar" class="calendar-grid"></div>
            </div>
        </div>
        
        <!-- Resumen de Reserva - Lado Derecho -->
        <div class="col-lg-5 col-md-12 order-2 order-lg-2 d-flex align-items-start" style="padding-top: 0;">
            <div id="resumen-reserva" class="reservation-summary w-100 scroll-reveal-left" style="margin-top: 0; align-self: flex-start;">
                <h4 class="text-center mb-4"><?php echo __('reservation.summary'); ?></h4>
                <div id="reservationDetails" class="reservation-details">
                    <div class="detail-item">
                        <span class="label"><?php echo __('reservation.checkin_date'); ?>:</span>
                        <span id="checkinDate" class="value">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="label"><?php echo __('reservation.checkout_date'); ?>:</span>
                        <span id="checkoutDate" class="value">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="label"><?php echo __('reservation.nights'); ?>:</span>
                        <span id="nightsCount" class="value">-</span>
                    </div>
                    <hr>
                    <div class="detail-item total">
                        <span class="label"><?php echo __('reservation.total'); ?>:</span>
                        <span id="totalPrice" class="value">$0 COP</span>
                    </div>
                </div>
                <button id="reserveBtn" class="btn btn-primary btn-lg w-100 mt-3" disabled>
                    <?php echo __('reservation.book_now'); ?>
                </button>
                
                <!-- Leyenda del Calendario -->
                <div class="reservation-legend mt-4">
                    <p class="legend-title">
                        <?php echo __('reservation.legend.title'); ?>
                    </p>
                    <p class="legend-description">
                        <?php echo __('reservation.legend.description'); ?>
                    </p>
                    
                    <!-- Primera sección: Estados de disponibilidad -->
                    <div class="calendar-legend-section">
                        <div class="legend-item">
                            <span class="legend-color available"></span>
                            <span><?php echo __('calendar.available'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color occupied"></span>
                            <span><?php echo __('calendar.occupied'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Segunda sección: Estados de selección -->
                    <div class="calendar-legend-section">
                        <div class="legend-item">
                            <span class="legend-color checkin"></span>
                            <span><?php echo __('calendar.checkin'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color checkout"></span>
                            <span><?php echo __('calendar.checkout'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color in-range"></span>
                            <span><?php echo __('calendar.selected_range'); ?></span>
                        </div>
                    </div>
                    
                    <p class="text-center mt-3 mb-0" style="font-size: 0.85rem; color: rgb(38, 38, 38); font-family: 'Oxygen', sans-serif; font-weight: 500; font-style: italic;">
                        <?php echo __('calendar.rates_note'); ?> <span class="bold-text" style="font-weight: 700; color: rgb(38, 38, 38);">COP ($)</span>
                    </p>
                </div>
                
                <!-- Botón de Pago -->
                <button id="pagoBtn" class="btn-pago btn-reservar-ahora w-100 mt-4" style="background-color: #FFE082; color: #333; padding: 12px 30px; font-family: 'Oxygen', sans-serif; font-size: 16px; font-weight: 600; border: none; border-radius: 50px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); text-transform: uppercase; letter-spacing: 1px; cursor: pointer;">
                    Botón de Pago
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Advertencia de Rango -->
<div class="modal fade" id="rangeErrorModal" tabindex="-1" aria-labelledby="rangeErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #ffffff; border: none; border-radius: 0; box-shadow: none; font-family: 'Oxygen', sans-serif;">
            <div class="modal-header" style="background: #ffffff; border: none; border-bottom: 1px solid #e1e5e9; border-radius: 0; padding: 30px 40px 20px 40px;">
                <h5 class="modal-title" id="rangeErrorModalLabel" style="font-family: 'Oxygen', sans-serif; font-size: 1.5rem; font-weight: 400; color: #333; margin: 0;" data-default="⚠️ <?php echo __('modal.range_error_title'); ?>">⚠️ <?php echo __('modal.range_error_title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: transparent; border: none; border-bottom: 1px solid #e1e5e9; color: #333; font-size: 1.5rem; padding: 0; width: auto; height: auto; opacity: 1;"></button>
            </div>
            <div class="modal-body" style="padding: 30px 40px; background: #ffffff;">
                <div class="text-center">
                    <div style="font-size: 3rem; margin-bottom: 1.5rem;">⚠️</div>
                    <h6 id="rangeErrorMessage" style="font-family: 'Oxygen', sans-serif; font-size: 1.25rem; font-weight: 400; color: #333; margin-bottom: 15px;"><?php echo __('modal.range_error_message'); ?></h6>
                    <p id="rangeErrorExplanation" style="font-family: 'Oxygen', sans-serif; font-size: 1rem; font-weight: 400; color: #333; margin-bottom: 15px;"><?php echo __('modal.range_error_explanation'); ?></p>
                    <p id="rangeErrorAction" style="font-family: 'Oxygen', sans-serif; font-size: 1rem; font-weight: 400; color: #333; margin-bottom: 0;"><?php echo __('modal.range_error_action'); ?></p>
                </div>
            </div>
            <div class="modal-footer" style="background: #ffffff; border: none; border-top: 1px solid #e1e5e9; border-radius: 0; padding: 20px 40px 30px 40px;">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background: transparent; color: #333; border: none; border-bottom: 1px solid #e1e5e9; border-radius: 0; padding: 12px 30px; font-size: 1rem; font-family: 'Oxygen', sans-serif; font-weight: 400; cursor: pointer; transition: border-bottom-color 0.3s ease;"><?php echo __('modal.got_it'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Día Sin Precio -->
<div class="modal fade" id="noPriceModal" tabindex="-1" aria-labelledby="noPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #ffffff; border: none; border-radius: 0; box-shadow: none; font-family: 'Oxygen', sans-serif;">
            <div class="modal-header" style="background: #ffffff; border: none; border-bottom: 1px solid #e1e5e9; border-radius: 0; padding: 30px 40px 20px 40px;">
                <h5 class="modal-title" id="noPriceModalLabel" style="font-family: 'Oxygen', sans-serif; font-size: 1.5rem; font-weight: 400; color: #333; margin: 0;">⚠️ Lo sentimos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: transparent; border: none; border-bottom: 1px solid #e1e5e9; color: #333; font-size: 1.5rem; padding: 0; width: auto; height: auto; opacity: 1;"></button>
            </div>
            <div class="modal-body" style="padding: 30px 40px; background: #ffffff;">
                <div class="text-center">
                    <div style="font-size: 3rem; margin-bottom: 1.5rem;">⚠️</div>
                    <h6 style="font-family: 'Oxygen', sans-serif; font-size: 1.25rem; font-weight: 400; color: #333; margin-bottom: 15px;">Ese día no está disponible</h6>
                    <p style="font-family: 'Oxygen', sans-serif; font-size: 1rem; font-weight: 400; color: #333; margin-bottom: 15px;">Lo sentimos, pero la fecha seleccionada o alguna fecha en el rango seleccionado no tiene precio disponible.</p>
                    <p style="font-family: 'Oxygen', sans-serif; font-size: 1rem; font-weight: 400; color: #333; margin-bottom: 0;">Por favor, selecciona otras fechas disponibles.</p>
                </div>
            </div>
            <div class="modal-footer" style="background: #ffffff; border: none; border-top: 1px solid #e1e5e9; border-radius: 0; padding: 20px 40px 30px 40px;">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background: transparent; color: #333; border: none; border-bottom: 1px solid #e1e5e9; border-radius: 0; padding: 12px 30px; font-size: 1rem; font-family: 'Oxygen', sans-serif; font-weight: 400; cursor: pointer; transition: border-bottom-color 0.3s ease;">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Formulario de Reserva -->
<div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationModalLabel"><?php echo __('modal.reservation_form'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>	
            <div class="modal-body">
                <form id="reservationForm">
                    <?php if ($user_logged_in && $user_data): ?>
                    <div class="alert alert-info mb-3">
                        <strong><?php echo __('nav.hello'); ?> <?php echo htmlspecialchars($user_data['nombre']); ?>!</strong> 
                        <?php echo __('modal.user_locked_info'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Indicador de progreso -->
                    <div class="progress-indicator">
                        <div class="progress-step active" id="step1">1</div>
                        <div class="progress-step inactive" id="step2">2</div>
                        <div class="progress-step inactive" id="step3">3</div>
                        <div class="progress-step inactive" id="step4">4</div>
                        <div class="progress-step inactive" id="step5">5</div>
                    </div>
                    
                    <!-- Paso 1: Información Personal -->
                    <div class="form-section active" id="section1">
                        <div class="section-header">
                            <h3>Paso 1 de 5</h3>
                            <p>Información Personal</p>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="nombres"><?php echo __('form.first_name'); ?> *</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?php echo I18n::sharedAsset('avatar.webp'); ?>" alt="avatar" style="width: 24px; height: 24px; flex-shrink: 0;">
                                <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombre" value="<?php echo $user_data ? htmlspecialchars($user_data['nombre']) : ''; ?>" <?php echo ($user_logged_in && $user_data) ? 'readonly' : ''; ?> required>
                            </div>
                            <span class="error-message" id="nombresError"></span>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="apellidos"><?php echo __('form.last_name'); ?> *</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 24px; height: 24px; flex-shrink: 0;"></div>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Apellido" value="<?php echo $user_data ? htmlspecialchars($user_data['apellido']) : ''; ?>" <?php echo ($user_logged_in && $user_data) ? 'readonly' : ''; ?> required>
                            </div>
                            <span class="error-message" id="apellidosError"></span>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="celular"><?php echo __('form.phone'); ?> *</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?php echo I18n::sharedAsset('receptor-de-telefono.webp'); ?>" alt="teléfono" style="width: 24px; height: 24px; flex-shrink: 0;">
                                <?php
                                $codigoPaisValue = '+57';
                                $celularValue = '';
                                $isReadonly = ($user_logged_in && $user_data);
                                
                                if ($user_data && isset($user_data['telefono']) && $user_data['telefono']) {
                                    $telefono = trim($user_data['telefono']);
                                    
                                    // Intentar extraer código de país y número
                                    // Formatos posibles: "+57 3001234567", "+573001234567", "573001234567", "3001234567"
                                    
                                    // Caso 1: Formato con espacio "+57 3001234567"
                                    if (preg_match('/^(\+\d{1,4})\s+(.+)$/', $telefono, $matches)) {
                                        $codigoPaisValue = $matches[1];
                                        $celularValue = $matches[2];
                                    }
                                    // Caso 2: Formato sin espacio pero con + "+573001234567"
                                    elseif (preg_match('/^(\+\d{1,4})(\d{7,15})$/', $telefono, $matches)) {
                                        $codigoPaisValue = $matches[1];
                                        $celularValue = $matches[2];
                                    }
                                    // Caso 3: Solo números sin + (asumir código de país por defecto)
                                    elseif (preg_match('/^(\d{7,15})$/', $telefono, $matches)) {
                                        // Si tiene 10 dígitos, probablemente es colombiano (sin código de país)
                                        $codigoPaisValue = '+57';
                                        $celularValue = $telefono;
                                    }
                                    // Caso 4: Cualquier otro formato, intentar extraer lo que parezca código de país
                                    else {
                                        // Intentar encontrar código de país al inicio
                                        if (preg_match('/^(\+\d{1,4})/', $telefono, $matches)) {
                                            $codigoPaisValue = $matches[1];
                                            $celularValue = preg_replace('/^' . preg_quote($matches[1], '/') . '/', '', $telefono);
                                        } else {
                                            // Si no tiene código de país, usar el número completo y código por defecto
                                            $codigoPaisValue = '+57';
                                            $celularValue = preg_replace('/^\+?/', '', $telefono);
                                        }
                                    }
                                    
                                    // Limpiar el número de celular (solo dígitos)
                                    $celularValue = preg_replace('/\D/', '', $celularValue);
                                }
                                ?>
                                <input type="text" id="codigoPais" name="codigoPais" class="form-control" placeholder="+57" style="width: 60px; flex-shrink: 0; maxlength: 5; padding: 10px 5px;" value="<?php echo htmlspecialchars($codigoPaisValue); ?>" <?php echo $isReadonly ? 'readonly' : ''; ?> <?php echo $isReadonly ? '' : 'required'; ?>>
                                <input type="tel" class="form-control" id="celular" name="celular" placeholder="3001234567" pattern="[0-9]*" inputmode="numeric" maxlength="15" value="<?php echo htmlspecialchars($celularValue); ?>" <?php echo $isReadonly ? 'readonly' : ''; ?> <?php echo $isReadonly ? '' : 'required'; ?>>
                            </div>
                            <span class="error-message" id="celularError"></span>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="fechaNacimiento"><?php echo __('form.birthday'); ?> *</label>
                            <div style="display: flex; align-items: center; gap: 10px; position: relative; flex: 1;">
                                <img src="https://cdn1.iconfinder.com/data/icons/cc_mono_icon_set/blacks/16x16/calendar_2.png" alt="calendario" style="width: 24px; height: 24px; flex-shrink: 0; opacity: 0.7;">
                                <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" value="<?php echo $user_data && $user_data['fecha_nacimiento'] ? $user_data['fecha_nacimiento'] : ''; ?>" <?php echo ($user_logged_in && $user_data) ? 'readonly' : 'required'; ?> onkeydown="return false;" onpaste="return false;" autocomplete="off" style="flex: 1;">
                            </div>
                            <span class="error-message" id="fechaNacimientoError"></span>
                        </div>
                        
                        <div class="section-buttons">
                            <button type="button" class="next-btn" onclick="nextReservationSection(1)">Continuar</button>
                        </div>
                    </div>
                    
                    <!-- Paso 2: Huéspedes -->
                    <div class="form-section" id="section2">
                        <div class="section-header">
                            <h3>Paso 2 de 5</h3>
                            <p>Información de Huéspedes</p>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="adultos"><?php echo __('form.adults'); ?> *</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?php echo I18n::sharedAsset('hombre.webp'); ?>" alt="adultos" style="width: 24px; height: 24px; flex-shrink: 0;">
                                <select class="form-control" id="adultos" name="adultos" required>
                                    <option value="" disabled selected>Adultos</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                </select>
                            </div>
                            <span class="error-message" id="adultosError"></span>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="ninos"><?php echo __('form.children'); ?></label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?php echo I18n::sharedAsset('ninos.webp'); ?>" alt="niños" style="width: 24px; height: 24px; flex-shrink: 0;">
                                <select class="form-control" id="ninos" name="ninos">
                                    <option value="0" selected>Niños</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="input-group-ihg">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="vivePalmira" name="vivePalmira">
                                <label class="form-check-label" for="vivePalmira">
                                    ¿Vives en Palmira?
                                </label>
                            </div>
                            <div id="palmiraInfo" class="alert alert-info mt-2" style="display: none;">
                                <small><?php echo __('form.palmira_info'); ?></small>
                            </div>
                        </div>
                        
                        <div class="section-buttons">
                            <button type="button" class="back-btn" onclick="prevReservationSection(2)">Atrás</button>
                            <button type="button" class="next-btn" onclick="nextReservationSection(2)">Continuar</button>
                        </div>
                    </div>
                    
                    <!-- Paso 3: Correo Electrónico -->
                    <div class="form-section" id="section3">
                        <div class="section-header">
                            <h3>Paso 3 de 5</h3>
                            <p>Correo Electrónico</p>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label for="correo"><?php echo __('form.email'); ?> *</label>
                            <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo Electrónico" value="<?php echo $user_data ? htmlspecialchars($user_data['correo']) : ''; ?>" required>
                            <span class="error-message" id="correoError"></span>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <small><strong>Nota:</strong> Confirma tu correo antes de continuar; el seguimiento de tu reserva se enviarán a esa dirección.</small>
                        </div>
                        
                        <div class="section-buttons">
                            <button type="button" class="back-btn" onclick="prevReservationSection(3)">Atrás</button>
                            <button type="button" class="next-btn" onclick="nextReservationSection(3)">Continuar</button>
                        </div>
                    </div>
                    
                    <!-- Paso 4: Método de Pago -->
                    <div class="form-section" id="section4">
                        <div class="section-header">
                            <h3>Paso 4 de 5</h3>
                            <p>Método de Pago</p>
                        </div>
                        
                        <div class="input-group-ihg">
                            <label><?php echo __('form.payment_method'); ?> *</label>
                            <div class="payment-methods-container" style="display: flex; gap: 30px; align-items: flex-start; justify-content: center;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metodoPago" id="tarjetaCredito" value="tarjeta_credito" checked>
                                    <label class="form-check-label" for="tarjetaCredito">
                                        <?php echo __('form.credit_card'); ?>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metodoPago" id="transferencia" value="transferencia">
                                    <label class="form-check-label" for="transferencia">
                                        Transferencia (<?php echo isset($descuentos['promocional']) && $descuentos['promocional']['activo'] ? $descuentos['promocional']['porcentaje'] : 3; ?>% <?php echo __('form.cash_discount'); ?>)
                                    </label>
                                </div>
                            </div>
                            <div id="descuentoInfo" class="alert alert-success mt-2" style="display: none;">
                                <small><?php echo isset($descuentos['promocional']) && $descuentos['promocional']['activo'] ? $descuentos['promocional']['porcentaje'] : 3; ?>% <?php echo __('form.discount_applied'); ?></small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <h6 class="mb-2"><strong>Importante:</strong></h6>
                            <p class="mb-0">
                                Una vez aprobada tu reserva, deberás pagar el 20% del total y enviar el comprobante a <strong>gerencia@mysuiteincartagena.com.co</strong>. El saldo restante se cancelará el día anterior al check-in.
                            </p>
                        </div>
                        
                        <div class="section-buttons">
                            <button type="button" class="back-btn" onclick="prevReservationSection(4)">Atrás</button>
                            <button type="button" class="next-btn" onclick="nextReservationSection(4)">Continuar</button>
                        </div>
                    </div>
                    
                    <!-- Paso 5: Resumen y Confirmación -->
                    <div class="form-section" id="section5">
                        <div class="section-header">
                            <h3>Paso 5 de 5</h3>
                            <p>Resumen y Confirmación</p>
                        </div>
                        
                        <div class="reservation-summary-section">
                            <h6>Resumen de tu Reserva</h6>
                            <div class="summary-item">
                                <span class="summary-label">Nombre:</span>
                                <span class="summary-value" id="summaryNombres">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Apellidos:</span>
                                <span class="summary-value" id="summaryApellidos">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Celular:</span>
                                <span class="summary-value" id="summaryCelular">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Correo:</span>
                                <span class="summary-value" id="summaryCorreo">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Adultos:</span>
                                <span class="summary-value" id="summaryAdultos">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Niños:</span>
                                <span class="summary-value" id="summaryNinos">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Método de Pago:</span>
                                <span class="summary-value" id="summaryMetodoPago">-</span>
                            </div>
                            <div class="summary-item" style="border-top: 2px solid #FFE082; margin-top: 10px; padding-top: 15px;">
                                <span class="summary-label">Subtotal:</span>
                                <span class="summary-value" id="summarySubtotal">$0 COP</span>
                            </div>
                            <div class="summary-item" id="summaryDescuentoFidelidad" style="display: none;">
                                <span class="summary-label" style="color: #28a745;">Descuento por Fidelidad (<span id="summaryDescuentoFidelidadPorcentaje"><?php echo isset($descuentos['fidelidad']) && $descuentos['fidelidad']['activo'] ? number_format($descuentos['fidelidad']['porcentaje'], 0) : '5'; ?>%</span>):</span>
                                <span class="summary-value" style="color: #28a745;" id="summaryDescuentoFidelidadValor">-$0 COP</span>
                            </div>
                            <div class="summary-item" id="summaryDescuentoCumpleanos" style="display: none;">
                                <span class="summary-label" style="color: #28a745;">Descuento por Cumpleaños (<span id="summaryDescuentoCumpleanosPorcentaje"><?php echo isset($descuentos['cumpleanos']) && $descuentos['cumpleanos']['activo'] ? number_format($descuentos['cumpleanos']['porcentaje'], 0) : '30'; ?>%</span>):</span>
                                <span class="summary-value" style="color: #28a745;" id="summaryDescuentoCumpleanosValor">-$0 COP</span>
                            </div>
                            <div class="summary-item" id="summarySubtotalIntermedioContainer" style="display: none; border-top: 2px solid #FFE082; margin-top: 10px; padding-top: 15px;">
                                <span class="summary-label">Subtotal:</span>
                                <span class="summary-value" id="summarySubtotalIntermedio">$0 COP</span>
                            </div>
                            <div class="summary-item" id="summaryDescuentoEfectivo" style="display: none;">
                                <span class="summary-label" style="color: #28a745;">Descuento por Transferencia (<span id="summaryDescuentoEfectivoPorcentaje"><?php echo isset($descuentos['promocional']) && $descuentos['promocional']['activo'] ? number_format($descuentos['promocional']['porcentaje'], 1) : '3.0'; ?>%</span>):</span>
                                <span class="summary-value" style="color: #28a745;" id="summaryDescuentoEfectivoValor">-$0 COP</span>
                            </div>
                            <div class="summary-item" style="border-top: 2px solid #FFE082; margin-top: 10px; padding-top: 15px;">
                                <span class="summary-label"><strong>Total:</strong></span>
                                <span class="summary-value"><strong id="summaryTotal">$0 COP</strong></span>
                            </div>
                        </div>
                        
                        <div class="form-check mt-3">
                            <div class="mb-2">
                                <button type="button" class="btn btn-link p-0 text-primary" id="btnLeerTerminos" style="text-decoration: underline; font-size: 0.95rem; border: none; background: none; cursor: pointer;">
                                    📄 <?php echo __('form.read_terms'); ?>
                                </button>
                                <span class="text-muted" id="terminosStatus" style="font-size: 0.85rem; margin-left: 10px;">
                                    <i class="fa fa-times-circle text-danger"></i> <?php echo __('form.terms_not_read'); ?>
                                </span>
                            </div>
                            <input class="form-check-input" type="checkbox" id="aceptaPolitica" name="aceptaPolitica" required disabled>
                            <label class="form-check-label" for="aceptaPolitica" style="cursor: not-allowed; opacity: 0.6;">
                                <?php echo __('form.accept_policy'); ?> *
                            </label>
                            <span class="error-message" id="aceptaPoliticaError"></span>
                        </div>
                        
                        <div class="section-buttons">
                            <button type="button" class="back-btn" onclick="prevReservationSection(5)">Atrás</button>
                            <button type="button" class="next-btn" id="submitReservationFinal">Solicitar Reserva</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelReservation" data-bs-dismiss="modal"><?php echo __('form.cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Términos y Condiciones - Pantalla Completa -->
<div class="modal fade" id="terminosModal" tabindex="-1" aria-labelledby="terminosModalLabel" aria-hidden="true" style="z-index: 10003;">
    <div class="modal-dialog modal-fullscreen" style="margin: 0; width: 100%; max-width: 100%; height: 100vh; max-height: 100vh;">
        <div class="modal-content" style="border-radius: 0; border: none; box-shadow: none; display: flex; flex-direction: column; height: 100vh; max-height: 100vh; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 0; flex-shrink: 0; padding: 1.25rem; border-bottom: 2px solid rgba(255,255,255,0.2); display: flex; align-items: center; gap: 15px;">
                <img src="<?php echo I18n::sharedAsset('HOTEL_CARTAGENA_silueta[1].png'); ?>" alt="My Suite In Cartagena" style="height: 50px; width: auto; object-fit: contain;">
                <h5 class="modal-title" id="terminosModalLabel" style="font-weight: 600; font-size: 1.5rem; margin: 0; flex: 1;">
                    <i class="fa fa-file-text-o" style="margin-right: 8px;"></i><?php echo __('form.terms_and_conditions'); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="closeTerminosModal" style="opacity: 0.9; font-size: 1.2rem;"></button>
            </div>
            <div class="modal-body" style="flex: 1 1 auto; overflow-y: auto; padding: 30px; background: #fff; position: relative; min-height: 0;">
                <div id="terminosContent" style="line-height: 1.8; color: #333; font-size: 1rem; max-width: 900px; margin: 0 auto;">
                    <!-- El contenido de términos y condiciones se cargará aquí -->
                </div>
            </div>
            <div class="modal-footer" style="flex-shrink: 0; border-top: 2px solid #e9ecef; padding: 1.5rem; background: #f8f9fa; border-radius: 0;">
                <div class="w-100" style="max-width: 900px; margin: 0 auto;">
                    <div class="progress mb-3" style="height: 10px; border-radius: 5px; background: #e9ecef;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="readingProgress" role="progressbar" style="width: 0%; background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span id="readingStatus" class="text-muted" style="font-size: 1rem; flex: 1;">
                            <i class="fa fa-info-circle"></i> <span id="readingStatusText"><?php echo __('form.please_read_document'); ?></span>
                        </span>
                        <button type="button" class="btn btn-primary btn-lg" id="btnAceptarTerminos" style="min-width: 250px; font-weight: 600; padding: 0.875rem 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(30, 60, 114, 0.3); font-size: 1.1rem; cursor: not-allowed; opacity: 0.6;" disabled>
                            <i class="fa fa-check-circle"></i> <?php echo __('form.accept_terms_button'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Perfil de Usuario -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true" style="z-index: 10001;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2); z-index: 10002;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title" id="profileModalLabel" style="font-weight: bold;">
                    👤 <?php echo __('profile.title'); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div id="profileContent">
                    <div class="text-center mb-4">
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 3rem; color: white;">
                            👤
                        </div>
                    </div>
                    <div class="profile-info">
                        <div class="info-item mb-3">
                            <div class="info-label" style="font-weight: 600; color: #666; font-size: 0.9rem; margin-bottom: 5px;">
                                👤 <?php echo __('profile.name'); ?>
                            </div>
                            <div class="info-value" id="profileName" style="font-size: 1.1rem; color: #333;">-</div>
                        </div>
                        <div class="info-item mb-3">
                            <div class="info-label" style="font-weight: 600; color: #666; font-size: 0.9rem; margin-bottom: 5px;">
                                📧 <?php echo __('profile.email'); ?>
                            </div>
                            <div class="info-value" id="profileEmail" style="font-size: 1.1rem; color: #333;">-</div>
                        </div>
                        <div class="info-item mb-3">
                            <div class="info-label" style="font-weight: 600; color: #666; font-size: 0.9rem; margin-bottom: 5px;">
                                📞 <?php echo __('profile.phone'); ?>
                            </div>
                            <div class="info-value" id="profilePhone" style="font-size: 1.1rem; color: #333;">-</div>
                        </div>
                        <div class="info-item mb-3">
                            <div class="info-label" style="font-weight: 600; color: #666; font-size: 0.9rem; margin-bottom: 5px;">
                                🎂 <?php echo __('profile.birthday'); ?>
                            </div>
                            <div class="info-value" id="profileBirthday" style="font-size: 1.1rem; color: #333;">-</div>
                        </div>
                        <hr style="margin: 1.5rem 0;">
                        <div class="discounts-section">
                            <h6 style="color: #1e3c72; font-weight: bold; margin-bottom: 1rem;">
                                🏷️ <?php echo __('profile.available_discounts'); ?>
                            </h6>
                            <div class="discount-item mb-2" style="padding: 10px; background: #f8f9fa; border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>⭐ <?php echo __('profile.fidelity'); ?></span>
                                    <span class="badge bg-success"><?php echo isset($descuentos['fidelidad']) ? $descuentos['fidelidad']['porcentaje'] : 5; ?>%</span>
                                </div>
                                <small class="text-muted"><?php echo __('profile.always'); ?></small>
                            </div>
                            <div class="discount-item mb-2" style="padding: 10px; background: #f8f9fa; border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>🎂 <?php echo __('profile.birthday_discount'); ?></span>
                                    <span class="badge bg-danger"><?php echo isset($descuentos['cumpleanos']) ? $descuentos['cumpleanos']['porcentaje'] : 30; ?>%</span>
                                </div>
                                <small class="text-muted"><?php echo __('profile.birthday_range'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 2rem;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    ❌ <?php echo __('modal.close'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Mis Reservas -->
<div class="modal fade" id="reservationsModal" tabindex="-1" aria-labelledby="reservationsModalLabel" aria-hidden="true" style="z-index: 10001;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2); z-index: 10002;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title" id="reservationsModalLabel" style="font-weight: bold;">
                    📅 <?php echo __('reservations.title'); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div id="reservationsContent">
                    <div class="text-center" id="loadingReservations">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted"><?php echo __('reservations.loading'); ?></p>
                    </div>
                    <div id="reservationsList" style="display: none;"></div>
                    <div id="noReservations" style="display: none; text-align: center; padding: 3rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📅</div>
                        <h5 class="text-muted"><?php echo __('reservations.no_reservations'); ?></h5>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 2rem;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    ❌ <?php echo __('modal.close'); ?>
                </button>
            </div>
        </div>	
    </div>
</div>

<!-- Nuevo Footer con 3 Columnas -->
<footer class="new-footer">
	<div class="footer-top-section">
		<div class="container-fluid">
			<div class="row no-gutters">
				<!-- Primera Columna: Redes Sociales -->
				<div class="col-md-4 footer-column footer-social">
					<div class="footer-content">
						<h3 class="footer-subtitle"><?php echo __('footer.get_social'); ?></h3>
						<h2 class="footer-title"><?php echo __('footer.connect_with_us'); ?></h2>
						<div class="social-icons-container">
							<a href="https://www.facebook.com/profile.php?id=61569691859314" target="_blank" class="social-icon">
								<span class="fa fa-facebook-f"></span>
							</a>
							<a href="https://www.instagram.com/mysuiteincartagena/" target="_blank" class="social-icon">
								<span class="fa fa-instagram"></span>
							</a>
							<a href="https://wa.me/+573015193163" target="_blank" class="social-icon">
								<img src="<?php echo I18n::sharedAsset('whatsapp.webp'); ?>" alt="WhatsApp" class="whatsapp-icon">
							</a>
						</div>
					</div>
				</div>
				
				<!-- Segunda Columna: Transporte -->
				<div class="col-md-4 footer-column footer-transport">
					<div class="footer-content">
						<div class="transport-image-container">
							<img src="<?php echo I18n::sharedAsset('optra.webp'); ?>" alt="Transporte Optra" class="transport-image">
						</div>
						<h3 class="footer-subtitle"><?php echo __('footer.transport_subtitle'); ?></h3>
						<h2 class="footer-title"><?php echo __('footer.transport_title'); ?></h2>
					</div>
				</div>
				
				<!-- Tercera Columna: Te Esperamos -->
				<div class="col-md-4 footer-column footer-waiting">
					<div class="footer-content">
						<h2 class="footer-title"><?php echo __('footer.we_wait'); ?></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Sección inferior negra (copyright) -->
	<div class="footer-bottom-section">
		<div class="container-fluid">
			<div class="row align-items-center">
				<div class="col-md-6 text-left">
					<p class="footer-links">
						<a href="../contactenos.php"><?php echo __('footer.contact'); ?></a>
						<a href="#"><?php echo __('footer.privacy_terms'); ?></a>
					</p>
				</div>
				<div class="col-md-6 text-right">
					<p class="footer-copyright"><?php echo __('footer.copyright'); ?></p>
				</div>
			</div>
		</div>
	</div>
</footer>


	<!-- move top icon -->
	<a href="#home" class="move-top text-center"></a>
	<!-- //move top icon -->

	<!-- Estilos CSS para el Sistema de Reservas -->
	<style>
		/* Estilos generales para mejor adaptabilidad */
		* {
			box-sizing: border-box !important;
		}
		
		html {
			font-size: 16px;
		}
		
		body {
			width: 100%;
			max-width: 100vw;
			overflow-x: hidden;
		}
		
		/* Ajustes para diferentes navegadores */
		@supports (-webkit-appearance: none) {
			* {
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
			}
		}
		
		/* Header transparente cuando está arriba */
		header {
			background-color: transparent !important;
			transition: background-color 0.3s ease !important;
		}
		
		header .container-fluid {
			background-color: transparent !important;
			transition: background-color 0.3s ease !important;
		}
		
		header .header {
			background-color: transparent !important;
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
			width: 100% !important;
		}
		
		/* Ajustar alineación del header */
		header #logo {
			display: flex !important;
			align-items: center !important;
			flex-shrink: 0 !important;
		}
		
		header .nav_w3ls {
			display: flex !important;
			align-items: center !important;
			flex-shrink: 0 !important;
		}
		
		header nav ul.menu {
			display: flex !important;
			align-items: center !important;
			margin: 0 !important;
			padding: 0 !important;
			list-style: none !important;
		}
		
		header nav ul.menu li {
			display: flex !important;
			align-items: center !important;
			margin: 0 5px !important;
		}
		
		/* Fuente para el header */
		header #logo a,
		header nav a,
		header nav ul li a {
			font-family: 'Oxygen', sans-serif !important;
			font-weight: 300 !important;
			font-size: clamp(0.875rem, 1.5vw, 1rem) !important;
		}
		
		header #logo a {
			font-size: clamp(1.5rem, 3vw, 2.5rem) !important;
		}
		
		/* Letras blancas en el header cuando está arriba (transparente) */
		header nav a,
		header #logo a,
		header nav ul li a,
		header .dropdown-toggle {
			color: #fff !important;
			text-shadow: none !important;
			text-decoration: none !important;
		}
		
		header .dropdown-toggle:hover {
			color: #fff !important;
			text-decoration: none !important;
		}
		
		/* Estilos para el dropdown del perfil */
		header nav ul.menu li.dropdown {
			position: relative;
		}
		
		#profileDropdownMenu {
			position: absolute !important;
			top: 100% !important;
			left: 0 !important;
			right: auto !important;
			background: #fff !important;
			border: 1px solid #ddd !important;
			border-radius: 5px !important;
			padding: 10px 0 !important;
			min-width: 200px !important;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
			z-index: 10000 !important;
			margin-top: 5px !important;
		}
		
		#profileDropdownMenu .dropdown-item {
			display: block !important;
			padding: 10px 20px !important;
			color: #333 !important;
			text-decoration: none !important;
			transition: background-color 0.2s ease !important;
		}
		
		#profileDropdownMenu .dropdown-item:hover {
			background-color: #f5f5f5 !important;
			color: #333 !important;
		}
		
		header.scrolled #profileDropdownMenu {
			background: #fff !important;
		}
		
		header nav a:hover {
			color: #fff !important;
			opacity: 0.8 !important;
		}
		
		/* Header fijo - solo cuando se hace scroll */
		header.scrolled {
			position: fixed !important;
			top: 0 !important;
			left: 0 !important;
			right: 0 !important;
			width: 100% !important;
			z-index: 1000 !important;
			background-color: #000 !important;
			padding: 8px 0 !important;
		}
		
		/* En móviles, ocultar el contenido del header scrolled excepto el botón */
		@media (max-width: 768px) {
			/* Ocultar el contenido del header scrolled pero mantener el toggle visible */
			header.scrolled .header {
				display: none !important;
			}
			
			header.scrolled .container-fluid {
				background-color: transparent !important;
				padding: 0 !important;
			}
			
			/* Mantener visible solo el botón toggle móvil con position fixed */
			header.scrolled .mobile-toggle,
			header.scrolled label.mobile-toggle {
				display: block !important;
				position: fixed !important;
				top: 10px !important;
				right: 15px !important;
				z-index: 1001 !important;
				background-color: rgba(255, 255, 255, 0.9) !important;
				color: #000 !important;
				padding: 8px 14px !important;
				border-radius: 4px !important;
			}
			
			/* Ocultar el toggle dentro del nav en móviles cuando está scrolled */
			header.scrolled nav .toggle {
				display: none !important;
			}
			
			/* Mostrar el header cuando el checkbox esté marcado */
			header.scrolled [id^=drop]:checked ~ .container-fluid {
				display: block !important;
				background-color: #000 !important;
				padding: 10px 20px !important;
			}
			
			header.scrolled [id^=drop]:checked ~ .container-fluid .header {
				display: flex !important;
				position: relative !important;
			}
		}
		
		header.scrolled .container-fluid {
			background-color: #000 !important;
			padding: 5px 20px !important;
		}
		
		header.scrolled .header {
			background-color: #000 !important;
			padding: 5px 0 !important;
			align-items: center !important;
		}
		
		/* Ajustar alineación vertical de la navegación para que coincida con el logo */
		header.scrolled .nav_w3ls {
			display: flex !important;
			align-items: center !important;
		}
		
		header.scrolled nav ul.menu {
			display: flex !important;
			align-items: center !important;
			margin-top: 2px !important;
		}
		
		header.scrolled nav ul.menu li {
			display: flex !important;
			align-items: center !important;
		}
		
		/* Letras blancas cuando el header está scrolled (fondo negro) */
		header.scrolled nav a,
		header.scrolled #logo a,
		header.scrolled nav ul li a,
		header.scrolled .dropdown-toggle,
		header.scrolled nav ul li a[style*="color"] {
			color: #fff !important;
			text-shadow: none !important;
		}
		
		/* Quitar text-shadow del logo siempre */
		#logo a {
			text-shadow: none !important;
		}
		
		header.scrolled nav a:hover {
			color: #fff !important;
			opacity: 0.8 !important;
		}
		
		/* Asegurar que el texto del contador también sea blanco */
		header.scrolled div[style*="font-family: 'Oxygen'"] {
			color: #fff !important;
		}
		
		/* Responsive para header - Tablets y pantallas medianas */
		@media (max-width: 1200px) {
			header #logo a {
				font-size: clamp(1.25rem, 2.5vw, 2rem) !important;
			}
			header nav a,
			header nav ul li a {
				font-size: clamp(0.8rem, 1.2vw, 0.95rem) !important;
				padding: 5px 8px !important;
			}
		}
		
		/* Responsive para header en móviles */
		/* Estilos para el toggle menu en móviles */
		.toggle,
		label.toggle,
		.mobile-toggle {
			display: none !important;
		}
		
		[id^=drop] {
			display: none !important;
		}
		
		@media (max-width: 768px) {
			/* Mostrar el botón toggle móvil en móviles - esquina superior derecha */
			.mobile-toggle {
				display: flex !important;
				padding: 8px !important;
				text-decoration: none !important;
				border: none !important;
				background-color: rgba(255, 255, 255, 0.9) !important;
				border-radius: 4px !important;
				cursor: pointer !important;
				transition: 0.5s all !important;
				position: absolute !important;
				top: 10px !important;
				right: 15px !important;
				z-index: 1001 !important;
				margin: 0 !important;
				align-items: center !important;
				justify-content: center !important;
			}
			
			.mobile-toggle .menu-icon {
				width: 28px !important;
				height: 28px !important;
				display: block !important;
				object-fit: contain !important;
			}
			
			header.scrolled .mobile-toggle {
				background-color: rgba(255, 255, 255, 0.9) !important;
				position: fixed !important;
				top: 10px !important;
				right: 15px !important;
				z-index: 1001 !important;
				display: flex !important;
				align-items: center !important;
				justify-content: center !important;
			}
			
			.toggle:hover {
				background-color: rgba(255, 255, 255, 1) !important;
				opacity: 0.9 !important;
			}
			
			/* Ocultar el menú por defecto en móviles */
			header nav ul.menu {
				display: none !important;
				flex-direction: column !important;
				width: 100% !important;
				background-color: rgba(0, 0, 0, 0.95) !important;
				padding: 15px 0 !important;
				margin-top: 50px !important;
				border-radius: 4px !important;
				position: relative !important;
				z-index: 1000 !important;
			}
			
			header.scrolled nav ul.menu {
				background-color: rgba(0, 0, 0, 0.95) !important;
				margin-top: 50px !important;
				position: relative !important;
				z-index: 1000 !important;
			}
			
			/* Mostrar el menú cuando el checkbox esté marcado */
			[id^=drop]:checked + ul.menu {
				display: flex !important;
			}
			
			/* Asegurar que el header scrolled también tenga el menú responsive */
			header.scrolled .nav_w3ls {
				width: 100% !important;
				justify-content: center !important;
				position: relative !important;
			}
			
			header.scrolled nav {
				width: 100% !important;
			}
			
			/* Estilos para los items del menú en móviles */
			header nav ul.menu li {
				display: block !important;
				width: 100% !important;
				margin: 0 !important;
				padding: 8px 0 !important;
				text-align: center !important;
			}
			
			header nav ul.menu li a {
				display: block !important;
				width: 100% !important;
				padding: 10px 20px !important;
				color: #fff !important;
				font-size: 15px !important;
				text-align: center !important;
			}
			
			header nav ul.menu li a:hover {
				background-color: rgba(255, 255, 255, 0.1) !important;
				color: #fff !important;
			}
			
			/* Estilos del header en móviles */
			header .header {
				flex-wrap: wrap !important;
				padding: 10px 0 !important;
			}
			
			header #logo {
				width: 100% !important;
				text-align: center !important;
				margin-bottom: 10px !important;
			}
			
			header .nav_w3ls {
				width: 100% !important;
				justify-content: center !important;
			}
			
			header.scrolled {
				padding: 5px 0 !important;
			}
			
			header.scrolled .container-fluid {
				padding: 0 10px !important;
			}
			
			header.scrolled #logo a {
				font-size: clamp(1.1rem, 4vw, 1.5rem) !important;
			}
			
			/* Dropdown menu en móviles */
			header nav ul.menu li.dropdown .dropdown-menu {
				position: static !important;
				float: none !important;
				width: 100% !important;
				background-color: rgba(255, 255, 255, 0.1) !important;
				border: none !important;
				box-shadow: none !important;
				margin-top: 5px !important;
			}
			
			header nav ul.menu li.dropdown .dropdown-item {
				color: #fff !important;
				padding: 10px 20px !important;
				text-align: center !important;
			}
			
			header nav ul.menu li.dropdown .dropdown-item:hover {
				background-color: rgba(255, 255, 255, 0.2) !important;
			}
		}
		
		@media (max-width: 480px) {
			header .header {
				padding: 8px 0 !important;
			}
			
			header #logo a {
				font-size: clamp(1rem, 5vw, 1.3rem) !important;
			}
			
			header nav ul.menu li {
				margin: 2px 3px !important;
			}
			
			header.scrolled #logo a {
				font-size: clamp(1rem, 5vw, 1.3rem) !important;
			}
			header.scrolled nav a {
				font-size: clamp(0.7rem, 2.5vw, 0.85rem) !important;
				padding: 4px 6px !important;
			}
		}
		
		/* Ajustes para pantallas muy grandes */
		@media (min-width: 1920px) {
			header #logo a {
				font-size: 2.5rem !important;
			}
			header nav a,
			header nav ul li a {
				font-size: 1rem !important;
			}
		}
		
		/* Ajustes para diferentes navegadores */
		@supports (-webkit-appearance: none) {
			header {
				-webkit-font-smoothing: antialiased !important;
				-moz-osx-font-smoothing: grayscale !important;
			}
		}
		
		
		
		.banner-overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100vh;
			min-height: 100vh;
			background: rgba(0, 0, 0, 0.1);
			z-index: 2;
			pointer-events: none;
		}
		
		/* Overlay adicional específico para las imágenes del slider */
		.csslider > ul > li::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100vh;
			min-height: 100vh;
			z-index: 1;
			pointer-events: none;
		}
		
		.banner_w3lspvt .csslider {
			position: relative !important;
			width: 100% !important;
			height: 100vh !important;
			min-height: 100vh !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		.banner_w3lspvt .w3ls_banner_txt,
		.banner_w3lspvt .csslider > ul > li > .container {
			position: relative;
			z-index: 10 !important;
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
			height: 100vh !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
		}
		
		.w3ls_banner_txt,
		.w3ls_pvt-title {
			position: relative;
			z-index: 11 !important;
		}
		
		/* Centrado completo del banner - Estilos base para cada slide */
		.banner_w3lspvt .csslider > ul > li {
			vertical-align: top !important;
			margin: 0 !important;
			padding: 0 !important;
			background-size: cover !important;
			background-position: center center !important;
			background-repeat: no-repeat !important;
			position: relative !important;
			box-sizing: border-box !important;
			white-space: normal !important;
		}
		
		/* Imágenes de fondo para cada slide del carrusel */
		.banner_w3lspvt .csslider > ul > li:first-child {
			background-image: url('<?php echo I18n::sharedAsset('1.webp'); ?>') !important;
		}
		
		.banner_w3lspvt .csslider > ul > li:nth-child(2) {
			background-image: url('<?php echo I18n::sharedAsset('2.webp'); ?>') !important;
		}
		
		.banner_w3lspvt .csslider > ul > li:nth-child(3) {
			background-image: url('<?php echo I18n::sharedAsset('piesplaya.webp'); ?>') !important;
		}
		
		/* CSS para el carrusel con inputs de radio - Control de visibilidad */
		.csslider > input {
			display: none;
		}
		
		.banner_w3lspvt .csslider > ul {
			list-style: none !important;
			margin: 0 !important;
			padding: 0 !important;
			width: 300% !important;
			height: 100vh !important;
			display: flex !important;
			transition: transform 0.8s ease-in-out !important;
			position: relative !important;
		}
		
		.banner_w3lspvt .csslider > ul > li {
			width: 33.33% !important;
			height: 100vh !important;
			min-height: 100vh !important;
			flex-shrink: 0 !important;
			display: block !important;
		}
		
		/* Controlar la posición del carrusel según el input seleccionado */
		#slides_1:checked ~ ul {
			transform: translateX(0%);
		}
		
		#slides_2:checked ~ ul {
			transform: translateX(-33.33%);
		}
		
		#slides_3:checked ~ ul {
			transform: translateX(-66.66%);
		}
		
		/* Estilos para los labels (flechas) del carrusel */
		.csslider .arrows {
			position: absolute;
			bottom: 30px;
			left: 50%;
			transform: translateX(-50%);
			z-index: 100;
			display: flex;
			gap: 10px;
		}
		
		.csslider .arrows label {
			width: 12px;
			height: 12px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.5);
			cursor: pointer;
			transition: background 0.3s ease;
		}
		
		.csslider .arrows label:hover {
			background: rgba(255, 255, 255, 0.8);
		}
		
		#slides_1:checked ~ .arrows label:nth-child(1),
		#slides_2:checked ~ .arrows label:nth-child(2),
		#slides_3:checked ~ .arrows label:nth-child(3) {
			background: rgba(255, 255, 255, 1);
		}
		
		/* Auto-play para el carrusel con clase infinity */
		.csslider.infinity #slides_1:checked ~ ul {
			animation: slide1 20s infinite;
		}
		
		.csslider.infinity #slides_2:checked ~ ul {
			animation: slide2 20s infinite;
		}
		
		.csslider.infinity #slides_3:checked ~ ul {
			animation: slide3 20s infinite;
		}
		
		@keyframes slide1 {
			0% { transform: translateX(0%); }
			25% { transform: translateX(0%); }
			33.33% { transform: translateX(-33.33%); }
			58.33% { transform: translateX(-33.33%); }
			66.66% { transform: translateX(-66.66%); }
			91.66% { transform: translateX(-66.66%); }
			100% { transform: translateX(0%); }
		}
		
		@keyframes slide2 {
			0% { transform: translateX(-33.33%); }
			25% { transform: translateX(-33.33%); }
			33.33% { transform: translateX(-66.66%); }
			58.33% { transform: translateX(-66.66%); }
			66.66% { transform: translateX(0%); }
			91.66% { transform: translateX(0%); }
			100% { transform: translateX(-33.33%); }
		}
		
		@keyframes slide3 {
			0% { transform: translateX(-66.66%); }
			25% { transform: translateX(-66.66%); }
			33.33% { transform: translateX(0%); }
			58.33% { transform: translateX(0%); }
			66.66% { transform: translateX(-33.33%); }
			91.66% { transform: translateX(-33.33%); }
			100% { transform: translateX(-66.66%); }
		}
		
		/* Estilos para la nueva sección CTA con imagen de fondo */
		.cta-section-new {
			position: relative;
			overflow: hidden;
		}
		
		.cta-section-new a {
			transition: all 0.3s ease;
		}
		
		.cta-section-new a:hover {
			background-color: #f0f0f0 !important;
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4) !important;
		}
		
		/* Responsive para CTA */
		@media (max-width: 768px) {
			.cta-section-new {
				padding: 100px 20px !important;
				min-height: 500px !important;
			}
			
			#texto-cta-titulo {
				font-size: 3.5rem !important;
				letter-spacing: 1px !important;
				margin-bottom: 20px !important;
			}
			
			#texto-cta-subtitulo {
				font-size: 1.3rem !important;
			}
			
			.cta-section-new > div > div {
				margin-bottom: 40px !important;
			}
			
			.cta-section-new a {
				padding: 15px 40px !important;
				font-size: 1rem !important;
			}
		}
		
		@media (max-width: 480px) {
			.cta-section-new {
				padding: 80px 15px !important;
				min-height: 400px !important;
			}
			
			#texto-cta-titulo {
				font-size: 2.8rem !important;
				letter-spacing: 0.5px !important;
				margin-bottom: 15px !important;
			}
			
			#texto-cta-subtitulo {
				font-size: 1.1rem !important;
			}
			
			.cta-section-new > div > div {
				margin-bottom: 30px !important;
			}
			
			.cta-section-new a {
				padding: 12px 30px !important;
				font-size: 0.9rem !important;
			}
		}
		
		/* Optimización para 4K */
		@media (min-width: 2560px) {
			.cta-section-new {
				min-height: 800px !important;
				padding: 200px 20px !important;
			}
			
			#texto-cta-titulo {
				font-size: 7rem !important;
				letter-spacing: 3px !important;
			}
			
			#texto-cta-subtitulo {
				font-size: 2.2rem !important;
			}
			
			.cta-section-new > div > div {
				margin-bottom: 80px !important;
			}
			
			.cta-section-new a {
				padding: 25px 70px !important;
				font-size: 1.5rem !important;
			}
		}
		
		/* Estilos del ul ya están definidos arriba para el carrusel */
		
		.banner_w3lspvt .csslider > ul > li > .container {
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			width: 100% !important;
			height: 100% !important;
			text-align: center !important;
			min-height: 100vh !important;
		}
		
		/* Estilos del li ya están definidos arriba para el carrusel */
		
		/* Estilos de fuente para el banner */
		.w3ls_banner_txt {
			text-align: center !important;
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			justify-content: center !important;
			width: 100% !important;
			margin: 0 auto !important;
		}
		
		.w3ls_banner_txt p,
		.w3ls_pvt-title {
			font-family: 'Migra', serif !important;
			font-weight: 2000 !important;
			letter-spacing: 2px !important;
			text-align: center !important;
			width: 100% !important;
			margin-left: auto !important;
			margin-right: auto !important;
		}
		
		.w3ls_pvt-title {
			font-size: 85px !important;
			font-weight: 2000 !important;
			text-transform: uppercase !important;
			margin: 0 auto !important;
			text-align: center !important;
			text-shadow: 6px 6px 5px rgba(0, 0, 0, 0.5) !important;
		}
		
		/* Estilos para el contenido del banner integrado */
		.banner-content-wrapper {
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			justify-content: center !important;
			gap: 20px !important;
			padding: 40px 20px !important;
		}
		
		.banner-main-title {
			font-size: 70px !important;
			margin-bottom: 15px !important;
		}
		
		.banner-subtitle {
			font-family: 'Crimson Text', serif !important;
			font-size: 1.5rem !important;
			font-weight: 400 !important;
			font-style: italic !important;
			text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6) !important;
			margin: 0 !important;
			max-width: 800px !important;
			line-height: 1.6 !important;
		}
		
		.banner-buttons-wrapper {
			display: flex !important;
			justify-content: center !important;
			align-items: center !important;
			margin: 10px 0 !important;
		}
		
		.btn-registrate-ahora-banner {
			display: inline-block !important;
			background-color: #fff !important;
			color: #333 !important;
			padding: 15px 40px !important;
			font-family: 'Oxygen', sans-serif !important;
			font-size: 1.1rem !important;
			font-weight: 600 !important;
			text-decoration: none !important;
			border-radius: 50px !important;
			transition: all 0.3s ease !important;
			box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
			text-align: center !important;
		}
		
		.btn-registrate-ahora-banner:hover {
			background-color: #f0f0f0 !important;
			transform: translateY(-2px) !important;
			box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4) !important;
		}
		
		.banner-counter {
			font-family: 'Crimson Text', serif !important;
			font-size: 1.3rem !important;
			font-weight: 400 !important;
			font-style: italic !important;
			text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6) !important;
			margin: 10px 0 !important;
		}
		
		.banner-counter strong {
			font-weight: 700 !important;
			font-size: 1.4rem !important;
		}
		
		.btn-reservar-ahora-banner {
			padding: 15px 40px !important;
			font-size: 18px !important;
			font-weight: 600 !important;
			color: #333 !important;
			background-color: #FFE082 !important;
			border: none !important;
			border-radius: 50px !important;
			cursor: pointer !important;
			transition: all 0.3s ease !important;
			font-family: 'Oxygen', 'Arial', sans-serif !important;
			text-transform: uppercase !important;
			letter-spacing: 1px !important;
			box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
		}
		
		.btn-reservar-ahora-banner:hover {
			background-color: #FFD54F !important;
			box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
			transform: translateY(-2px) !important;
		}
		
		.btn-reservar-ahora-banner:active {
			background-color: #FFC107 !important;
			transform: translateY(0) !important;
		}
		
		/* Botón Reservar Ahora */
		.btn-reservar-ahora {
			padding: 15px 40px;
			font-size: 18px;
			font-weight: 600;
			color: #333;
			background-color: #FFE082;
			border: none !important;
			border-radius: 50px;
			cursor: pointer;
			transition: all 0.3s ease;
			font-family: 'Oxygen', 'Arial', sans-serif;
			text-transform: uppercase;
			letter-spacing: 1px;
			box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
		}
		
		/* Responsive para botón en móviles */
		@media (max-width: 768px) {
			.w3ls_pvt-title {
				font-size: 40px !important;
				letter-spacing: 1px !important;
				text-shadow: 4px 4px 4px rgba(0, 0, 0, 0.5) !important;
			}
			
			.banner-main-title {
				font-size: 33px !important;
				margin-bottom: 10px !important;
			}
			
			.banner-content-wrapper {
				gap: 15px !important;
				padding: 30px 15px !important;
			}
			
			.banner-subtitle {
				font-size: 1.2rem !important;
				margin: 0 !important;
			}
			
			.banner-counter {
				font-size: 1.1rem !important;
				margin: 8px 0 !important;
			}
			
			.banner-counter strong {
				font-size: 1.2rem !important;
			}
			
			.btn-registrate-ahora-banner {
				padding: 12px 30px !important;
				font-size: 1rem !important;
			}
			
			.btn-reservar-ahora-banner {
				padding: 12px 30px !important;
				font-size: 16px !important;
			}
		}
		
		@media (max-width: 480px) {
			.w3ls_pvt-title {
				font-size: 28px !important;
				letter-spacing: 0.5px !important;
				text-shadow: 3px 3px 3px rgba(0, 0, 0, 0.5) !important;
			}
			
			.banner-main-title {
				font-size: 28px !important;
				margin-bottom: 8px !important;
			}
			
			.banner-content-wrapper {
				gap: 12px !important;
				padding: 20px 10px !important;
			}
			
			.banner-subtitle {
				font-size: 1rem !important;
				line-height: 1.4 !important;
			}
			
			.banner-counter {
				font-size: 0.95rem !important;
				margin: 6px 0 !important;
			}
			
			.banner-counter strong {
				font-size: 1.05rem !important;
			}
			
			.btn-registrate-ahora-banner {
				padding: 10px 25px !important;
				font-size: 0.9rem !important;
			}
			
			.btn-reservar-ahora-banner {
				padding: 10px 25px !important;
				font-size: 14px !important;
				letter-spacing: 0.5px !important;
			}
		}
		
		/* Responsive para sección de texto y carrusel */
		@media (max-width: 768px) {
			#texto-carrusel h2 {
				font-size: 2.5rem !important;
			}
			#texto-carrusel p {
				font-size: 18px !important;
			}
			#blogCarousel {
				margin-right: 0 !important;
				margin-top: 20px;
			}
			#blogCarousel .carousel-item img {
				height: 400px !important;
			}
			#blogCarousel .carousel-control-prev {
				left: 10px !important;
				right: auto !important;
			}
			#blogCarousel .carousel-control-next {
				right: 10px !important;
				left: auto !important;
			}
			#blogCarousel .carousel-control-prev,
			#blogCarousel .carousel-control-next {
				width: 40px !important;
				height: 40px !important;
				display: flex !important;
				align-items: center !important;
				justify-content: center !important;
				background: rgba(0, 0, 0, 0.3) !important;
				border-radius: 50% !important;
				opacity: 0.9 !important;
				z-index: 10 !important;
			}
			#blogCarousel .carousel-control-prev:hover,
			#blogCarousel .carousel-control-next:hover {
				background: rgba(0, 0, 0, 0.5) !important;
				opacity: 1 !important;
			}
			#blogCarousel .carousel-control-prev span,
			#blogCarousel .carousel-control-next span {
				font-size: 30px !important;
				line-height: 1 !important;
			}
			.cta-section {
				padding: 80px 15px !important;
				margin-top: 40px !important;
			}
			.cta-section p {
				font-size: 1.8rem !important;
			}
		}
		
		@media (max-width: 480px) {
			#texto-carrusel {
				padding: 15px !important;
			}
			#texto-carrusel h2 {
				font-size: 2rem !important;
				margin-bottom: 15px !important;
			}
			#texto-carrusel p {
				font-size: 16px !important;
				line-height: 1.6 !important;
				margin-bottom: 12px !important;
			}
			#blogCarousel {
				margin-top: 15px;
			}
			#blogCarousel .carousel-item img {
				height: 300px !important;
			}
			#blogCarousel .carousel-control-prev {
				left: 5px !important;
				right: auto !important;
			}
			#blogCarousel .carousel-control-next {
				right: 5px !important;
				left: auto !important;
			}
			#blogCarousel .carousel-control-prev,
			#blogCarousel .carousel-control-next {
				width: 35px !important;
				height: 35px !important;
				display: flex !important;
				align-items: center !important;
				justify-content: center !important;
				background: rgba(0, 0, 0, 0.4) !important;
				border-radius: 50% !important;
				opacity: 0.9 !important;
				z-index: 10 !important;
			}
			#blogCarousel .carousel-control-prev:hover,
			#blogCarousel .carousel-control-next:hover {
				background: rgba(0, 0, 0, 0.6) !important;
				opacity: 1 !important;
			}
			#blogCarousel .carousel-control-prev span,
			#blogCarousel .carousel-control-next span {
				font-size: 24px !important;
				line-height: 1 !important;
			}
			.cta-section {
				padding: 60px 10px !important;
				margin-top: 30px !important;
			}
			.cta-section p {
				font-size: 1.4rem !important;
				line-height: 1.5 !important;
			}
		}
		
		.btn-reservar-ahora:hover {
			background-color: #FFD54F;
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
		}
		
		.btn-reservar-ahora:active {
			transform: translateY(0);
			box-shadow: 0 2px 10px rgba(255, 193, 7, 0.3);
		}
		
		/* Estilos para el botón de pago */
		#pagoBtn, .btn-pago {
			background-color: #FFE082 !important;
			color: #333 !important;
			padding: 12px 30px !important;
			font-family: 'Oxygen', sans-serif !important;
			font-size: 16px !important;
			font-weight: 600 !important;
			border: none !important;
			border-radius: 50px !important;
			transition: all 0.3s ease !important;
			box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
			text-transform: uppercase !important;
			letter-spacing: 1px !important;
			cursor: pointer !important;
		}
		
		#pagoBtn:hover, .btn-pago:hover {
			background-color: #FFD54F !important;
			color: #333 !important;
			box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
			transform: translateY(-2px);
		}
		
		#pagoBtn:active, .btn-pago:active {
			background-color: #FFC107 !important;
			transform: translateY(0);
		}
		
		/* Estilos del Carrusel de Blog */
		#blogCarousel {
			position: relative;
		}
		
		#blogCarousel .carousel-inner {
			overflow: hidden !important;
			position: relative !important;
			width: 100% !important;
		}
		
		/* Animación de entrada para las imágenes del carrusel */
		@keyframes slideInFromRight {
			from {
				transform: translateX(100%);
				opacity: 0.8;
			}
			to {
				transform: translateX(0);
				opacity: 1;
			}
		}
		
		@keyframes slideInFromLeft {
			from {
				transform: translateX(-100%);
				opacity: 0.8;
			}
			to {
				transform: translateX(0);
				opacity: 1;
			}
		}
		
		@-webkit-keyframes slideInFromRight {
			from {
				-webkit-transform: translateX(100%);
				opacity: 0.8;
			}
			to {
				-webkit-transform: translateX(0);
				opacity: 1;
			}
		}
		
		@-webkit-keyframes slideInFromLeft {
			from {
				-webkit-transform: translateX(-100%);
				opacity: 0.8;
			}
			to {
				-webkit-transform: translateX(0);
				opacity: 1;
			}
		}
		
		/* Forzar transición rápida y fluida en TODOS los items del carrusel */
		#blogCarousel .carousel-item,
		#blogCarousel .carousel-inner .carousel-item,
		.carousel-item,
		.carousel-inner .carousel-item {
			transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
			-webkit-transition: -webkit-transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
			transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.5s ease !important;
		}
		
		/* Animación cuando la imagen entra desde la derecha */
		#blogCarousel .carousel-item.carousel-item-next,
		#blogCarousel .carousel-item.active.carousel-item-left {
			animation: slideInFromRight 0.5s ease-out !important;
			-webkit-animation: slideInFromRight 0.5s ease-out !important;
		}
		
		/* Animación cuando la imagen entra desde la izquierda */
		#blogCarousel .carousel-item.carousel-item-prev,
		#blogCarousel .carousel-item.active.carousel-item-right {
			animation: slideInFromLeft 0.5s ease-out !important;
			-webkit-animation: slideInFromLeft 0.5s ease-out !important;
		}
		
		#blogCarousel .carousel-item img {
			display: block !important;
			width: 100% !important;
			/* height se define inline en cada imagen, no sobrescribir aquí */
			object-fit: cover !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		/* Asegurar que las imágenes estén siempre visibles durante la transición */
		#blogCarousel .carousel-item.active,
		#blogCarousel .carousel-item-next,
		#blogCarousel .carousel-item-prev,
		.carousel-item.active,
		.carousel-item-next,
		.carousel-item-prev {
			display: block !important;
		}
		
		/* Forzar transición suave en TODOS los estados posibles */
		#blogCarousel .carousel-item.active,
		#blogCarousel .carousel-item.carousel-item-next,
		#blogCarousel .carousel-item.carousel-item-prev,
		#blogCarousel .carousel-item.carousel-item-start,
		#blogCarousel .carousel-item.carousel-item-end,
		#blogCarousel .carousel-item.active.carousel-item-left,
		#blogCarousel .carousel-item.active.carousel-item-right,
		.carousel-item.active,
		.carousel-item.carousel-item-next,
		.carousel-item.carousel-item-prev,
		.carousel-item.carousel-item-start,
		.carousel-item.carousel-item-end,
		.carousel-item.active.carousel-item-left,
		.carousel-item.active.carousel-item-right {
			transition: transform 0.4s ease !important;
			-webkit-transition: -webkit-transform 0.4s ease !important;
			transition: transform 0.4s ease, -webkit-transform 0.4s ease !important;
		}
		
		/* Asegurar que translate3d también use la transición */
		@supports ((-webkit-transform-style: preserve-3d) or (transform-style: preserve-3d)) {
			#blogCarousel .carousel-item,
			.carousel-item {
				transition: transform 0.4s ease !important;
				-webkit-transition: -webkit-transform 0.4s ease !important;
				transition: transform 0.4s ease, -webkit-transform 0.4s ease !important;
			}
		}
		
		#blogCarousel .carousel-control-prev,
		#blogCarousel .carousel-control-next {
			width: auto;
			height: auto;
			background: none !important;
			border: none !important;
			top: 50%;
			transform: translateY(-50%);
			opacity: 0.8;
			transition: all 0.3s ease;
		}
		
		#blogCarousel .carousel-control-prev {
			left: 20px;
		}
		
		#blogCarousel .carousel-control-next {
			right: 20px;
		}
		
		#blogCarousel .carousel-control-prev:hover,
		#blogCarousel .carousel-control-next:hover {
			opacity: 1;
			transform: translateY(-50%) scale(1.2);
		}
		
		#blogCarousel .carousel-control-prev span,
		#blogCarousel .carousel-control-next span {
			display: block;
			line-height: 1;
		}
		
		/* Animación de scroll para el texto del carrusel */
		.scroll-reveal-text {
			opacity: 0;
			transform: translateY(100px);
			transition: opacity 1.2s ease-out, transform 1.2s ease-out;
		}
		
		.scroll-reveal-text.revealed {
			opacity: 1;
			transform: translateY(0);
		}
		
		.scroll-reveal-left {
			opacity: 0;
			transform: translateX(-100px);
			transition: opacity 1.2s ease-out, transform 1.2s ease-out;
		}
		
		.scroll-reveal-left.revealed {
			opacity: 1;
			transform: translateX(0);
		}
		
		/* Estilos del Calendario - Aplicando estilos del formulario */
		.calendar-container {
			background: #ffffff !important;
			border-radius: 0 !important;
			box-shadow: none !important;
			padding: 30px 40px !important;
			max-width: 1100px;
			margin: 0 auto;
			border: none !important;
			overflow: visible; /* Permitir que las etiquetas de precio se muestren */
		}
		
		.calendar-header {
			text-align: center;
			margin-bottom: 20px;
		}
		
		.calendar-header h3 {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 1.5rem !important;
			font-weight: 400 !important;
			color: #333 !important;
			margin-bottom: 20px;
		}
		
		.calendar-navigation {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 30px;
			padding: 0 20px;
		}
		
		.calendar-navigation button,
		.calendar-navigation #prevMonth,
		.calendar-navigation #nextMonth {
			background: transparent !important;
			color: #333 !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
			width: auto !important;
			height: auto !important;
			font-size: 24px !important;
			font-weight: 400 !important;
			cursor: pointer !important;
			transition: border-bottom-color 0.3s ease !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			padding: 10px 0 !important;
			font-family: 'Oxygen', sans-serif !important;
		}
		
		.calendar-navigation button:hover,
		.calendar-navigation #prevMonth:hover,
		.calendar-navigation #nextMonth:hover {
			background: transparent !important;
			color: #333 !important;
			border-bottom: 1px solid #333 !important;
			transform: none !important;
		}
		
		.month-display {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 1.5rem !important;
			font-weight: 400 !important;
			color: #333 !important;
		}
		
		.calendar-grid {
			display: grid;
			grid-template-columns: repeat(7, 1fr);
			gap: 10px;
			margin-bottom: 20px;
			max-width: 1080px;
			margin-left: auto;
			margin-right: auto;
			grid-auto-rows: minmax(85px, auto);
			overflow: visible; /* Permitir que las etiquetas de precio se muestren */
		}
		
		.calendar-day {
			aspect-ratio: 1;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			transition: border-bottom-color 0.3s ease;
			position: relative;
			min-height: 85px;
			font-size: 18px;
			font-family: 'Oxygen', sans-serif !important;
			border-radius: 0 !important;
			background: #ffffff !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			overflow: visible; /* Permitir que las etiquetas de precio se muestren fuera del contenedor */
		}
		
		.calendar-day.available {
			background: #ffffff !important;
			color: #333 !important;
			border-bottom: 1px solid #e1e5e9 !important;
			box-shadow: none !important;
		}

        /* Evitar que el borde de selección cambie el tamaño del día
   cuando el día ya tiene clases especiales de check-in/checkout */
        .calendar-day.day-checkout-available.checkin,
        .calendar-day.day-checkin-only.checkin,
        .calendar-day.day-checkout-available.checkout,
        .calendar-day.day-checkin-only.checkout {
            box-sizing: border-box !important;
            border: 3px solid #FFC107 !important;
        }

        /* Triángulo superior izquierdo - día de checkout disponible */
        .calendar-day.day-checkout-available {
            background: #ffffff !important;
            cursor: pointer !important;
            border-bottom: 1px solid #e1e5e9 !important;
            overflow: visible !important;
        }

        .calendar-day.day-checkout-available::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 18px 18px 0 0;
            border-color: #e57373 transparent transparent transparent;
            z-index: 2;
            pointer-events: none;
        }

        /* Triángulo inferior derecho - día de checkin ocupado */
        .calendar-day.day-checkin-only {
            background: #ffffff !important;
            cursor: pointer !important;
            border-bottom: 1px solid #e1e5e9 !important;
            overflow: visible !important;
        }

        /* IMPORTANTE: usar un pseudo-elemento que no conflictúe con selected-day-price */
        .calendar-day.day-checkin-only::before {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 0 18px 18px;
            border-color: transparent transparent #e57373 transparent;
            z-index: 2;
            pointer-events: none;
        }

        /* Día completamente bloqueado (checkout + checkin el mismo día) */
        .calendar-day.day-checkin-checkout {
            background: #ffffff !important;
            cursor: not-allowed !important;
            opacity: 0.5;
            border-bottom: 1px solid #e1e5e9 !important;
        }

        .calendar-day.day-checkin-checkout::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 18px 18px 0 0;
            border-color: #e57373 transparent transparent transparent;
            z-index: 2;
            pointer-events: none;
        }

        /* Mantener números y precios por encima de los triángulos */
        .calendar-day.day-checkout-available .day-number,
        .calendar-day.day-checkout-available .day-price,
        .calendar-day.day-checkin-only .day-number,
        .calendar-day.day-checkin-only .day-price,
        .calendar-day.day-checkin-checkout .day-number,
        .calendar-day.day-checkin-checkout .day-price {
            position: relative;
            z-index: 3;
        }

        .calendar-day.day-checkout-available:hover {
            border-bottom: 1px solid #333 !important;
        }

        .calendar-day.day-checkin-only::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 0 18px 18px;
            border-color: transparent transparent #e57373 transparent;
            z-index: 2;
        }

        .calendar-day.day-checkin-only:hover {
            border-bottom: 1px solid #333 !important;
        }

        .calendar-day.day-checkin-checkout::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 0 18px 18px;
            border-color: transparent transparent #e57373 transparent;
            z-index: 2;
        }

		.calendar-day.available:hover {
			background: #ffffff !important;
			border-bottom: 1px solid #333 !important;
			transform: none !important;
			box-shadow: none !important;
		}
		
		.calendar-day.occupied {
			background: #ffffff !important;
			color: #999 !important;
			cursor: not-allowed;
			border-bottom: 1px solid #e1e5e9 !important;
			opacity: 0.5;
			position: relative;
		}
		
		.calendar-day.not-available {
			position: relative;
			opacity: 0.6;
		}
		.calendar-day.not-available::after {
			content: '';
			position: absolute;
			top: 50%;
			left: 0;
			right: 0;
			height: 3px;
			background: #dc3545;
			transform: translateY(-50%);
			z-index: 10;
		}
		.calendar-day.occupied::after {
			content: '';
			position: absolute;
			top: 50%;
			left: 0;
			right: 0;
			height: 2px;
			background-color: #ff0000;
			transform: translateY(-50%);
			z-index: 1;
		}
		
		.calendar-day.checkin {
			background: #FFF9C4 !important;
			color: #333 !important;
			border: 3px solid #FFC107 !important;
			border-bottom: 4px solid #FFC107 !important;
			font-weight: 600 !important;
			transform: none !important;
			box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3) !important;
		}
		
		.calendar-day.checkout {
			background: #E3F2FD !important;
			color: #333 !important;
			border: 3px solid #2196F3 !important;
			border-bottom: 4px solid #2196F3 !important;
			font-weight: 600 !important;
			transform: none !important;
			box-shadow: 0 2px 4px rgba(33, 150, 243, 0.3) !important;
		}
		
		.calendar-day.in-range {
			background: #E1F5FE !important;
			color: #333 !important;
			border: 2px solid #BBDEFB !important;
			border-bottom: 2px solid #BBDEFB !important;
			font-weight: 500 !important;
			box-shadow: 0 1px 3px rgba(187, 222, 251, 0.4) !important;
		}
		
		.calendar-day.other-month {
			color: #999 !important;
			border-bottom: 1px solid #e1e5e9 !important;
			background: #ffffff !important;
			opacity: 0.5;
		}
		
		.day-price {
			font-size: 0.7rem;
			position: absolute;
			bottom: 6px;
			left: 50%;
			transform: translateX(-50%);
			color: #333 !important;
			font-weight: 400 !important;
			font-family: 'Oxygen', sans-serif !important;
			background: transparent !important;
			padding: 3px 8px;
			border-radius: 0 !important;
		}
		
		/* Etiqueta de precio sobre días seleccionados (solo móviles/tablets) */
		.selected-day-price {
			position: absolute;
			top: -18px;
			left: 0;
			right: 0;
			width: 100%;
			transform: translateY(-5px);
			background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
			border: 1.5px solid #333;
			border-bottom: 2.5px solid #333;
			border-radius: 4px;
			padding: 5px 4px;
			font-size: 0.65rem;
			color: #333;
			font-weight: 600;
			font-family: 'Oxygen', sans-serif;
			text-align: center;
			z-index: 100;
			box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1);
			display: none; /* Oculto por defecto, solo visible en móviles/tablets */
			line-height: 1.3;
			letter-spacing: 0.3px;
			transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
			box-sizing: border-box;
		}
		
		/* Estilos específicos según el tipo de día seleccionado */
		.calendar-day.checkin .selected-day-price {
			background: linear-gradient(135deg, #FFF9C4 0%, #FFF59D 100%);
			border-color: #FFC107;
			border-bottom-color: #FFA000;
			color: #E65100;
			box-shadow: 0 3px 8px rgba(255, 193, 7, 0.4), 0 1px 3px rgba(255, 193, 7, 0.2);
		}
		
		.calendar-day.checkout .selected-day-price {
			background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
			border-color: #2196F3;
			border-bottom-color: #1976D2;
			color: #0D47A1;
			box-shadow: 0 3px 8px rgba(33, 150, 243, 0.4), 0 1px 3px rgba(33, 150, 243, 0.2);
		}
		
		.calendar-day.in-range .selected-day-price {
			background: linear-gradient(135deg, #E1F5FE 0%, #B3E5FC 100%);
			border-color: #03A9F4;
			border-bottom-color: #0288D1;
			color: #01579B;
			box-shadow: 0 3px 8px rgba(3, 169, 244, 0.35), 0 1px 3px rgba(3, 169, 244, 0.2);
		}
		
		/* Pequeño triángulo apuntando hacia el día */
		.selected-day-price::after {
			content: '';
			position: absolute;
			bottom: -6px;
			left: 50%;
			transform: translateX(-50%);
			width: 0;
			height: 0;
			border-left: 5px solid transparent;
			border-right: 5px solid transparent;
			border-top: 6px solid #333;
		}
		
		.calendar-day.checkin .selected-day-price::after {
			border-top-color: #FFA000;
		}
		
		.calendar-day.checkout .selected-day-price::after {
			border-top-color: #1976D2;
		}
		
		.calendar-day.in-range .selected-day-price::after {
			border-top-color: #0288D1;
		}
		
		/* Mostrar etiquetas de precio en días seleccionados solo en móviles/tablets */
		@media (max-width: 992px) {
			.selected-day-price {
				display: block;
				animation: priceLabelFadeIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
			}
		}
		
		@keyframes priceLabelFadeIn {
			0% {
				opacity: 0;
				transform: translateY(-15px) scale(0.85);
			}
			100% {
				opacity: 1;
				transform: translateY(-5px) scale(1);
			}
		}
		
		/* Ocultar en desktop */
		@media (min-width: 993px) {
			.selected-day-price {
				display: none !important;
			}
		}
		
		.calendar-legend {
			display: flex;
			justify-content: center;
			gap: 30px;
			flex-wrap: wrap;
			font-size: 15px !important;
			font-family: 'Oxygen', sans-serif !important;
			padding: 20px 0 !important;
			border-radius: 0 !important;
			background: transparent !important;
			border-top: 1px solid #e1e5e9 !important;
			border-bottom: 1px solid #e1e5e9 !important;
		}
		
		/* Estilos para la leyenda en el resumen de reserva - Aplicando estilos del formulario */
		.reservation-legend {
			margin-top: 30px;
			padding-top: 20px;
			border-top: 1px solid #e1e5e9 !important;
		}
		
		.reservation-legend .legend-title {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 1.5rem !important;
			font-weight: 400 !important;
			color: #333 !important;
			margin-top: 0 !important;
			text-align: left !important;
		}
		
		.reservation-legend .legend-description {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 15px !important;
			color: #333 !important;
			text-align: left !important;
		}
		
		.reservation-legend .calendar-legend {
			padding: 20px 0 !important;
			border-radius: 0 !important;
			gap: 20px;
			font-size: 15px !important;
			background: transparent !important;
			border-top: 1px solid #e1e5e9 !important;
			border-bottom: 1px solid #e1e5e9 !important;
		}
		
		.calendar-legend-section {
			display: flex;
			justify-content: flex-start;
			gap: 30px;
			flex-wrap: wrap;
			padding: 15px 0 !important;
			border-bottom: 1px solid #e1e5e9 !important;
		}
		
		.calendar-legend-section:last-of-type {
			border-bottom: none !important;
		}
		
		.legend-item {
			display: flex;
			align-items: center;
			gap: 8px;
			color: #333 !important;
			font-family: 'Oxygen', sans-serif !important;
		}
		
		.legend-color {
			width: 20px;
			height: 20px;
			border-radius: 0 !important;
		}
		
		.legend-color.available { 
			background: #ffffff !important;
			border: 1px solid #e1e5e9 !important;
		}
		.legend-color.occupied { 
			background: transparent !important;
			border: none !important;
			opacity: 1;
			position: relative;
		}
		.legend-color.occupied::after {
			content: '';
			position: absolute;
			top: 50%;
			left: 0;
			right: 0;
			height: 2px;
			background: #dc3545;
			transform: translateY(-50%);
			z-index: 10;
		}
		.legend-color.checkin { 
			background: #ffffff !important;
			border: 3px solid #FFC107 !important;
		}
		.legend-color.checkout { 
			background: #ffffff !important;
			border: 3px solid #2196F3 !important;
		}
		.legend-color.in-range { 
			background: #ffffff !important;
			border: 1px solid #BBDEFB !important;
		}
		
		/* Zoom aplicado a toda la página - 75% pero ocupando todo el ancho (igual que header.scrolled) */
		html {
			zoom: 0.75 !important;
			width: 100% !important;
			height: 100% !important;
			overflow-x: hidden !important;
		}
		
		body {
			zoom: 1 !important;
			position: relative !important;
			left: 0 !important;
			right: 0 !important;
			width: 100% !important;
			min-width: 100% !important;
			max-width: 100% !important;
			min-height: 100% !important;
			overflow-x: hidden !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		/* Asegurar que todos los contenedores principales ocupen todo el ancho (igual que header.scrolled) */
		.main-top {
			position: relative !important;
			left: 0 !important;
			right: 0 !important;
			width: 100% !important;
			max-width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		/* Asegurar que el banner dentro de main-top ocupe toda la pantalla */
		.main-top > .banner_w3lspvt {
			margin: 0 !important;
			padding: 0 !important;
		}
		
		.container-fluid,
		.container {
			width: 100% !important;
			max-width: 100% !important;
			padding-left: 0 !important;
			padding-right: 0 !important;
		}
		
		/* Asegurar que las secciones principales ocupen todo el ancho */
		section,
		div[class*="section"],
		#reservation-section {
			width: 100% !important;
			max-width: 100% !important;
		}
		
		/* Fondo amarillo para la sección de reservas */
		#reservation-section {
			background-color: rgb(235, 234, 223);
			padding-top: 60px !important;
			padding-bottom: 60px !important;
		}
		
		#reservation-section .container-fluid {
			max-width: 1400px;
			margin: 0 auto;
		}
		
		/* Alineación vertical del calendario y resumen */
		#reservation-section .row {
			display: flex;
			align-items: flex-start;
		}
		
		#reservation-section .col-lg-7,
		#reservation-section .col-lg-5 {
			display: flex;
			flex-direction: column;
		}
		
		/* Ajuste para que el resumen comience a la misma altura que el calendario */
		#reservation-section .col-lg-5 {
			padding-top: 0 !important; /* Ya no necesita padding porque el título está en otra fila */
		}
		
		#reservation-section .col-lg-5 .reservation-summary {
			margin-top: 0 !important;
			margin-left: -90px !important; /* Mover más a la izquierda */
		}
		
		/* Estilos del Resumen de Reserva (Sidebar) - Aplicando estilos del formulario */
		.reservation-summary {
			background: #ffffff !important;
			border-radius: 0 !important;
			padding: 30px 40px !important;
			min-height: 550px !important;
			box-shadow: none !important;
			margin-top: 0 !important;
			margin-left: 0 !important;
			width: 100%;
			border: none !important;
			max-width: 820px !important;
			display: flex !important;
			flex-direction: column !important;
		}
		
		/* Responsive para tablets */
		@media (max-width: 992px) {
			#reservation-section .col-lg-5 .reservation-summary {
				margin-left: 0 !important;
			}
			
			.reservation-summary {
				margin-left: 0 !important;
				padding: 25px 30px !important;
				height: auto !important;
				min-height: auto !important;
			}
			
			.reservation-summary h4 {
				font-size: 1.5rem !important;
			}
		}
		
		/* Responsive para móviles */
		@media (max-width: 768px) {
			#reservation-section {
				padding-top: 30px !important;
				padding-bottom: 30px !important;
			}
			
			#reservation-section .row {
				flex-direction: column;
			}
			
			#reservation-section .col-lg-5 {
				padding-top: 0 !important;
				width: 100% !important;
			}
			
			#reservation-section .col-lg-5 .reservation-summary {
				margin-left: 0 !important;
				margin-top: 20px !important;
			}
			
			.reservation-summary {
				padding: 20px 15px !important;
				margin-left: 0 !important;
				margin-right: 0 !important;
				width: 100% !important;
				max-width: 100% !important;
				min-height: auto !important;
			}
			
			.reservation-summary h4 {
				font-size: 1.3rem !important;
				margin-bottom: 20px !important;
				text-align: center !important;
			}
			
			.reservation-details {
				padding: 0 !important;
			}
			
			.detail-item {
				flex-direction: column !important;
				align-items: flex-start !important;
				gap: 5px !important;
				font-size: 14px !important;
				padding: 12px 0 !important;
			}
			
			.detail-item .label {
				font-size: 13px !important;
				margin-bottom: 5px;
			}
			
			.detail-item .value {
				font-size: 14px !important;
				font-weight: 500 !important;
			}
			
			.detail-item.total {
				flex-direction: row !important;
				justify-content: space-between !important;
				font-size: 16px !important;
				padding: 15px 0 !important;
			}
			
			#reserveBtn {
				font-size: 14px !important;
				padding: 12px 0 !important;
				margin-top: 20px !important;
			}
			
			.reservation-legend {
				margin-top: 20px !important;
				padding: 15px 0 !important;
			}
			
			.reservation-legend .legend-title {
				font-size: 1rem !important;
			}
			
			.reservation-legend .legend-description {
				font-size: 0.85rem !important;
			}
			
			.calendar-legend-section {
				flex-direction: column !important;
				gap: 10px !important;
			}
			
			.legend-item {
				font-size: 0.85rem !important;
			}
		}
		
		/* Responsive para móviles pequeños */
		@media (max-width: 480px) {
			.reservation-summary {
				padding: 15px 10px !important;
			}
			
			.reservation-summary h4 {
				font-size: 1.1rem !important;
				margin-bottom: 15px !important;
			}
			
			.detail-item {
				font-size: 13px !important;
				padding: 10px 0 !important;
			}
			
			.detail-item .label {
				font-size: 12px !important;
			}
			
			.detail-item .value {
				font-size: 13px !important;
			}
			
			.detail-item.total {
				font-size: 15px !important;
			}
			
			#reserveBtn {
				font-size: 13px !important;
				padding: 10px 0 !important;
			}
		}
		
		.reservation-summary h4 {
			font-size: 1.5rem !important;
			font-family: 'Oxygen', sans-serif !important;
			color: #333 !important;
			font-weight: 400 !important;
			margin-bottom: 30px;
			text-align: left !important;
		}
		
		.reservation-details {
			background: transparent !important;
			border-radius: 0 !important;
			padding: 0 !important;
			border: none !important;
			box-shadow: none !important;
		}
		
		.detail-item {
			display: flex;
			justify-content: space-between;
			margin-bottom: 0 !important;
			padding: 10px 0 !important;
			font-size: 15px !important;
			font-family: 'Oxygen', sans-serif !important;
			border-bottom: 1px solid #e1e5e9 !important;
		}
		
		.detail-item .label {
			color: #333 !important;
			font-weight: 400 !important;
		}
		
		.detail-item .value {
			font-weight: 400 !important;
			color: #333 !important;
		}
		
		.detail-item hr {
			display: none !important;
		}
		
		.detail-item.total {
			font-weight: 400 !important;
			font-size: 15px !important;
			border-top: 1px solid #e1e5e9 !important;
			border-bottom: 1px solid #e1e5e9 !important;
			padding-top: 15px !important;
			padding-bottom: 15px !important;
			margin-top: 10px !important;
			color: #333 !important;
		}
		
		#reserveBtn {
			font-weight: 600 !important;
			font-size: 15px !important;
			padding: 10px 0 !important;
			margin-top: 20px !important;
			background: transparent !important;
			border: none !important;
			border-bottom: 2px solid #333 !important;
			border-radius: 0 !important;
			color: #333 !important;
			transition: all 0.3s ease !important;
			font-family: 'Oxygen', sans-serif !important;
			box-shadow: none !important;
		}
		
		#reserveBtn:not(:disabled) {
			background-color: #FFE082 !important;
			border-bottom: 3px solid #FFC107 !important;
			color: #333 !important;
			cursor: pointer;
		}
		
		#reserveBtn:hover:not(:disabled) {
			background-color: #FFD54F !important;
			border-bottom: 3px solid #FFC107 !important;
			color: #333 !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
			font-weight: 700 !important;
		}
		
		#reserveBtn:disabled {
			background: transparent !important;
			border-bottom: 1px solid #e1e5e9 !important;
			color: #999 !important;
			cursor: not-allowed;
			opacity: 0.5;
			font-weight: 400 !important;
		}
		
		/* Estilos del Modal (Resumen de Costo) */
		.cost-summary-modal {
			background: linear-gradient(135deg, #eaf6ff, #d4edff);
			border-radius: 12px;
			padding: 20px;
			margin: 20px 0;
			border-left: 5px solid #007bff;
			color: #333;
		}
		
		.cost-summary-modal h6 {
			color: #007bff;
			border-bottom: 1px solid #b3d9ff;
			padding-bottom: 5px;
			margin-bottom: 10px;
			font-weight: 600;
		}
		
		.cost-summary-modal .row {
			margin-bottom: 5px;
		}
		
		.cost-summary-modal small {
			font-size: 0.9rem;
		}
		
		.cost-summary-modal strong {
			color: #2c3e50;
			font-size: 1.1rem;
		}
		
		.cost-summary-modal .text-success {
			font-weight: 600;
			color: #28a745 !important;
		}
		
		/* Adaptación para tablets */
		@media (max-width: 992px) and (min-width: 769px) {
			.day-price {
				display: none; /* Ocultar el precio en tablets para simplificar */
			}
		}
		
		/* Adaptación para dispositivos pequeños */
		@media (max-width: 768px) {
			.calendar-container {
				padding: 20px 10px;
			}
			.calendar-grid {
				gap: 2px;
				grid-auto-rows: minmax(45px, auto);
			}
			.calendar-day {
				font-size: 0.9rem;
				min-height: 45px;
			}
			.day-price {
				display: none; /* Ocultar el precio en móviles para simplificar */
			}
			.month-display {
				font-size: 1.5rem;
			}
			.calendar-legend {
				gap: 15px;
				font-size: 0.8rem;
				padding: 10px;
			}
			.legend-color {
				width: 14px;
				height: 14px;
			}
			/* Ajustar etiquetas de precio en móviles */
			.selected-day-price {
				top: -16px;
				padding: 4px 3px;
				font-size: 0.6rem;
			}
			.selected-day-price::after {
				border-left-width: 4px;
				border-right-width: 4px;
				border-top-width: 5px;
				bottom: -5px;
			}
		}
		
		/* Estilos para modal manual (fallback) */
		.modal {
			display: none;
			position: fixed;
			z-index: 1050;
			left: 0;
		}
		
		/* Tooltips */
		.tooltip {
			position: relative;
		}
		
		.tooltip .tooltiptext {
			visibility: hidden;
			width: 200px;
			background-color: #2c3e50;
			color: #fff;
			text-align: center;
			border-radius: 8px;
			padding: 8px;
			position: absolute;
			z-index: 1;
			bottom: 125%;
			left: 50%;
			margin-left: -100px;
			opacity: 0;
			transition: opacity 0.3s;
			font-size: 12px;
		}
		
		.tooltip:hover .tooltiptext {
			visibility: visible;
			opacity: 1;
		}
		
		/* Estilos del Modal */
		.cost-summary-modal {
			background: linear-gradient(135deg, #f8f9fa, #e9ecef);
			border-radius: 12px;
			padding: 20px;
			margin: 20px 0;
			border-left: 4px solid #3498db;
		}
		
		/* Estilos para modal manual (fallback) */
		.modal {
			display: none;
			position: fixed;
			z-index: 1050;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: hidden;
			background-color: rgba(0,0,0,0.5);
		}
		
		.modal.show {
			display: block !important;
		}
		
		.modal-dialog {
			position: relative;
			width: auto;
			margin: 0.5rem;
			pointer-events: none;
		}
		
		/* Estilos para el modal de reserva - mismo tamaño que el login */
		#reservationModal .modal-dialog {
			max-width: 1000px !important;
			margin: 1.75rem auto !important;
		}
		
		#reservationModal .modal-content {
			min-height: 500px !important;
			background-color: #ffffff !important;
			border-radius: 0 !important;
			box-shadow: none !important;
			border: none !important;
		}
		
		#reservationModal .modal-header {
			background: #ffffff !important;
			border-bottom: none !important;
			border-radius: 0 !important;
			padding: 30px 40px 20px 40px !important;
		}
		
		#reservationModal .modal-title {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 1.5rem !important;
			font-weight: 400 !important;
			color: #333 !important;
			margin: 0 !important;
			text-align: left !important;
		}
		
		#reservationModal .modal-body {
			padding: 20px 40px 40px 40px !important;
			font-family: 'Oxygen', sans-serif !important;
			background: #ffffff !important;
		}
		
		/* Estilos interactivos para inputs como el registro */
		#reservationModal .input-group-ihg {
			margin-bottom: 20px;
		}
		
		/* Estilo para el contenedor del icono y input de nombre */
		#reservationModal .input-group-ihg > div[style*="display: flex"] {
			display: flex !important;
			align-items: center;
			gap: 10px;
		}
		
		#reservationModal .input-group-ihg > div[style*="display: flex"] input {
			flex: 1;
		}
		
		#reservationModal .input-group-ihg label {
			display: none !important;
		}
		
		/* Excepción: mostrar labels de checkboxes y radio buttons - MÁXIMA ESPECIFICIDAD */
		#reservationModal .input-group-ihg .form-check-label,
		#reservationModal .input-group-ihg label.form-check-label,
		#reservationModal label[for="vivePalmira"] {
			display: block !important;
			visibility: visible !important;
			opacity: 1 !important;
		}
		
		#reservationModal .form-label {
			display: none !important;
		}
		
		#reservationModal .form-control {
			width: 100%;
			padding: 10px 0;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
			font-size: 16px;
			box-sizing: border-box;
			transition: border-bottom-color 0.3s ease;
			font-family: 'Oxygen', sans-serif;
			background-color: transparent !important;
			box-shadow: none !important;
		}
		
		#reservationModal .form-control:focus {
			outline: none !important;
			border-bottom: 1px solid #333 !important;
			box-shadow: none !important;
		}
		
		#reservationModal .form-control::placeholder {
			color: #999;
			font-size: 15px;
		}
		
		#reservationModal .form-control:read-only {
			background-color: transparent !important;
			cursor: not-allowed;
			opacity: 0.5;
		}
		
		/* Estilos para el campo de fecha */
		#reservationModal input[type="date"] {
			background: transparent !important;
			cursor: pointer;
		}
		
		/* Ocultar el icono de calendario nativo del lado derecho */
		#reservationModal input[type="date"]::-webkit-calendar-picker-indicator {
			opacity: 0 !important;
			width: 0 !important;
			height: 0 !important;
			pointer-events: none !important;
		}
		
		/* Para Firefox */
		#reservationModal input[type="date"]::-moz-calendar-picker-indicator {
			opacity: 0 !important;
			width: 0 !important;
			height: 0 !important;
			pointer-events: none !important;
		}
		
		/* Prevenir selección de texto en el campo date */
		#reservationModal input[type="date"] {
			user-select: none;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
		}
		
		/* Estilos para select y textarea - línea única */
		#reservationModal select.form-control,
		#reservationModal select,
		#reservationModal .form-select,
		#reservationModal textarea.form-control,
		#reservationModal textarea {
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
			background-color: transparent !important;
			padding: 10px 0 !important;
			box-shadow: none !important;
			cursor: default !important;
		}
		
		/* Estilos específicos para selects de adultos y niños - cursor normal */
		#reservationModal #adultos,
		#reservationModal #ninos {
			cursor: default !important;
		}
		
		#reservationModal #adultos:hover,
		#reservationModal #ninos:hover {
			cursor: default !important;
		}
		
		#reservationModal select.form-control:focus,
		#reservationModal select:focus,
		#reservationModal .form-select:focus,
		#reservationModal textarea.form-control:focus,
		#reservationModal textarea:focus {
			outline: none !important;
			border-bottom: 1px solid #333 !important;
			box-shadow: none !important;
		}
		
		/* Estilo específico para el input de código de país */
		#reservationModal #codigoPais {
			border-bottom: 1px solid #e1e5e9 !important;
			padding: 10px 3px !important;
			font-size: 15px;
			cursor: text;
			text-align: center;
			width: 60px !important;
			min-width: 60px !important;
			max-width: 60px !important;
		}
		
		#reservationModal #codigoPais:focus {
			border-bottom: 1px solid #333 !important;
			outline: none !important;
		}
		
		#reservationModal #codigoPais::placeholder {
			color: #999;
			opacity: 0.7;
		}
		
		/* Estilos para form-check */
		#reservationModal .form-check {
			display: flex;
			align-items: flex-start;
			gap: 15px;
			margin-bottom: 15px;
		}
		
		
		/* Estilos para métodos de pago centrados */
		#reservationModal .payment-methods-container {
			justify-content: center !important;
		}
		
		#reservationModal .payment-methods-container .form-check {
			flex: none !important;
		}
		
		/* Responsive para métodos de pago */
		@media (max-width: 768px) {
			#reservationModal .payment-methods-container {
				flex-direction: column !important;
				gap: 15px !important;
				align-items: center !important;
			}
			
			#reservationModal .payment-methods-container .form-check {
				width: auto !important;
			}
		}
		
		/* Estilos generales para labels de checkboxes/radios */
		#reservationModal .form-check-label {
			font-family: 'Oxygen', sans-serif;
			font-size: 15px;
			color: rgb(38, 38, 38);
			cursor: pointer;
			margin-left: 0;
			padding-left: 0 !important;
			line-height: 1.5;
		}
		
		/* Labels de métodos de pago (con iconos) */
		#reservationModal .payment-methods-container .form-check-label {
			display: flex !important;
			align-items: center;
			flex: 1;
		}
		
		
		/* Estilos para métodos de pago sin iconos */
		#reservationModal .payment-methods-container .form-check-label {
			display: block !important;
		}
		
		/* Estilos para radio buttons */
		#reservationModal .form-check input[type="radio"] {
			width: 20px !important;
			height: 20px !important;
			accent-color: #FFE082;
			cursor: pointer;
		}
		
		/* Botones del modal */
		#reservationModal .modal-footer {
			border-top: none !important;
			padding: 20px 40px 30px 40px;
			background-color: #ffffff !important;
			border-radius: 0;
		}
		
		#reservationModal #cancelReservation {
			background-color: #FFE082 !important;
			color: #333 !important;
			padding: 15px 40px !important;
			border: none !important;
			border-radius: 50px !important;
			font-size: 16px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			font-family: 'Oxygen', sans-serif;
			box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
			text-transform: uppercase;
			letter-spacing: 1px;
		}
		
		#reservationModal #cancelReservation:hover {
			background-color: #FFD54F !important;
			color: #333 !important;
			box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
			transform: translateY(-2px);
		}
		
		#reservationModal #cancelReservation:active {
			background-color: #FFC107 !important;
			transform: translateY(0);
		}
		
		#reservationModal #submitReservation {
			background: transparent !important;
			color: #333 !important;
			padding: 10px 0 !important;
			border: none !important;
			border-bottom: 1px solid #333 !important;
			border-radius: 0 !important;
			font-size: 16px;
			font-weight: 400;
			cursor: pointer;
			transition: border-bottom-width 0.3s ease;
			font-family: 'Oxygen', sans-serif;
			box-shadow: none !important;
		}
		
		#reservationModal #submitReservation:hover {
			background: transparent !important;
			border-bottom: 2px solid #333 !important;
			transform: none;
			box-shadow: none !important;
		}
		
		/* Alerts con colores del sitio */
		#reservationModal .alert-info {
			background-color: transparent !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			color: #666 !important;
			font-family: 'Oxygen', sans-serif;
			padding: 15px 0 !important;
			margin-bottom: 20px !important;
		}
		
		#reservationModal .alert-success {
			background-color: rgba(40, 167, 69, 0.1) !important;
			border: 1px solid #28a745 !important;
			color: #155724 !important;
			font-family: 'Oxygen', sans-serif;
		}
		
		/* Cost summary con estilo del sitio */
		#reservationModal .cost-summary-modal {
			background: transparent !important;
			border: none !important;
			border-top: 1px solid #e1e5e9 !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0;
			padding: 20px 0;
			margin: 20px 0;
		}
		
		#reservationModal .cost-summary-modal h6 {
			font-family: 'Crimson Text', serif;
			font-size: 1.3rem;
			color: rgb(38, 38, 38);
			font-weight: 600;
			margin-bottom: 15px;
		}
		
		#reservationModal .cost-summary-modal small,
		#reservationModal .cost-summary-modal strong {
			font-family: 'Oxygen', sans-serif;
			color: rgb(38, 38, 38);
		}
		
		/* Animaciones para el formulario */
		#reservationModal .row {
			animation: fadeIn 0.5s ease-in-out;
		}
		
		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(20px); }
			to { opacity: 1; transform: translateY(0); }
		}
		
		/* Estilos para formulario multi-step */
		#reservationModal .progress-indicator {
			display: flex;
			justify-content: center;
			margin-bottom: 30px;
			gap: 10px;
		}
		
		#reservationModal .progress-step {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: bold;
			font-family: 'Oxygen', sans-serif;
			transition: all 0.3s ease;
		}
		
		#reservationModal .progress-step.active {
			background: transparent !important;
			color: #333 !important;
			border: 1px solid #333 !important;
			box-shadow: none !important;
		}
		
		#reservationModal .progress-step.completed {
			background: transparent !important;
			color: #333 !important;
			border: 1px solid #333 !important;
		}
		
		#reservationModal .progress-step.inactive {
			background: transparent !important;
			color: #999 !important;
			border: 1px solid #e1e5e9 !important;
		}
		
		#reservationModal .form-section {
			display: none;
			animation: fadeIn 0.5s ease-in-out;
		}
		
		#reservationModal .form-section.active {
			display: block;
		}
		
		#reservationModal .section-header {
			text-align: center;
			margin-bottom: 30px;
		}
		
		#reservationModal .section-header h3 {
			font-family: 'Crimson Text', serif;
			font-size: 1.5rem;
			color: rgb(38, 38, 38);
			margin-bottom: 5px;
			font-weight: 600;
		}
		
		#reservationModal .section-header p {
			font-family: 'Oxygen', sans-serif;
			color: #666;
			font-size: 14px;
			margin: 0;
		}
		
		#reservationModal .section-buttons {
			display: flex;
			gap: 15px;
			margin-top: 30px;
		}
		
		#reservationModal .next-btn, #reservationModal .back-btn {
			flex: 1;
			padding: 15px 25px;
			border: none;
			border-radius: 8px;
			font-size: 16px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			font-family: 'Oxygen', sans-serif;
		}
		
		#reservationModal .next-btn {
			background-color: #FFE082 !important;
			color: #333 !important;
			border: none !important;
			border-radius: 50px !important;
			box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
			padding: 15px 40px !important;
			text-transform: uppercase;
			letter-spacing: 1px;
		}
		
		#reservationModal .next-btn:hover {
			background-color: #FFD54F !important;
			box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
			transform: translateY(-2px);
		}
		
		#reservationModal .next-btn:active {
			background-color: #FFC107 !important;
			transform: translateY(0);
		}
		
		#reservationModal .back-btn {
			background-color: #FFE082 !important;
			color: #333 !important;
			border: none !important;
			border-radius: 50px !important;
			box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3) !important;
			padding: 15px 40px !important;
			text-transform: uppercase;
			letter-spacing: 1px;
		}
		
		#reservationModal .back-btn:hover {
			background-color: #FFD54F !important;
			box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
			transform: translateY(-2px);
		}
		
		#reservationModal .back-btn:active {
			background-color: #FFC107 !important;
			transform: translateY(0);
		}
		
		#reservationModal .error-message {
			color: #dc3545;
			font-size: 12px;
			margin-top: 5px;
			display: block;
			font-family: 'Oxygen', sans-serif;
		}
		
		#reservationModal .form-control.error {
			border-color: #dc3545 !important;
		}
		
		#reservationModal .reservation-summary-section {
			background: transparent !important;
			border: none !important;
			border-top: 1px solid #e1e5e9 !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0;
			padding: 20px 0;
			margin: 20px 0;
		}
		
		#reservationModal .reservation-summary-section h6 {
			font-family: 'Crimson Text', serif;
			font-size: 1.3rem;
			color: rgb(38, 38, 38);
			font-weight: 600;
			margin-bottom: 15px;
		}
		
		#reservationModal .reservation-summary-section .summary-item {
			display: flex;
			justify-content: space-between;
			padding: 10px 0;
			border-bottom: 1px solid rgba(38, 38, 38, 0.1);
			font-family: 'Oxygen', sans-serif;
		}
		
		#reservationModal .reservation-summary-section .summary-item:last-child {
			border-bottom: none;
		}
		
		#reservationModal .reservation-summary-section .summary-label {
			font-weight: 600;
			color: rgb(38, 38, 38);
		}
		
		#reservationModal .reservation-summary-section .summary-value {
			color: #666;
		}
		
		.modal-content {
			position: relative;
			display: flex;
			flex-direction: column;
			width: 100%;
			pointer-events: auto;
			background-color: #fff;
			background-clip: padding-box;
			border: 1px solid rgba(0,0,0,.2);
			border-radius: 0.3rem;
			outline: 0;
		}
		
		.modal-backdrop {
			position: fixed !important;
			top: 0 !important;
			left: 0 !important;
			right: 0 !important;
			bottom: 0 !important;
			z-index: 1040 !important;
			width: 100% !important;
			height: 100% !important;
			min-width: 100vw !important;
			min-height: 100vh !important;
			background-color: rgba(0, 0, 0, 0.5) !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		.modal-backdrop.fade {
			opacity: 0;
		}
		
		.modal-backdrop.show {
			opacity: 0.5;
		}
		
		/* Asegurar que los modales estén por encima del backdrop */
		.modal {
			z-index: 1050 !important;
		}
		
		.modal.show {
			z-index: 1050 !important;
		}
		
		#reservationModal {
			z-index: 1050 !important;
		}
		
		#rangeErrorModal {
			z-index: 1050 !important;
		}
		
		#noPriceModal {
			z-index: 1050 !important;
		}
		
		#reservationModal .modal-dialog {
			z-index: 1051 !important;
		}
		
		#rangeErrorModal .modal-dialog {
			z-index: 1051 !important;
		}
		
		#noPriceModal .modal-dialog {
			z-index: 1051 !important;
		}
		
		/* Forzar z-index correcto para todos los backdrops */
		body .modal-backdrop {
			z-index: 1040 !important;
		}
		
		body .modal {
			z-index: 1050 !important;
		}
		
		body .modal.show {
			z-index: 1050 !important;
		}
		
		body .modal-dialog {
			z-index: 1051 !important;
		}
		
		/* Estilos específicos para modal de advertencia */
		/* Estilos del modal de error de rango - Aplicando estilos del calendario */
		#rangeErrorModal .modal-content {
			background: #ffffff !important;
			border: none !important;
			border-radius: 0 !important;
			box-shadow: none !important;
		}
		
		#rangeErrorModal .modal-header {
			background: #ffffff !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
		}
		
		#rangeErrorModal .modal-body {
			background: #ffffff !important;
		}
		
		#rangeErrorModal .modal-footer {
			background: #ffffff !important;
			border: none !important;
			border-top: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
		}
		
		#rangeErrorModal .btn-primary {
			background: transparent !important;
			color: #333 !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
			font-family: 'Oxygen', sans-serif !important;
			font-weight: 400 !important;
		}
		
		#rangeErrorModal .btn-primary:hover {
			background: transparent !important;
			color: #333 !important;
			border-bottom: 1px solid #333 !important;
			transform: none !important;
		}
		
		#rangeErrorModal .btn-close {
			background: transparent !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			color: #333 !important;
			opacity: 1 !important;
		}
		
		#rangeErrorModal .btn-close:hover {
			border-bottom: 1px solid #333 !important;
		}
		
		#rangeErrorModal .modal-title {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 1.5rem !important;
			font-weight: 400 !important;
			color: #333 !important;
		}
		
		#rangeErrorModal h6 {
			font-family: 'Oxygen', sans-serif !important;
			font-weight: 400 !important;
			color: #333 !important;
		}
		
		#rangeErrorModal p {
			font-family: 'Oxygen', sans-serif !important;
			font-weight: 400 !important;
			color: #333 !important;
		}
		
		/* Estilos del modal de día sin precio - Aplicando estilos del calendario */
		#noPriceModal .modal-content {
			background: #ffffff !important;
			border: none !important;
			border-radius: 0 !important;
			box-shadow: none !important;
		}
		
		#noPriceModal .modal-header {
			background: #ffffff !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
		}
		
		#noPriceModal .modal-body {
			background: #ffffff !important;
		}
		
		#noPriceModal .modal-footer {
			background: #ffffff !important;
			border: none !important;
			border-top: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
		}
		
		#noPriceModal .btn-primary {
			background: transparent !important;
			color: #333 !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			border-radius: 0 !important;
			font-family: 'Oxygen', sans-serif !important;
			font-weight: 400 !important;
		}
		
		#noPriceModal .btn-primary:hover {
			background: transparent !important;
			color: #333 !important;
			border-bottom: 1px solid #333 !important;
			transform: none !important;
		}
		
		#noPriceModal .btn-close {
			background: transparent !important;
			border: none !important;
			border-bottom: 1px solid #e1e5e9 !important;
			color: #333 !important;
			opacity: 1 !important;
		}
		
		#noPriceModal .btn-close:hover {
			border-bottom: 1px solid #333 !important;
		}
		
		#noPriceModal .modal-title {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 1.5rem !important;
			font-weight: 400 !important;
			color: #333 !important;
		}
		
		#noPriceModal h6 {
			font-family: 'Oxygen', sans-serif !important;
			font-weight: 400 !important;
			color: #333 !important;
		}
		
		#noPriceModal p {
			font-family: 'Oxygen', sans-serif !important;
			font-weight: 400 !important;
			color: #333 !important;
		}
		
		/* Estilo para el botón de cerrar del modal */
		.btn-close {
			background: none;
			border: none;
			font-size: 1.5rem;
			font-weight: bold;
			color: #6c757d;
			cursor: pointer;
			padding: 0.5rem;
			line-height: 1;
			opacity: 0.7;
			transition: opacity 0.15s ease-in-out;
		}
		
		.btn-close:hover {
			opacity: 1;
			color: #000;
		}
		
		.btn-close:focus {
			outline: none;
			box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
		}
		
		.btn-close::before {
			content: "×";
			font-size: 1.5rem;
			font-weight: bold;
		}
		
		@media (min-width: 576px) {
			.modal-dialog {
				max-width: 500px;
				margin: 1.75rem auto;
			}
		}
		
		/* Estilos del Modal de Login */
		.modal .close:hover {
			color: #ff6b6b !important;
		}
		
		.modal .input-group input:focus {
			border-color: #007BFF !important;
			outline: none;
			box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
		}
		
		.modal .login-button:hover {
			background-color: #FF8C00 !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(255, 165, 0, 0.3);
		}
		
		.modal .login-button {
			transition: all 0.3s ease;
		}
		
		.modal .links a:hover {
			color: #0056b3 !important;
			text-decoration: underline !important;
		}
		
		/* Estilos para el dropdown del perfil */
		.dropdown-menu {
			background: white;
			border: 1px solid #ddd;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
			padding: 8px 0;
			min-width: 200px;
		}
		
		.dropdown-item {
			padding: 8px 16px;
			color: #333;
			text-decoration: none;
			display: flex;
			align-items: center;
			transition: background-color 0.2s ease;
		}
		
		.dropdown-item:hover {
			background-color: #f8f9fa;
			color: #007bff;
		}
		
		.dropdown-item i {
			margin-right: 8px;
			width: 16px;
		}
		
		.dropdown-divider {
			margin: 4px 0;
			border-top: 1px solid #dee2e6;
		}
		
		.dropdown-toggle::after {
			margin-left: 8px;
		}
		
		/* Responsive Design Mejorado */
		@media (max-width: 768px) {
			.calendar-container {
				margin-bottom: 20px;
				padding: 15px;
			}
			
			.calendar-grid {
				gap: 1px;
				max-width: 100%;
			}
			
			.calendar-day {
				min-height: 40px;
				font-size: 12px;
			}
			
			.day-number {
				font-size: 12px;
			}
			
			.day-price {
				font-size: 0.6rem;
				padding: 1px 4px;
			}
			
			.calendar-legend {
				justify-content: center;
				gap: 15px;
			}
			
			.legend-item {
				font-size: 12px;
			}
			
			.legend-color {
				width: 20px;
				height: 20px;
			}
			
			.reservation-summary {
				padding: 25px !important;
				margin-left: 0 !important;
				margin-top: 30px !important;
				height: auto !important;
				min-height: auto !important;
				width: 100% !important;
			}
			
			.reservation-summary h4 {
				font-size: 1.8rem !important;
				margin-bottom: 20px !important;
			}
			
			.reservation-details {
				padding: 20px !important;
			}
			
			.detail-item {
				flex-direction: column;
				align-items: flex-start;
				gap: 5px;
				font-size: 1rem !important;
				margin-bottom: 15px !important;
			}
			
			.detail-item.total {
				flex-direction: row;
				justify-content: space-between;
				font-size: 1.3rem !important;
			}
			
			#reserveBtn {
				font-size: 1.1rem !important;
				padding: 12px !important;
				margin-top: 20px !important;
			}
			
			/* Modal responsive */
			.modal .modal-content {
				width: 95% !important;
				margin: 10% auto !important;
			}
			
			.modal .modal-body {
				padding: 20px !important;
			}
			
			.modal .links {
				flex-direction: column !important;
				gap: 10px !important;
			}
		}
		
		@media (max-width: 480px) {
			.calendar-container {
				padding: 10px;
			}
			
			.calendar-day {
				min-height: 35px;
			}
			
			.day-number {
				font-size: 11px;
			}
			
			/* Ajustar etiquetas de precio en móviles muy pequeños */
			.selected-day-price {
				top: -14px;
				padding: 3px 2px;
				font-size: 0.55rem;
				letter-spacing: 0.2px;
			}
			.selected-day-price::after {
				border-left-width: 3px;
				border-right-width: 3px;
				border-top-width: 4px;
				bottom: -4px;
			}
			
			.reservation-summary {
				padding: 20px !important;
				margin-left: 0 !important;
				margin-top: 20px !important;
			}
			
			.reservation-summary h4 {
				font-size: 1.5rem !important;
				margin-bottom: 15px !important;
			}
			
			.reservation-details {
				padding: 15px !important;
			}
			
			.detail-item {
				font-size: 0.95rem !important;
				margin-bottom: 12px !important;
			}
			
			.detail-item.total {
				font-size: 1.2rem !important;
			}
			
			#reserveBtn {
				font-size: 1rem !important;
				padding: 10px !important;
			}
			
			.day-price {
				font-size: 0.55rem;
				padding: 1px 3px;
			}
			
			.month-display {
				font-size: 1.4rem;
			}
		}
		
		/* Estilos para el nuevo Footer */
		.new-footer {
			width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		.footer-top-section {
			width: 100% !important;
			margin: 0 !important;
			padding: 0 !important;
		}
		
		.footer-top-section .row {
			margin: 0 !important;
		}
		
		.footer-column {
			padding: 60px 40px !important;
			min-height: 300px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
		}
		
		.footer-social {
			background-color: rgb(238, 211, 197) !important;
		}
		
		.footer-transport {
			background-color: rgb(77, 86, 67) !important;
		}
		
		.footer-waiting {
			background-color: rgb(133, 128, 126) !important;
		}
		
		.footer-content {
			text-align: center !important;
			width: 100% !important;
		}
		
		.footer-subtitle {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 0.9rem !important;
			font-weight: 400 !important;
			color: #000 !important;
			margin-bottom: 15px !important;
			text-transform: uppercase !important;
			letter-spacing: 1px !important;
		}
		
		.footer-transport .footer-subtitle {
			color: #fff !important;
		}
		
		.footer-title {
			font-family: 'Oxygen', sans-serif !important;
			font-size: 1.8rem !important;
			font-weight: 700 !important;
			color: #000 !important;
			margin-bottom: 30px !important;
			line-height: 1.3 !important;
		}
		
		.footer-transport .footer-title {
			font-size: 1.5rem !important;
			color: #fff !important;
		}
		
		.footer-waiting .footer-title {
			color: #fff !important;
			font-family: 'Abril Fatface', serif !important;
			font-size: 3rem !important;
			font-weight: 400 !important;
			letter-spacing: 2px !important;
			text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3) !important;
		}
		
		/* Contenedor de la imagen del transporte */
		.transport-image-container {
			margin-bottom: 25px !important;
			display: flex !important;
			justify-content: center !important;
			align-items: center !important;
		}
		
		.transport-image {
			max-width: 280px !important;
			width: 100% !important;
			height: auto !important;
			object-fit: contain !important;
			animation: floatCar 3s ease-in-out infinite !important;
			filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3)) !important;
			transition: transform 0.3s ease !important;
		}
		
		.transport-image:hover {
			transform: scale(1.05) !important;
			filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.4)) !important;
		}
		
		/* Animación flotante para el auto */
		@keyframes floatCar {
			0%, 100% {
				transform: translateY(0px) rotate(0deg);
			}
			25% {
				transform: translateY(-10px) rotate(1deg);
			}
			50% {
				transform: translateY(-5px) rotate(0deg);
			}
			75% {
				transform: translateY(-10px) rotate(-1deg);
			}
		}
		
		.social-icons-container {
			display: flex !important;
			justify-content: center !important;
			align-items: center !important;
			gap: 25px !important;
			margin-top: 20px !important;
		}
		
		.social-icon {
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			width: 50px !important;
			height: 50px !important;
			color: #000 !important;
			text-decoration: none !important;
			transition: transform 0.3s ease !important;
		}
		
		.social-icon:hover {
			transform: scale(1.1) !important;
		}
		
		.social-icon .fa {
			font-size: 28px !important;
			color: #000 !important;
		}
		
		.whatsapp-icon {
			width: 35px !important;
			height: 35px !important;
			object-fit: contain !important;
		}
		
		.footer-bottom-section {
			background-color: #000 !important;
			padding: 20px 0 !important;
			color: #fff !important;
		}
		
		.footer-links {
			margin: 0 !important;
			padding: 0 !important;
		}
		
		.footer-links a {
			color: #fff !important;
			text-decoration: none !important;
			margin-right: 20px !important;
			font-family: 'Oxygen', sans-serif !important;
			font-size: 0.9rem !important;
			transition: opacity 0.3s ease !important;
		}
		
		.footer-links a:hover {
			opacity: 0.7 !important;
		}
		
		.footer-copyright {
			margin: 0 !important;
			color: #fff !important;
			font-family: 'Oxygen', sans-serif !important;
			font-size: 0.9rem !important;
		}
		
		/* Responsive para el footer */
		@media (max-width: 768px) {
			.footer-column {
				padding: 40px 20px !important;
				min-height: 250px !important;
			}
			
			.footer-title {
				font-size: 1.4rem !important;
			}
			
			.footer-transport .footer-title {
				font-size: 1.2rem !important;
			}
			
			.footer-subtitle {
				font-size: 0.8rem !important;
			}
			
			.social-icons-container {
				gap: 20px !important;
			}
			
			.footer-bottom-section .row {
				flex-direction: column !important;
				text-align: center !important;
			}
			
			.footer-links {
				margin-bottom: 10px !important;
			}
			
			.footer-copyright {
				text-align: center !important;
			}
		}
		
		@media (max-width: 480px) {
			.footer-column {
				padding: 30px 15px !important;
				min-height: 200px !important;
			}
			
			.footer-title {
				font-size: 1.2rem !important;
			}
			
			.footer-transport .footer-title {
				font-size: 1rem !important;
			}
			
			.social-icons-container {
				gap: 15px !important;
			}
		}
	</style>

	<!-- JavaScript para el Sistema de Reservas -->
	<script>
		// Traducciones JavaScript dinámicas
		const translations = {
			months: <?php echo I18n::getJS('months'); ?>,
			days: <?php echo I18n::getJS('days'); ?>,
			locale: <?php echo json_encode(I18n::getJS('locale')); ?>,
		calendar: {
			past_day: <?php echo json_encode(__('calendar.past_day')); ?>,
			not_available: <?php echo json_encode(__('calendar.not_available')); ?>,
			price_per_night: <?php echo json_encode(__('calendar.price_per_night')); ?>,
			full_day_occupied: <?php echo json_encode(__('calendar.full_day_occupied')); ?>,
			checkout_available: <?php echo json_encode(__('calendar.checkout_available')); ?>,
			checkin_available: <?php echo json_encode(__('calendar.checkin_available')); ?>,
			checkin_blocked_title: <?php echo json_encode(__('calendar.checkin_blocked_title')); ?>,
			checkin_blocked_msg: <?php echo json_encode(__('calendar.checkin_blocked_msg')); ?>,
			checkin_blocked_exp: <?php echo json_encode(__('calendar.checkin_blocked_exp')); ?>,
			checkout_blocked_title: <?php echo json_encode(__('calendar.checkout_blocked_title')); ?>,
			checkout_blocked_msg: <?php echo json_encode(__('calendar.checkout_blocked_msg')); ?>,
			checkout_blocked_exp: <?php echo json_encode(__('calendar.checkout_blocked_exp')); ?>,
			range_overlap_title: <?php echo json_encode(__('calendar.range_overlap_title')); ?>,
			range_overlap_msg: <?php echo json_encode(__('calendar.range_overlap_msg')); ?>,
			range_overlap_exp: <?php echo json_encode(__('calendar.range_overlap_exp')); ?>
		},
		messages: {
				success_sent: <?php echo json_encode(__('message.success_sent')); ?>,
				error_sending: <?php echo json_encode(__('message.error_sending')); ?>,
				error_retry: <?php echo json_encode(__('message.error_retry')); ?>
			},
			profile: {
				title: <?php echo json_encode(__('profile.title')); ?>,
				name: <?php echo json_encode(__('profile.name')); ?>,
				email: <?php echo json_encode(__('profile.email')); ?>,
				phone: <?php echo json_encode(__('profile.phone')); ?>,
				birthday: <?php echo json_encode(__('profile.birthday')); ?>,
				not_registered: <?php echo json_encode(__('profile.not_registered')); ?>,
				available_discounts: <?php echo json_encode(__('profile.available_discounts')); ?>,
				fidelity: <?php echo json_encode(__('profile.fidelity')); ?>,
				always: <?php echo json_encode(__('profile.always')); ?>,
				birthday_discount: <?php echo json_encode(__('profile.birthday_discount')); ?>,
				birthday_range: <?php echo json_encode(__('profile.birthday_range')); ?>
			},
			reservations: {
				title: <?php echo json_encode(__('reservations.title')); ?>,
				soon: <?php echo json_encode(__('reservations.soon')); ?>,
				view_history: <?php echo json_encode(__('reservations.view_history')); ?>,
				no_reservations: <?php echo json_encode(__('reservations.no_reservations')); ?>,
				checkin: <?php echo json_encode(__('reservations.checkin')); ?>,
				checkout: <?php echo json_encode(__('reservations.checkout')); ?>,
				nights: <?php echo json_encode(__('reservations.nights')); ?>,
				total: <?php echo json_encode(__('reservations.total')); ?>,
				status: <?php echo json_encode(__('reservations.status')); ?>,
				payment_status: <?php echo json_encode(__('reservations.payment_status')); ?>,
				payment_method: <?php echo json_encode(__('reservations.payment_method')); ?>,
				created: <?php echo json_encode(__('reservations.created')); ?>,
				adults: <?php echo json_encode(__('reservations.adults')); ?>,
				children: <?php echo json_encode(__('reservations.children')); ?>,
				pending: <?php echo json_encode(__('reservations.pending')); ?>,
				approved: <?php echo json_encode(__('reservations.approved')); ?>,
				confirmed: <?php echo json_encode(__('reservations.confirmed')); ?>,
				cancelled: <?php echo json_encode(__('reservations.cancelled')); ?>,
				paid: <?php echo json_encode(__('reservations.paid')); ?>,
				unpaid: <?php echo json_encode(__('reservations.unpaid')); ?>,
				cash: <?php echo json_encode(__('reservations.cash')); ?>,
				card: <?php echo json_encode(__('reservations.card')); ?>
			}
		};
		
		// Variables globales
		let currentDate = new Date();
		let selectedStartDate = null;
		let selectedEndDate = null;
        let occupiedDates  = <?php echo json_encode($occupied_dates); ?>;
        let checkinDates   = <?php echo json_encode($checkin_dates ?? []); ?>;   // días de check-in de reservas existentes
        let checkoutDates  = <?php echo json_encode($checkout_dates ?? []); ?>;  // días de check-out de reservas existentes
        let basePrice      = <?php echo $base_price; ?>;let tarifasCache = {}; // Cache de tarifas por fecha
		let eventListenersSetup = false; // Flag para evitar configurar listeners múltiples veces
		let clickOutsideHandler = null; // Handler para clicks fuera del calendario
		
		// Función para obtener fechas ocupadas
		function getFechasOcupadas() {
			return occupiedDates;
		}
		
		// Función para obtener tarifa por fecha desde la base de datos
		function getTarifaPorFecha(fecha) {
			// Si está en cache, retornar
			if (tarifasCache[fecha] !== undefined) {
				return tarifasCache[fecha];
			}
			
			// Si no está en cache, retornar precio base (se cargará después)
			return basePrice;
		}
		
		// Función para verificar si una fecha tiene precio disponible
		function tienePrecioDisponible(fecha) {
			// Si está en cache
			if (tarifasCache[fecha] !== undefined) {
				// Si el valor es null o undefined, no tiene precio
				if (tarifasCache[fecha] === null || tarifasCache[fecha] === undefined) {
					return false;
				}
				// Si tiene un valor numérico válido (incluyendo 0), tiene precio
				// Verificar que sea un número (0 es válido)
				if (typeof tarifasCache[fecha] === 'number' && !isNaN(tarifasCache[fecha])) {
					return true;
				}
				return false;
			}
			
			// Si no está en cache, verificar si el cache ya se ha poblado
			// Si el cache tiene elementos, significa que ya se cargaron las tarifas
			// y si una fecha no está en el cache, no tiene precio
			if (Object.keys(tarifasCache).length > 0) {
				// Cache ya cargado, si no está aquí, no tiene precio
				return false;
			}
			
			// Cache aún no cargado, permitir selección temporalmente
			// (se validará cuando se carguen las tarifas)
			return true;
		}
		
		// Función para formatear precio en formato colombiano
		function formatearPrecioCOP(precio) {
			// Formato: $200.000 COP
			return '$' + precio.toLocaleString('es-CO') + ' COP';
		}
		
		// Cargar tarifas basándose en el mes visible actual del calendario
		function loadTarifas() {
			// Calcular rango basado en el mes visible (currentDate) + meses adyacentes
			const year = currentDate.getFullYear();
			const month = currentDate.getMonth();
			
			// Inicio: primer día del mes anterior (para mostrar días completos)
			const fechaInicio = new Date(year, month - 1, 1);
			// Fin: último día del mes siguiente (para mostrar días completos)
			const fechaFin = new Date(year, month + 2, 0);
			
			const fechaInicioStr = fechaInicio.toISOString().split('T')[0];
			const fechaFinStr = fechaFin.toISOString().split('T')[0];
			
			// Renderizar calendario inmediatamente sin esperar las tarifas
			renderCalendar();
			
			// Cargar tarifas en segundo plano
			fetch(`../app/api/admin/get_tarifas_range.php?fecha_inicio=${fechaInicioStr}&fecha_fin=${fechaFinStr}&apartamento_id=1`)
				.then(response => {
					return response.json();
				})
				.then(data => {
					if (data.success && data.tarifas) {
						// Limpiar cache para el rango de fechas que se está cargando
						// y luego actualizar con las nuevas tarifas (esto sobrescribe valores antiguos)
						Object.keys(data.tarifas).forEach(fecha => {
							delete tarifasCache[fecha];
						});
						// Actualizar cache con las nuevas tarifas (sobrescribe valores existentes)
						// Asegurar que los precios 0 se guarden correctamente
						Object.keys(data.tarifas).forEach(fecha => {
							const precio = data.tarifas[fecha];
							// Guardar el precio incluso si es 0 (0 es un valor válido)
							tarifasCache[fecha] = typeof precio === 'number' ? precio : parseFloat(precio) || 0;
						});
						// Re-renderizar calendario con precios actualizados
						renderCalendar();
					} else {
						// Renderizar calendario de todas formas aunque falle la carga de tarifas
						renderCalendar();
					}
				})
				.catch(error => {
					// Renderizar calendario de todas formas aunque falle la carga de tarifas
					renderCalendar();
				});
		}
		
		
		// Inicializar calendario
		function initCalendar() {
			// Verificar que el elemento del calendario existe
			const calendar = document.getElementById('calendar');
			if (!calendar) {
				setTimeout(initCalendar, 500);
				return;
			}
			
			// Verificar que translations existe
			if (!translations) {
				return;
			}
			
			// Reiniciar variables globales
			currentDate = new Date();
			selectedStartDate = null;
			selectedEndDate = null;
			occupiedDates = <?php echo json_encode($occupied_dates); ?>;
			basePrice = <?php echo $base_price; ?>;
			tarifasCache = {};
			
			// Resetear flag de event listeners para permitir reconfiguración
			eventListenersSetup = false;
			
			// Cargar tarifas primero (loadTarifas() ya llama a renderCalendar())
			loadTarifas();
			
			setupEventListeners();
		}
		
		
		// Renderizar calendario
		function renderCalendar() {
			try {
				const calendar = document.getElementById('calendar');
				const monthDisplay = document.getElementById('currentMonth');
				
				if (!calendar) {
					return;
				}
				
				if (!monthDisplay) {
					return;
				}
				
				// Limpiar calendario
				calendar.innerHTML = '';
				
				// Verificar que translations existe y tiene months
				if (!translations || !translations.months) {
					return;
				}
				
				// Mostrar mes actual
				const monthNames = translations.months;
				const monthText = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
				monthDisplay.textContent = monthText;
				
				// Grid fijo de 6 filas para mantener estructura consistente
				calendar.style.gridTemplateRows = 'repeat(6, 1fr)';
				
				// Obtener primer día del mes y cuántos días tiene
				const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
				const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
				const daysInMonth = lastDay.getDate();
				const startingDayOfWeek = firstDay.getDay();
				
				// Verificar que translations.days existe
				if (!translations.days) {
					return;
				}
				
				// Días de la semana
				const dayNames = translations.days;
				dayNames.forEach(day => {
					const dayHeader = document.createElement('div');
					dayHeader.className = 'calendar-day text-center fw-bold';
					dayHeader.textContent = day;
					dayHeader.style.background = '#e9ecef';
					dayHeader.style.cursor = 'default';
					calendar.appendChild(dayHeader);
				});
				
				// Días del mes anterior (invisibles pero mantienen la estructura)
				const prevMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 0);
				for (let i = startingDayOfWeek - 1; i >= 0; i--) {
					const day = document.createElement('div');
					day.className = 'calendar-day other-month';
					day.style.visibility = 'hidden'; // Hacer invisibles pero mantener estructura
					day.textContent = prevMonth.getDate() - i;
					calendar.appendChild(day);
				}
				
				// Días del mes actual
				for (let day = 1; day <= daysInMonth; day++) {
					const dayElement = document.createElement('div');
					const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
					const dateString = date.toISOString().split('T')[0];
					
					dayElement.className = 'calendar-day';
					dayElement.dataset.date = dateString;
					
					// Crear estructura del día
					const dayNumber = document.createElement('div');
					dayNumber.className = 'day-number';
					dayNumber.textContent = day;
					dayElement.appendChild(dayNumber);
					
					// Verificar si es un día pasado
					const today = new Date();
					today.setHours(0, 0, 0, 0); // Resetear horas para comparar solo fechas
					const dayDate = new Date(date);
					dayDate.setHours(0, 0, 0, 0);
					
					// Verificar si la fecha está en el rango no disponible (hoy hasta 31 enero 2026 inclusive)
					const endDate = new Date(2026, 0, 31); // 31 de enero de 2026 (mes 0 = enero)
					endDate.setHours(23, 59, 59, 999); // Incluir todo el día 31
					const isNotAvailable = dayDate >= today && dayDate <= endDate;

                    const isCheckin  = checkinDates.includes(dateString);
                    const isCheckout = checkoutDates.includes(dateString);

                    if (dayDate < today) {
                        // Día pasado
                        dayElement.classList.add('other-month');
                        dayElement.style.cursor = 'not-allowed';
                        dayElement.title = translations.calendar.past_day;

                    } else if (occupiedDates.includes(dateString)) {
                        // Día intermedio de una reserva: completamente bloqueado
                        dayElement.classList.add('occupied');
                        dayElement.title = translations.calendar.not_available;

                    } else if (isCheckin && isCheckout) {
                        // Mismo día es check-OUT de una reserva y check-IN de otra
                        // Completamente ocupado: sale uno a las 11:00 y entra otro a las 15:00
                        dayElement.classList.add('occupied');
                        dayElement.classList.add('day-checkin-checkout');
                        dayElement.title = translations.calendar.full_day_occupied;

                    } else if (isCheckout) {
                        // Check-OUT de reserva existente: tarde libre para nuevo check-in (15:00)
                        // Triángulo rojo esquina superior izquierda
                        dayElement.classList.add('available');
                        dayElement.classList.add('day-checkout-available');
                        dayElement.title = translations.calendar.checkout_available;
                        dayElement.addEventListener('click', () => selectDate(date));

                        const priceElement = document.createElement('div');
                        priceElement.className = 'day-price';
                        priceElement.textContent = formatearPrecioCOP(getTarifaPorFecha(dateString));
                        priceElement.title = `${translations.calendar.price_per_night}: ${formatearPrecioCOP(getTarifaPorFecha(dateString))}`;
                        dayElement.appendChild(priceElement);

                    } else if (isCheckin) {
                        // Check-IN de reserva existente: mañana libre para check-out (hasta 11:00)
                        // Triángulo rojo esquina inferior derecha
                        dayElement.classList.add('available');
                        dayElement.classList.add('day-checkin-only');
                        dayElement.title = translations.calendar.checkin_available;
                        dayElement.addEventListener('click', () => selectDate(date));

                        const priceElement = document.createElement('div');
                        priceElement.className = 'day-price';
                        priceElement.textContent = formatearPrecioCOP(getTarifaPorFecha(dateString));
                        priceElement.title = `${translations.calendar.price_per_night}: ${formatearPrecioCOP(getTarifaPorFecha(dateString))}`;
                        dayElement.appendChild(priceElement);

                    } else {
                        // Día completamente libre
                        dayElement.classList.add('available');
                        dayElement.addEventListener('click', () => selectDate(date));

                        const priceElement = document.createElement('div');
                        priceElement.className = 'day-price';
                        priceElement.textContent = formatearPrecioCOP(getTarifaPorFecha(dateString));
                        priceElement.title = `${translations.calendar.price_per_night}: ${formatearPrecioCOP(getTarifaPorFecha(dateString))}`;
                        dayElement.appendChild(priceElement);
                    }

					calendar.appendChild(dayElement);
				}
				
				// Días del mes siguiente (invisibles pero mantienen la estructura)
				const nextMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
				const remainingDays = 42 - (startingDayOfWeek + daysInMonth);
				for (let day = 1; day <= remainingDays; day++) {
					const dayElement = document.createElement('div');
					dayElement.className = 'calendar-day other-month';
					dayElement.style.visibility = 'hidden'; // Hacer invisibles pero mantener estructura
					dayElement.textContent = day;
					calendar.appendChild(dayElement);
				}
				
				// Aplicar estados de selección
				updateSelectionStates();
			} catch (error) {
				// Error silencioso
			}
		}

        // Seleccionar fecha
        function selectDate(date) {
            const dateString = date.toISOString().split('T')[0];

            const isExistingCheckin  = checkinDates.includes(dateString);
            const isExistingCheckout = checkoutDates.includes(dateString);

            if (selectedStartDate === null) {
                // ── Seleccionando CHECK-IN ──

                // No se puede hacer check-in en un día que ya tiene check-in de otra reserva
                // (ese día a las 15:00 ya estaría ocupado)
                if (isExistingCheckin && !isExistingCheckout) {
                    showCheckinBlockedError(date);
                    return;
                }

                if (!tienePrecioDisponible(dateString)) {
                    showNoPriceError();
                    return;
                }

                selectedStartDate = date;
                updateReservationSummary();
                updateSelectionStates();

            } else if (selectedEndDate === null) {
                // ── Seleccionando CHECK-OUT ──

                if (date <= selectedStartDate) {
                    // Fecha anterior o igual: reiniciar selección con este día como check-in
                    clearSelection();
                    if (isExistingCheckin && !isExistingCheckout) {
                        showCheckinBlockedError(date);
                        return;
                    }
                    if (!tienePrecioDisponible(dateString)) {
                        showNoPriceError();
                        return;
                    }
                    selectedStartDate = date;
                    updateReservationSummary();
                    updateSelectionStates();
                    return;
                }

                // No se puede hacer check-out en un día que ya tiene check-out de otra reserva
                // (ese día a las 11:00 ya estaría ocupado para salir)
                if (isExistingCheckout && !isExistingCheckin) {
                    showCheckoutBlockedError(date);
                    return;
                }

                // Validar todo el rango
                const rangeResult = validateDateRange(selectedStartDate, date);

                if (rangeResult !== true) {
                    // Mostrar error específico según el tipo de conflicto
                    if (rangeResult.error === 'checkin_blocked') {
                        showCheckinBlockedError(rangeResult.date);
                    } else if (rangeResult.error === 'checkout_blocked') {
                        showCheckoutBlockedError(rangeResult.date);
                    } else if (rangeResult.error === 'overlap') {
                        showOverlapError(rangeResult.date);
                    } else {
                        showRangeError();
                    }
                    return;
                }

                // Validar precios en el rango
                if (!validateDateRangePrice(selectedStartDate, date)) {
                    showNoPriceError();
                    return;
                }

                selectedEndDate = date;
                updateReservationSummary();
                updateSelectionStates();

            } else {
                // Ya había check-in y check-out: reiniciar con este día como nuevo check-in
                clearSelection();
                if (isExistingCheckin && !isExistingCheckout) {
                    showCheckinBlockedError(date);
                    return;
                }
                if (!tienePrecioDisponible(dateString)) {
                    showNoPriceError();
                    return;
                }
                selectedStartDate = date;
                selectedEndDate   = null;
                updateReservationSummary();
                updateSelectionStates();
            }
        }
		
		// Limpiar selección
		function clearSelection() {
			selectedStartDate = null;
			selectedEndDate = null;
			updateSelectionStates();
		}


        // Validar rango de fechas
        // Retorna: true si válido, o un objeto { error, date } si hay conflicto
        function validateDateRange(startDate, endDate) {
            const start = new Date(startDate);
            const end   = new Date(endDate);

            const startStr = start.toISOString().split('T')[0];
            const endStr   = end.toISOString().split('T')[0];

            // 1. El check-in elegido no puede ser un día que ya tiene check-in de otra reserva
            //    (a las 15:00 ya habría alguien entrando)
            if (checkinDates.includes(startStr) && !checkoutDates.includes(startStr)) {
                return { error: 'checkin_blocked', date: start };
            }

            // 2. El check-out elegido no puede ser un día que ya tiene check-out de otra reserva
            //    (a las 11:00 ya habría alguien saliendo)
            if (checkoutDates.includes(endStr) && !checkinDates.includes(endStr)) {
                return { error: 'checkout_blocked', date: end };
            }

            // 3. Verificar que ningún día INTERMEDIO esté bloqueado
            //    ni sea check-in o check-out de otra reserva
            //    (un día intermedio con checkin/checkout ajeno significa solapamiento total)
            const current = new Date(start);
            current.setDate(current.getDate() + 1); // empezar desde el día siguiente al check-in

            while (current < end) { // < excluye el día de check-out
                const dateString = current.toISOString().split('T')[0];

                if (occupiedDates.includes(dateString)) {
                    return { error: 'occupied', date: new Date(current) };
                }

                // Si un día intermedio es check-in o check-out de otra reserva,
                // significa que hay una reserva que empieza o termina en medio del rango
                if (checkinDates.includes(dateString) || checkoutDates.includes(dateString)) {
                    return { error: 'overlap', date: new Date(current) };
                }

                current.setDate(current.getDate() + 1);
            }

            return true; // sin conflictos
        }
		
		// Validar que todos los días del rango tengan precio disponible
		function validateDateRangePrice(startDate, endDate) {
			const start = new Date(startDate);
			const end = new Date(endDate);
			
			// La fecha de salida es exclusiva: solo validamos las noches realmente reservadas.
			for (let d = new Date(start); d < end; d.setDate(d.getDate() + 1)) {
				const dateString = d.toISOString().split('T')[0];
				if (!tienePrecioDisponible(dateString)) {
					return false;
				}
			}
			return true;
		}
		
		// Mostrar error de día sin precio
		function showNoPriceError() {
			const modalElement = document.getElementById('noPriceModal');
			if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
				// Bootstrap 5
				const modal = new bootstrap.Modal(modalElement);
				modal.show();
				
				// Asegurar z-index después de que Bootstrap muestre el modal
				setTimeout(() => {
					modalElement.style.zIndex = '1050';
					if (modalElement.querySelector('.modal-dialog')) {
						modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
					}
					// Ajustar backdrop si existe
					const existingBackdrop = document.querySelector('.modal-backdrop');
					if (existingBackdrop) {
						existingBackdrop.style.zIndex = '1040';
					}
				}, 100);
			} else if (typeof $ !== 'undefined' && $.fn.modal) {
				// Bootstrap 4 con jQuery
				$(modalElement).modal('show');
				
				// Asegurar z-index después de que jQuery muestre el modal
				setTimeout(() => {
					modalElement.style.zIndex = '1050';
					if (modalElement.querySelector('.modal-dialog')) {
						modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
					}
					// Ajustar backdrop si existe
					const existingBackdrop = document.querySelector('.modal-backdrop');
					if (existingBackdrop) {
						existingBackdrop.style.zIndex = '1040';
					}
				}, 100);
			} else {
				// Fallback manual
				modalElement.style.display = 'block';
				modalElement.classList.add('show');
				document.body.classList.add('modal-open');
				
				// Crear backdrop
				const backdrop = document.createElement('div');
				backdrop.className = 'modal-backdrop fade show';
				backdrop.id = 'noPriceBackdrop';
				backdrop.style.zIndex = '1040';
				document.body.appendChild(backdrop);
				
				// Asegurar que el modal esté por encima
				modalElement.style.zIndex = '1050';
				if (modalElement.querySelector('.modal-dialog')) {
					modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
				}
			}
		}

        // Textos por defecto del modal (para restaurar después)
        const rangeErrorDefaults = {
            title:       document.getElementById('rangeErrorModalLabel')?.dataset.default || translations.calendar.range_default_title,
            message:     document.getElementById('rangeErrorMessage')?.textContent        || '',
            explanation: document.getElementById('rangeErrorExplanation')?.textContent    || '',
            action:      document.getElementById('rangeErrorAction')?.textContent         || ''
        };

        function setRangeErrorContent(title, message, explanation, action) {
            const titleEl       = document.getElementById('rangeErrorModalLabel');
            const messageEl     = document.getElementById('rangeErrorMessage');
            const explanationEl = document.getElementById('rangeErrorExplanation');
            const actionEl      = document.getElementById('rangeErrorAction');

            if (titleEl)       titleEl.textContent      = title;
            if (messageEl)     messageEl.textContent    = message;
            if (explanationEl) explanationEl.textContent = explanation;
            if (actionEl)      actionEl.textContent    = action;
        }

        function restoreRangeErrorDefaults() {
            const titleEl       = document.getElementById('rangeErrorModalLabel');
            const messageEl     = document.getElementById('rangeErrorMessage');
            const explanationEl = document.getElementById('rangeErrorExplanation');
            const actionEl      = document.getElementById('rangeErrorAction');

            if (titleEl)       titleEl.textContent      = rangeErrorDefaults.title;
            if (messageEl)     messageEl.textContent    = rangeErrorDefaults.message;
            if (explanationEl) explanationEl.textContent = rangeErrorDefaults.explanation;
            if (actionEl)      actionEl.textContent    = rangeErrorDefaults.action;
        }

        function showCheckinBlockedError(date) {
            const fechaFormateada = formatearFecha(date);

            setRangeErrorContent(
                translations.calendar.checkin_blocked_title,
                translations.calendar.checkin_blocked_msg.replace('{date}', fechaFormateada),
                translations.calendar.checkin_blocked_exp,
                translations.calendar.checkin_blocked_action
            );

            showRangeError();
        }

        function showCheckoutBlockedError(date) {
            const fechaFormateada = formatearFecha(date);

            setRangeErrorContent(
                translations.calendar.checkout_blocked_title,
                translations.calendar.checkout_blocked_msg.replace('{date}', fechaFormateada),
                translations.calendar.checkout_blocked_exp,
                translations.calendar.checkout_blocked_action
            );

            showRangeError();
        }

        function showOverlapError(date) {
            const fechaFormateada = formatearFecha(date);

            setRangeErrorContent(
                translations.calendar.range_overlap_title,
                translations.calendar.range_overlap_msg.replace('{date}', fechaFormateada),
                translations.calendar.range_overlap_exp,
                translations.calendar.range_overlap_action
            );

            showRangeError();
        }

		// Mostrar error de rango
        function showRangeError() {
            const modalElement = document.getElementById('rangeErrorModal');

            // Restaurar textos por defecto cuando se cierre el modal
            modalElement.addEventListener('hidden.bs.modal', restoreRangeErrorDefaults, { once: true });

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                setTimeout(() => {
                    modalElement.style.zIndex = '1050';
                    if (modalElement.querySelector('.modal-dialog')) {
                        modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
                    }
                    const existingBackdrop = document.querySelector('.modal-backdrop');
                    if (existingBackdrop) {
                        existingBackdrop.style.zIndex = '1040';
                    }
                }, 100);
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                $(modalElement).modal('show');
                setTimeout(() => {
                    modalElement.style.zIndex = '1050';
                    if (modalElement.querySelector('.modal-dialog')) {
                        modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
                    }
                    const existingBackdrop = document.querySelector('.modal-backdrop');
                    if (existingBackdrop) {
                        existingBackdrop.style.zIndex = '1040';
                    }
                }, 100);
            } else {
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                document.body.classList.add('modal-open');
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'rangeErrorBackdrop';
                backdrop.style.zIndex = '1040';
                document.body.appendChild(backdrop);
                modalElement.style.zIndex = '1050';
                if (modalElement.querySelector('.modal-dialog')) {
                    modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
                }
            }
        }
		
		// Actualizar estados visuales de selección
		function updateSelectionStates() {
			document.querySelectorAll('.calendar-day').forEach(day => {
				day.classList.remove('checkin', 'checkout', 'in-range');
				
				// Eliminar etiqueta de precio anterior si existe
				const existingPriceLabel = day.querySelector('.selected-day-price');
				if (existingPriceLabel) {
					existingPriceLabel.remove();
				}
				
				if (day.dataset.date) {
					const dayDateString = day.dataset.date;
					
					// Convertir selectedStartDate y selectedEndDate a strings para comparar
					const startDateString = selectedStartDate ? selectedStartDate.toISOString().split('T')[0] : null;
					const endDateString = selectedEndDate ? selectedEndDate.toISOString().split('T')[0] : null;
					
					let isSelected = false;
					
					if (selectedStartDate && dayDateString === startDateString) {
						day.classList.add('checkin');
						isSelected = true;
					} else if (selectedEndDate && dayDateString === endDateString) {
						day.classList.add('checkout');
						isSelected = true;
					} else if (selectedStartDate && selectedEndDate && 
							  dayDateString > startDateString && dayDateString < endDateString) {
						day.classList.add('in-range');
						isSelected = true;
					}
					
					// Agregar etiqueta de precio sobre días seleccionados (solo en móviles/tablets)
					if (isSelected) {
						const precio = getTarifaPorFecha(dayDateString);
						const priceLabel = document.createElement('div');
						priceLabel.className = 'selected-day-price';
						priceLabel.textContent = formatearPrecioCOP(precio);
						day.style.position = 'relative';
						day.appendChild(priceLabel);
					}
				}
			});
		}
		
		
		// Actualizar resumen de reserva
		function updateReservationSummary() {
			const checkinDate = document.getElementById('checkinDate');
			const checkoutDate = document.getElementById('checkoutDate');
			const nightsCount = document.getElementById('nightsCount');
			const totalPrice = document.getElementById('totalPrice');
			const reserveBtn = document.getElementById('reserveBtn');
			
			if (selectedStartDate) {
				checkinDate.textContent = selectedStartDate.toLocaleDateString(translations.locale);
				
				if (selectedEndDate) {
					checkoutDate.textContent = selectedEndDate.toLocaleDateString(translations.locale);
					
					// Calcular noches
					const timeDiff = selectedEndDate.getTime() - selectedStartDate.getTime();
					const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
					nightsCount.textContent = nights;
					
					// Calcular total usando precios reales por día
					let total = 0;
					const currentDate = new Date(selectedStartDate);
					
					while (currentDate < selectedEndDate) {
						const dateString = currentDate.toISOString().split('T')[0];
						const precio = getTarifaPorFecha(dateString);
						total += precio;
						currentDate.setDate(currentDate.getDate() + 1);
					}
					
					totalPrice.textContent = '$' + total.toLocaleString('es-CO') + ' COP';
					
					// Habilitar botón de reserva
					reserveBtn.disabled = false;
				} else {
					checkoutDate.textContent = '-';
					nightsCount.textContent = '-';
					totalPrice.textContent = '$0 COP';
					reserveBtn.disabled = true;
				}
			} else {
				checkinDate.textContent = '-';
				checkoutDate.textContent = '-';
				nightsCount.textContent = '-';
				totalPrice.textContent = '$0 COP';
				reserveBtn.disabled = true;
			}
		}
		
		// Configurar event listeners
		function setupEventListeners() {
			// Evitar configurar listeners múltiples veces para elementos específicos
			if (eventListenersSetup) {
				return;
			}
			eventListenersSetup = true;
			
			// Navegación del calendario
			const prevMonthBtn = document.getElementById('prevMonth');
			const nextMonthBtn = document.getElementById('nextMonth');
			
			if (prevMonthBtn) {
				// Remover listeners anteriores si existen
				const newPrevBtn = prevMonthBtn.cloneNode(true);
				prevMonthBtn.parentNode.replaceChild(newPrevBtn, prevMonthBtn);
				
				newPrevBtn.addEventListener('click', (e) => {
					e.preventDefault();
					const year = currentDate.getFullYear();
					const month = currentDate.getMonth();
					
					// Manejar correctamente el cambio de año si es necesario
					let newYear = year;
					let newMonth = month - 1;
					
					if (newMonth < 0) {
						newMonth = 11;
						newYear = year - 1;
					}
					
					// Crear nueva fecha
					currentDate = new Date(newYear, newMonth, 1);
					
					// Recargar tarifas para el nuevo mes visible
					loadTarifas();
				});
			}
			
			if (nextMonthBtn) {
				// Remover listeners anteriores si existen
				const newNextBtn = nextMonthBtn.cloneNode(true);
				nextMonthBtn.parentNode.replaceChild(newNextBtn, nextMonthBtn);
				
				newNextBtn.addEventListener('click', (e) => {
					e.preventDefault();
					const year = currentDate.getFullYear();
					const month = currentDate.getMonth();
					
					// Manejar correctamente el cambio de año si es necesario
					let newYear = year;
					let newMonth = month + 1;
					
					if (newMonth > 11) {
						newMonth = 0;
						newYear = year + 1;
					}
					
					// Crear nueva fecha
					currentDate = new Date(newYear, newMonth, 1);
					
					// Recargar tarifas para el nuevo mes visible
					loadTarifas();
				});
			}
			
			// Deseleccionar al hacer clic fuera del calendario (solo una vez)
			if (!clickOutsideHandler) {
				clickOutsideHandler = (e) => {
					// Si hay una selección activa (check-in seleccionado pero no check-out)
					if (selectedStartDate && !selectedEndDate) {
						// Verificar si el clic fue fuera del calendario
						const calendarContainer = document.querySelector('.calendar-container');
						const isClickInsideCalendar = calendarContainer && calendarContainer.contains(e.target);
						
						// También verificar que no sea clic en botones de navegación del calendario
						const isNavigationClick = e.target.id === 'prevMonth' || e.target.id === 'nextMonth';
						
						// Si el clic fue fuera del calendario y no es navegación
						if (!isClickInsideCalendar && !isNavigationClick) {
							clearSelection();
							updateReservationSummary();
						}
					}
				};
				document.addEventListener('click', clickOutsideHandler);
			}
			
			// Dejar que Bootstrap maneje el cierre del modal automáticamente
			// Solo limpiar el formulario cuando el modal se cierre completamente
			const reservationModal = document.getElementById('reservationModal');
			if (reservationModal) {
			// Evento que se dispara cuando el modal se ha cerrado completamente
			reservationModal.addEventListener('hidden.bs.modal', function () {
				// Resetear al primer paso
				currentReservationSection = 1;
				document.querySelectorAll('#reservationModal .form-section').forEach((section, index) => {
					section.classList.remove('active');
					section.style.display = index === 0 ? 'block' : 'none';
					if (index === 0) section.classList.add('active');
				});
				
				// Resetear indicador de progreso
				document.querySelectorAll('#reservationModal .progress-step').forEach((step, index) => {
					step.classList.remove('active', 'completed');
					if (index === 0) {
						step.classList.add('active');
					} else {
						step.classList.add('inactive');
					}
				});
				
				// Limpiar formulario cuando el modal se cierre
				const form = document.getElementById('reservationForm');
				if (form) {
					form.reset();
				}
				
				// Limpiar mensajes de error
				document.querySelectorAll('#reservationModal .error-message').forEach(el => el.textContent = '');
				document.querySelectorAll('#reservationModal .form-control').forEach(el => el.classList.remove('error'));
				
				// Limpiar selección del calendario
				clearSelection();
				
				// Asegurar que el body se restaure completamente
				document.body.classList.remove('modal-open');
				document.body.style.overflow = '';
				document.body.style.paddingRight = '';
				
				// Remover todos los backdrops que puedan quedar
				const backdrops = document.querySelectorAll('.modal-backdrop');
				backdrops.forEach(backdrop => backdrop.remove());
			});
			}
			
			// Botón de cerrar del modal de error
			const closeErrorBtn = document.querySelector('#rangeErrorModal .btn-close');
			if (closeErrorBtn) {
				closeErrorBtn.addEventListener('click', () => {
					// Cerrar modal manualmente
					const modal = document.getElementById('rangeErrorModal');
					modal.style.display = 'none';
					modal.classList.remove('show');
					document.body.classList.remove('modal-open');
					
					// Remover backdrop si existe
					const backdrop = document.querySelector('.modal-backdrop');
					if (backdrop) {
						backdrop.remove();
					}
				});
			}
			
			// Botón de reserva
			const reserveBtn = document.getElementById('reserveBtn');
			if (reserveBtn) {
				// Remover listener anterior si existe (usando cloneNode para limpiar)
				const newReserveBtn = reserveBtn.cloneNode(true);
				reserveBtn.parentNode.replaceChild(newReserveBtn, reserveBtn);
				
				// Agregar listener al nuevo botón - usar mousedown para evitar problemas con disabled
				newReserveBtn.addEventListener('mousedown', function(e) {
					e.preventDefault();
					e.stopPropagation();
					
					// Si está deshabilitado, no hacer nada
					if (newReserveBtn.disabled) {
						return;
					}
					
					if (selectedStartDate && selectedEndDate) {
						try {
							openReservationModal();
						} catch (error) {
							alert('Error al abrir el formulario. Por favor recarga la página.');
						}
					} else {
						alert('Por favor selecciona las fechas de check-in y check-out en el calendario.');
					}
				});
				
				// También agregar listener de click normal
				newReserveBtn.addEventListener('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					
					// Si está deshabilitado, no hacer nada
					if (newReserveBtn.disabled) {
						return;
					}
					
					if (selectedStartDate && selectedEndDate) {
						try {
							openReservationModal();
						} catch (error) {
							alert('Error al abrir el formulario. Por favor recarga la página.');
						}
					} else {
						alert('Por favor selecciona las fechas de check-in y check-out en el calendario.');
					}
				});
				
				// También agregar listener usando onclick como fallback
				newReserveBtn.onclick = function(e) {
					e.preventDefault();
					e.stopPropagation();
					if (selectedStartDate && selectedEndDate) {
						openReservationModal();
					}
				};
			}
			
			// Modal de reserva - Botón final de envío
			const submitReservationFinalBtn = document.getElementById('submitReservationFinal');
			if (submitReservationFinalBtn) {
				submitReservationFinalBtn.addEventListener('click', function() {
					// Validar último paso antes de enviar
					if (validateReservationSection(5)) {
						submitReservation();
					}
				});
			}
			
			// Botón antiguo de submit (por si acaso)
			const oldSubmitBtn = document.getElementById('submitReservation');
			if (oldSubmitBtn) {
				oldSubmitBtn.addEventListener('click', submitReservation);
			}
			
			// Botón cancelar del modal - Bootstrap lo maneja automáticamente con data-bs-dismiss
			// No necesitamos agregar event listener, la limpieza se hace en hidden.bs.modal
			
			// Bootstrap maneja automáticamente el cierre al hacer clic en el backdrop
			// No necesitamos agregar código adicional
			
			// Botón "Entendido" del modal de error
			const entendidoBtn = document.querySelector('#rangeErrorModal .btn-primary');
			if (entendidoBtn) {
				entendidoBtn.addEventListener('click', () => {
					const modalElement = document.getElementById('rangeErrorModal');
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						// Bootstrap 5
						const modal = bootstrap.Modal.getInstance(modalElement);
						if (modal) modal.hide();
					} else if (typeof $ !== 'undefined' && $.fn.modal) {
						// Bootstrap 4 con jQuery
						$(modalElement).modal('hide');
					} else {
						// Fallback manual
						modalElement.style.display = 'none';
						modalElement.classList.remove('show');
						document.body.classList.remove('modal-open');
						
						// Remover backdrop
						const backdrop = document.getElementById('rangeErrorBackdrop');
						if (backdrop) backdrop.remove();
					}
				});
			}
			
			// Checkbox de Palmira
			const vivePalmiraCheckbox = document.getElementById('vivePalmira');
			if (vivePalmiraCheckbox) {
				vivePalmiraCheckbox.addEventListener('change', (e) => {
					const palmiraInfo = document.getElementById('palmiraInfo');
					if (palmiraInfo) {
						palmiraInfo.style.display = e.target.checked ? 'block' : 'none';
					}
				});
			}
			
			// Método de pago
			document.querySelectorAll('input[name="metodoPago"]').forEach(radio => {
				radio.addEventListener('change', (e) => {
					const descuentoInfo = document.getElementById('descuentoInfo');
					if (e.target.value === 'transferencia') {
						descuentoInfo.style.display = 'block';
						updateModalCostSummary();
					} else {
						descuentoInfo.style.display = 'none';
						updateModalCostSummary();
					}
					// Actualizar resumen del paso 5 si estamos en ese paso
					if (currentReservationSection === 5) {
						updateReservationFormSummary();
					}
				});
			});
			
			// Fecha de nacimiento - actualizar descuento por cumpleaños
			const fechaNacimientoInput = document.getElementById('fechaNacimiento');
			
			// Prevenir escritura manual y asegurar que solo se use el calendario
			if (fechaNacimientoInput) {
				// Prevenir todas las formas de entrada manual
				fechaNacimientoInput.addEventListener('keydown', (e) => {
					// Permitir solo teclas de navegación y escape
					const allowedKeys = ['Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'];
					if (!allowedKeys.includes(e.key) && !e.ctrlKey && !e.metaKey) {
						e.preventDefault();
						return false;
					}
				});
				
				fechaNacimientoInput.addEventListener('keypress', (e) => {
					e.preventDefault();
					return false;
				});
				
				fechaNacimientoInput.addEventListener('paste', (e) => {
					e.preventDefault();
					return false;
				});
				
				fechaNacimientoInput.addEventListener('drop', (e) => {
					e.preventDefault();
					return false;
				});
				
				// Hacer que todo el campo abra el calendario al hacer clic
				fechaNacimientoInput.addEventListener('click', () => {
					// Usar showPicker() si está disponible (navegadores modernos)
					if (typeof fechaNacimientoInput.showPicker === 'function') {
						fechaNacimientoInput.showPicker();
					}
					// Si no está disponible, el comportamiento nativo del input date abrirá el calendario
				});
				
				// Actualizar resumen cuando cambie la fecha de nacimiento
				fechaNacimientoInput.addEventListener('change', () => {
					updateModalCostSummary();
				});
			}
			
			// ==========================================================
			// FUNCIONALIDAD DE TÉRMINOS Y CONDICIONES
			// ==========================================================
			let terminosLeidos = false;
			let scrollCheckInterval = null;
			let readingTimer = null;
			
			// Contenido de términos y condiciones
			const terminosHTML = `
				<div style="padding: 25px; max-width: 800px; margin: 0 auto;">
					<div style="text-align: center; margin-bottom: 30px;">
						<h2 style="color: #1e3c72; margin-bottom: 10px; font-size: 1.8rem;">MY SUITE IN CARTAGENA</h2>
						<p style="color: #666; font-size: 0.95rem; margin: 0;">Autorización para el Tratamiento de Datos Personales</p>
					</div>
					
					<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #1e3c72;">
						<p style="line-height: 1.8; color: #333; margin: 0; text-align: justify;">
							Dando cumplimiento a lo dispuesto en la <strong>Ley 1581 de 2012</strong>, "Por el cual se dictan disposiciones generales para la protección de datos personales" y de conformidad con lo señalado en el <strong>Decreto 1377 de 2013</strong>, con la firma de este documento manifiesto que he sido informado por <strong>My Suite In Cartagena (MSIC)</strong> de lo siguiente:
						</p>
					</div>
					
					<ol style="line-height: 1.9; color: #333; padding-left: 25px; margin: 0;">
						<li style="margin-bottom: 18px; text-align: justify;">
							<strong>MSIC</strong> actuará como <strong>Responsable del Tratamiento</strong> de datos personales de los cuales soy titular y que, conjunta o separadamente podrá recolectar, usar y tratar mis datos personales conforme la <strong>Política de Tratamiento de Datos Personales MSIC</strong> disponible en página web de la entidad.
						</li>
						
						<li style="margin-bottom: 18px; text-align: justify;">
							Que me ha sido informada la(s) finalidad(es) de la recolección de los datos personales, la cual consiste en: <strong>NOMBRE COMPLETO, CORREO ELECTRÓNICO, NÚMERO DE TELÉFONO CELULAR Y FECHA DE NACIMIENTO</strong>.
						</li>
						
						<li style="margin-bottom: 18px; text-align: justify;">
							Es de carácter <strong>facultativo o voluntario</strong> responder preguntas que versen sobre Datos Sensibles o sobre menores de edad.
						</li>
						
						<li style="margin-bottom: 18px; text-align: justify;">
							Mis derechos como titular de los datos son los previstos en la Constitución y la ley, especialmente el derecho a <strong>conocer, actualizar, rectificar y suprimir</strong> mi información personal, así como el derecho a <strong>revocar el consentimiento</strong> otorgado para el tratamiento de datos personales.
						</li>
						
						<li style="margin-bottom: 18px; text-align: justify;">
							Los derechos pueden ser ejercidos a través de los canales dispuestos por <strong>MSIC</strong> y observando la <strong>Política de Tratamiento de Datos Personales de MSIC</strong>.
						</li>
						
						<li style="margin-bottom: 18px; text-align: justify;">
							Mediante la página web de la entidad <strong>(www.mysuiteincartagena.com.co)</strong>, podré radicar cualquier tipo de requerimiento relacionado con el tratamiento de mis datos personales.
						</li>
						
						<li style="margin-bottom: 18px; text-align: justify;">
							<strong>MSIC</strong> garantizará la <strong>confidencialidad, libertad, seguridad, veracidad, transparencia, acceso y circulación restringida</strong> de mis datos y se reservará el derecho de modificar su Política de Tratamiento de Datos Personales en cualquier momento. Cualquier cambio será informado y publicado oportunamente en la página web.
						</li>
						
						<li style="margin-bottom: 18px; text-align: justify;">
							Teniendo en cuenta lo anterior, <strong>autorizo de manera voluntaria, previa, explícita, informada e inequívoca a MSIC</strong> para tratar mis datos personales de acuerdo con su Política de Tratamiento de Datos Personales para los fines relacionados con su objeto y en especial para fines legales, contractuales, misionales descritos en la <strong>Política de Tratamiento de Datos Personales MSIC</strong>.
						</li>
						
						<li style="margin-bottom: 18px; text-align: justify;">
							La información obtenida para el Tratamiento de mis datos personales la he suministrado de forma <strong>voluntaria y es verídica</strong>.
						</li>
					</ol>
					
					<div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef; text-align: center;">
						<p style="color: #666; font-size: 0.9rem; margin: 0;">
							<strong>My Suite In Cartagena</strong><br>
							www.mysuiteincartagena.com.co
						</p>
					</div>
				</div>
			`;
			
			// Referencias a elementos del modal
			const btnLeerTerminos = document.getElementById('btnLeerTerminos');
			const terminosModal = document.getElementById('terminosModal');
			const terminosContent = document.getElementById('terminosContent');
			const btnAceptarTerminos = document.getElementById('btnAceptarTerminos');
			const aceptaPoliticaCheckbox = document.getElementById('aceptaPolitica');
			const terminosStatus = document.getElementById('terminosStatus');
			const readingProgress = document.getElementById('readingProgress');
			const readingStatus = document.getElementById('readingStatus');
			
			// Abrir modal de términos y condiciones
			if (btnLeerTerminos && terminosModal) {
				btnLeerTerminos.addEventListener('click', function() {
					// Cargar el contenido
					if (terminosContent) {
						terminosContent.innerHTML = terminosHTML;
					}
					
					// Abrir modal con Bootstrap
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						const modal = new bootstrap.Modal(terminosModal);
						modal.show();
						
						// Iniciar tracking después de que el modal se muestre
						terminosModal.addEventListener('shown.bs.modal', function() {
							setTimeout(() => {
								startReadingTracking();
							}, 100);
						}, { once: true });
					} else if (typeof $ !== 'undefined' && $.fn.modal) {
						$(terminosModal).modal('show');
						$(terminosModal).on('shown.bs.modal', function() {
							setTimeout(() => {
								startReadingTracking();
							}, 100);
						});
					}
				});
			}
			
			// Función para rastrear la lectura del documento
			function startReadingTracking() {
				const modalBody = terminosModal ? terminosModal.querySelector('.modal-body') : null;
				
				if (!modalBody) {
					setTimeout(() => startReadingTracking(), 300);
					return;
				}
				
				// Reiniciar estado
				terminosLeidos = false;
				if (btnAceptarTerminos) {
					btnAceptarTerminos.disabled = true;
					btnAceptarTerminos.style.opacity = '0.6';
					btnAceptarTerminos.style.cursor = 'not-allowed';
				}
				if (readingProgress) {
					readingProgress.style.width = '0%';
				}
				if (readingStatus) {
					readingStatus.innerHTML = '<i class="fa fa-info-circle"></i> <span id="readingStatusText"><?php echo __('form.please_read_document'); ?></span>';
					readingStatus.classList.remove('text-success');
					readingStatus.classList.add('text-muted');
				}
				
				// Función para verificar scroll
				function checkScrollProgress() {
					if (terminosLeidos) return;
					
					const scrollTop = modalBody.scrollTop || 0;
					const scrollHeight = modalBody.scrollHeight || 0;
					const clientHeight = modalBody.clientHeight || 0;
					
					if (scrollHeight > clientHeight) {
						const porcentajeScroll = Math.min(((scrollTop + clientHeight) / scrollHeight) * 100, 100);
						
						if (readingProgress) {
							readingProgress.style.width = porcentajeScroll + '%';
						}
						
						// Habilitar botón si ha scrolleado al 50% o más
						if (porcentajeScroll >= 50 && !terminosLeidos) {
							enableAcceptButton();
							if (scrollCheckInterval) {
								clearInterval(scrollCheckInterval);
								scrollCheckInterval = null;
							}
							return true;
						}
					} else {
						// Si no hay scroll (contenido cabe en pantalla)
						if (readingProgress) {
							readingProgress.style.width = '100%';
						}
					}
					return false;
				}
				
				// Tiempo mínimo de lectura (3 segundos)
				let tiempoLeido = 0;
				const tiempoMinimo = 3000;
				
				// Verificar scroll periódicamente
				scrollCheckInterval = setInterval(() => {
					if (checkScrollProgress()) {
						return;
					}
					
					tiempoLeido += 200;
					
					// Si ha pasado el tiempo mínimo, habilitar
					if (tiempoLeido >= tiempoMinimo && !terminosLeidos) {
						const scrollTop = modalBody.scrollTop || 0;
						const scrollHeight = modalBody.scrollHeight || 0;
						const clientHeight = modalBody.clientHeight || 0;
						
						if (scrollTop > 20 || scrollHeight <= clientHeight) {
							enableAcceptButton();
							if (scrollCheckInterval) {
								clearInterval(scrollCheckInterval);
								scrollCheckInterval = null;
							}
						}
					}
				}, 200);
				
				// Listener de scroll en tiempo real
				const scrollHandler = function() {
					if (terminosLeidos) return;
					
					const scrollTop = modalBody.scrollTop || 0;
					const scrollHeight = modalBody.scrollHeight || 0;
					const clientHeight = modalBody.clientHeight || 0;
					
					if (scrollHeight > clientHeight) {
						const porcentaje = Math.min(((scrollTop + clientHeight) / scrollHeight) * 100, 100);
						
						if (readingProgress) {
							readingProgress.style.width = porcentaje + '%';
						}
						
						if (porcentaje >= 50) {
							enableAcceptButton();
							if (scrollCheckInterval) {
								clearInterval(scrollCheckInterval);
								scrollCheckInterval = null;
							}
							modalBody.removeEventListener('scroll', scrollHandler);
						}
					}
				};
				
				modalBody.addEventListener('scroll', scrollHandler, { passive: true });
				
				// Verificar inmediatamente
				setTimeout(checkScrollProgress, 100);
			}
			
			// Función para habilitar el botón de aceptar
			function enableAcceptButton() {
				if (terminosLeidos) return;
				
				terminosLeidos = true;
				
				const btn = document.getElementById('btnAceptarTerminos');
				if (!btn) return;
				
				btn.disabled = false;
				btn.removeAttribute('disabled');
				btn.style.opacity = '1';
				btn.style.cursor = 'pointer';
				
				if (readingProgress) {
					readingProgress.style.width = '100%';
				}
				
				if (readingStatus) {
					const statusText = document.getElementById('readingStatusText');
					if (statusText) {
						statusText.textContent = '<?php echo __('form.document_read'); ?>';
					}
					readingStatus.innerHTML = '<i class="fa fa-check-circle text-success"></i> <?php echo __('form.document_read'); ?>';
					readingStatus.classList.remove('text-muted');
					readingStatus.classList.add('text-success');
				}
			}
			
			// Función para manejar la aceptación de términos
			function handleAcceptTerms() {
				if (!terminosLeidos) {
					alert('Por favor, lee el documento completo antes de aceptar.');
					return false;
				}
				
				// Habilitar checkbox
				if (aceptaPoliticaCheckbox) {
					aceptaPoliticaCheckbox.disabled = false;
					aceptaPoliticaCheckbox.checked = true;
					aceptaPoliticaCheckbox.removeAttribute('disabled');
					
					// Disparar evento change
					const changeEvent = new Event('change', { bubbles: true, cancelable: true });
					aceptaPoliticaCheckbox.dispatchEvent(changeEvent);
				}
				
				// Actualizar estilo del label
				const label = aceptaPoliticaCheckbox ? aceptaPoliticaCheckbox.nextElementSibling : null;
				if (label) {
					label.style.cursor = 'pointer';
					label.style.opacity = '1';
				}
				
				// Limpiar mensaje de error
				const errorElement = document.getElementById('aceptaPoliticaError');
				if (errorElement) {
					errorElement.textContent = '';
					errorElement.style.display = 'none';
				}
				
				// Actualizar estado visual
				if (terminosStatus) {
					terminosStatus.innerHTML = '<i class="fa fa-check-circle text-success"></i> <?php echo __('form.terms_read'); ?>';
					terminosStatus.classList.remove('text-muted', 'text-danger');
					terminosStatus.classList.add('text-success');
				}
				
				// Limpiar intervalos
				if (scrollCheckInterval) {
					clearInterval(scrollCheckInterval);
					scrollCheckInterval = null;
				}
				if (readingTimer) {
					clearInterval(readingTimer);
					readingTimer = null;
				}
				
				// Cerrar modal y restaurar scroll
				setTimeout(() => {
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						const modal = bootstrap.Modal.getInstance(terminosModal);
						if (modal) {
							modal.hide();
						}
					} else if (typeof $ !== 'undefined' && $.fn.modal) {
						$(terminosModal).modal('hide');
					} else {
						terminosModal.style.display = 'none';
						terminosModal.classList.remove('show');
					}
					
					// Restaurar el body completamente
					document.body.classList.remove('modal-open');
					document.body.style.overflow = '';
					document.body.style.paddingRight = '';
					
					// Remover todos los backdrops que puedan existir
					const backdrops = document.querySelectorAll('.modal-backdrop');
					backdrops.forEach(backdrop => backdrop.remove());
					
					// Verificar que el checkbox esté correctamente marcado
					setTimeout(() => {
						if (aceptaPoliticaCheckbox && !aceptaPoliticaCheckbox.checked) {
							aceptaPoliticaCheckbox.checked = true;
							aceptaPoliticaCheckbox.disabled = false;
						}
						
						// Asegurar que el formulario de reserva sea scrolleable
						const reservationModal = document.getElementById('reservationModal');
						if (reservationModal) {
							reservationModal.style.overflow = '';
							reservationModal.style.overflowY = 'auto';
						}
					}, 100);
				}, 100);
				
				return true;
			}
			
			// Botón aceptar términos - Event listeners
			if (btnAceptarTerminos) {
				btnAceptarTerminos.addEventListener('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					handleAcceptTerms();
					return false;
				});
			}
			
			// Limpiar cuando se cierra el modal sin aceptar
			if (terminosModal) {
				terminosModal.addEventListener('hidden.bs.modal', function() {
					// Restaurar el body completamente
					document.body.classList.remove('modal-open');
					document.body.style.overflow = '';
					document.body.style.paddingRight = '';
					
					// Remover todos los backdrops que puedan existir
					const backdrops = document.querySelectorAll('.modal-backdrop');
					backdrops.forEach(backdrop => {
						if (backdrop.parentNode) {
							backdrop.remove();
						}
					});
					
					if (scrollCheckInterval) {
						clearInterval(scrollCheckInterval);
						scrollCheckInterval = null;
					}
					if (readingTimer) {
						clearInterval(readingTimer);
						readingTimer = null;
					}
					// Reiniciar estado si no se aceptó
					if (!aceptaPoliticaCheckbox || !aceptaPoliticaCheckbox.checked) {
						terminosLeidos = false;
					}
					
					// Asegurar que el formulario de reserva sea scrolleable
					const reservationModal = document.getElementById('reservationModal');
					if (reservationModal) {
						reservationModal.style.overflow = '';
						reservationModal.style.overflowY = 'auto';
					}
				});
			}
			// ==========================================================
			// FIN FUNCIONALIDAD DE TÉRMINOS Y CONDICIONES
			// ==========================================================
			
			// Agregar validación en tiempo real para el celular
			const celularInput = document.getElementById('celular');
			const codigoPaisInput = document.getElementById('codigoPais');
			
			if (celularInput && codigoPaisInput) {
				// Validar formato del código de país (debe empezar con + y tener números)
				codigoPaisInput.addEventListener('input', function() {
					// Asegurar que empiece con +
					if (this.value && !this.value.startsWith('+')) {
						this.value = '+' + this.value.replace(/[^0-9]/g, '');
					} else {
						// Solo permitir + y números
						this.value = '+' + this.value.replace(/[^0-9]/g, '');
					}
					
					// Limitar a 5 caracteres máximo (ej: +1234)
					if (this.value.length > 5) {
						this.value = this.value.substring(0, 5);
					}
					
					// Limpiar error al cambiar código
					document.getElementById('celularError').textContent = '';
					celularInput.classList.remove('error');
				});
				
				// Asegurar que el código de país tenga el formato correcto al perder focus
				codigoPaisInput.addEventListener('blur', function() {
					if (this.value && !this.value.startsWith('+')) {
						this.value = '+' + this.value.replace(/[^0-9]/g, '');
					}
					if (!this.value || this.value === '+') {
						this.value = '+57'; // Valor por defecto
					}
				});
				
				// Validar en tiempo real mientras se escribe
				celularInput.addEventListener('input', function() {
					// Solo permitir números
					this.value = this.value.replace(/\D/g, '');
					
					// Limpiar error mientras escribe
					if (this.value.length > 0) {
						document.getElementById('celularError').textContent = '';
						this.classList.remove('error');
					}
				});
			}
		}
		
		// Variables globales para el control de secciones del formulario de reserva
		let currentReservationSection = 1;
		const totalReservationSections = 5;
		
		// Funciones para navegación entre secciones del formulario de reserva
		function nextReservationSection(sectionNumber) {
			if (validateReservationSection(sectionNumber)) {
				// Marcar sección actual como completada
				const currentStep = document.getElementById(`step${sectionNumber}`);
				if (currentStep) {
					currentStep.classList.remove('active');
					currentStep.classList.add('completed');
				}
				
				// Ocultar sección actual
				const currentSection = document.getElementById(`section${sectionNumber}`);
				if (currentSection) {
					currentSection.classList.remove('active');
					currentSection.style.display = 'none';
				}
				
				// Mostrar siguiente sección
				const nextSectionNum = sectionNumber + 1;
				const nextSection = document.getElementById(`section${nextSectionNum}`);
				if (nextSection) {
					nextSection.classList.add('active');
					nextSection.style.display = 'block';
				}
				
				// Actualizar indicador de progreso
				const nextStep = document.getElementById(`step${nextSectionNum}`);
				if (nextStep) {
					nextStep.classList.remove('inactive');
					nextStep.classList.add('active');
				}
				
				currentReservationSection = nextSectionNum;
				
				// Si llegamos al paso 5, actualizar el resumen
				if (nextSectionNum === 5) {
					updateReservationFormSummary();
				}
			}
		}
		
		function prevReservationSection(sectionNumber) {
			// Marcar sección actual como inactiva
			const currentStep = document.getElementById(`step${sectionNumber}`);
			if (currentStep) {
				currentStep.classList.remove('active');
				currentStep.classList.add('inactive');
			}
			
			// Ocultar sección actual
			const currentSection = document.getElementById(`section${sectionNumber}`);
			if (currentSection) {
				currentSection.classList.remove('active');
				currentSection.style.display = 'none';
			}
			
			// Mostrar sección anterior
			const prevSectionNum = sectionNumber - 1;
			const prevSection = document.getElementById(`section${prevSectionNum}`);
			if (prevSection) {
				prevSection.classList.add('active');
				prevSection.style.display = 'block';
			}
			
			// Actualizar indicador de progreso
			const prevStep = document.getElementById(`step${prevSectionNum}`);
			if (prevStep) {
				prevStep.classList.remove('completed');
				prevStep.classList.add('active');
			}
			
			currentReservationSection = prevSectionNum;
		}
		
		// Función para validar cada sección
		function validateReservationSection(sectionNumber) {
			let isValid = true;
			
			// Limpiar errores anteriores
			document.querySelectorAll('#reservationModal .error-message').forEach(el => el.textContent = '');
			document.querySelectorAll('#reservationModal .form-control').forEach(el => el.classList.remove('error'));
			
			if (sectionNumber === 1) {
				// Validar nombres, apellidos y celular
				const nombres = document.getElementById('nombres').value.trim();
				const apellidos = document.getElementById('apellidos').value.trim();
				const celularInput = document.getElementById('celular');
				const codigoPaisInput = document.getElementById('codigoPais');
				const celular = celularInput.value.trim();
				const isReadonly = celularInput.hasAttribute('readonly');
				
				if (!nombres) {
					document.getElementById('nombresError').textContent = 'El nombre es requerido';
					document.getElementById('nombres').classList.add('error');
					isValid = false;
				}
				
				if (!apellidos) {
					document.getElementById('apellidosError').textContent = 'Los apellidos son requeridos';
					document.getElementById('apellidos').classList.add('error');
					isValid = false;
				}
				
				// Solo validar celular si no es readonly (usuario no logueado o datos editables)
				if (!isReadonly) {
					if (!celular) {
						document.getElementById('celularError').textContent = 'El celular es requerido';
						celularInput.classList.add('error');
						isValid = false;
					} else {
						// Validar código de país
						const codigoPais = codigoPaisInput.value.trim();
						if (!codigoPais || !codigoPais.startsWith('+')) {
							document.getElementById('celularError').textContent = 'El código de país debe empezar con +';
							codigoPaisInput.classList.add('error');
							isValid = false;
						} else {
							codigoPaisInput.classList.remove('error');
						}
						
						// Validar número de celular (debe tener entre 7 y 15 dígitos)
						const celularLimpio = celular.replace(/\D/g, ''); // Solo números
						if (celularLimpio.length < 7 || celularLimpio.length > 15) {
							document.getElementById('celularError').textContent = 'El número de celular debe tener entre 7 y 15 dígitos';
							celularInput.classList.add('error');
							isValid = false;
						} else {
							document.getElementById('celularError').textContent = '';
							celularInput.classList.remove('error');
						}
					}
				} else {
					// Si es readonly, limpiar errores (los datos ya están validados)
					document.getElementById('celularError').textContent = '';
					celularInput.classList.remove('error');
					codigoPaisInput.classList.remove('error');
				}
				
				// Validar fecha de nacimiento
				const fechaNacimiento = document.getElementById('fechaNacimiento').value;
				if (!fechaNacimiento) {
					document.getElementById('fechaNacimientoError').textContent = <?php echo json_encode(__('form.birthday_required')); ?>;
					document.getElementById('fechaNacimiento').classList.add('error');
					isValid = false;
				} else {
					// Validar que no sea una fecha futura
					const fechaNac = new Date(fechaNacimiento);
					const hoy = new Date();
					hoy.setHours(0, 0, 0, 0); // Resetear horas para comparar solo fechas
					
					if (fechaNac > hoy) {
						document.getElementById('fechaNacimientoError').textContent = <?php echo json_encode(__('form.birthday_invalid')); ?>;
						document.getElementById('fechaNacimiento').classList.add('error');
						isValid = false;
					} else {
						document.getElementById('fechaNacimientoError').textContent = '';
						document.getElementById('fechaNacimiento').classList.remove('error');
					}
				}
			} else if (sectionNumber === 2) {
				// Validar adultos
				const adultos = document.getElementById('adultos').value;
				if (!adultos) {
					document.getElementById('adultosError').textContent = 'El número de adultos es requerido';
					document.getElementById('adultos').classList.add('error');
					isValid = false;
				}
			} else if (sectionNumber === 3) {
				// Validar correo
				const correo = document.getElementById('correo').value.trim();
				if (!correo) {
					document.getElementById('correoError').textContent = 'El correo es requerido';
					document.getElementById('correo').classList.add('error');
					isValid = false;
				} else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
					document.getElementById('correoError').textContent = 'El correo no es válido';
					document.getElementById('correo').classList.add('error');
					isValid = false;
				}
			} else if (sectionNumber === 4) {
				// No hay validación específica para el método de pago
				isValid = true;
			} else if (sectionNumber === 5) {
				// Validar términos y condiciones
				const aceptaPoliticaCheckbox = document.getElementById('aceptaPolitica');
				if (aceptaPoliticaCheckbox) {
					const aceptaPolitica = aceptaPoliticaCheckbox.checked && !aceptaPoliticaCheckbox.disabled;
					if (!aceptaPolitica) {
						const errorElement = document.getElementById('aceptaPoliticaError');
						if (errorElement) {
							errorElement.textContent = 'Debes leer y aceptar los términos y condiciones';
							errorElement.style.display = 'block';
						}
						isValid = false;
					} else {
						// Limpiar error si está marcado
						const errorElement = document.getElementById('aceptaPoliticaError');
						if (errorElement) {
							errorElement.textContent = '';
							errorElement.style.display = 'none';
						}
					}
				} else {
					isValid = false;
				}
			}
			
			return isValid;
		}
		
		// Función para actualizar el resumen en el paso 5 del formulario
		function updateReservationFormSummary() {
			document.getElementById('summaryNombres').textContent = document.getElementById('nombres').value || '-';
			document.getElementById('summaryApellidos').textContent = document.getElementById('apellidos').value || '-';
			const codigoPais = document.getElementById('codigoPais').value;
			const celular = document.getElementById('celular').value;
			document.getElementById('summaryCelular').textContent = celular ? (codigoPais + ' ' + celular) : '-';
			document.getElementById('summaryCorreo').textContent = document.getElementById('correo').value || '-';
			document.getElementById('summaryAdultos').textContent = document.getElementById('adultos').value || '-';
			document.getElementById('summaryNinos').textContent = document.getElementById('ninos').value || '0';
			
			const metodoPago = document.querySelector('input[name="metodoPago"]:checked');
			if (metodoPago) {
				const metodoTexto = metodoPago.value === 'tarjeta_credito' ? 'Tarjeta de Crédito' : 'Transferencia';
				document.getElementById('summaryMetodoPago').textContent = metodoTexto;
			}
			
			// Calcular y mostrar descuentos
			if (selectedStartDate && selectedEndDate) {
				// Calcular subtotal
				let subtotal = 0;
				const currentDate = new Date(selectedStartDate);
				while (currentDate < selectedEndDate) {
					const dateString = currentDate.toISOString().split('T')[0];
					const precio = getTarifaPorFecha(dateString);
					subtotal += precio;
					currentDate.setDate(currentDate.getDate() + 1);
				}
				
				// Mostrar subtotal (con redondeo)
				const summarySubtotal = document.getElementById('summarySubtotal');
				if (summarySubtotal) {
					summarySubtotal.textContent = '$' + Math.round(subtotal).toLocaleString('es-CO') + ' COP';
				}
				
				// Calcular descuentos
				const metodoPagoRadio = document.querySelector('input[name="metodoPago"]:checked');
				const metodoPagoValue = metodoPagoRadio ? metodoPagoRadio.value : 'tarjeta_credito';
				
				let descuentoEfectivo = 0;
				let descuentoFidelidad = 0;
				let descuentoCumpleanos = 0;
				
				// PASO 1: Descuento por fidelidad (sobre subtotal original) - con redondeo
				<?php if ($user_logged_in && isset($descuentos) && isset($descuentos['fidelidad']) && $descuentos['fidelidad']['activo']): ?>
				descuentoFidelidad = Math.round(subtotal * <?php echo $descuentos['fidelidad']['porcentaje'] / 100; ?>);
				<?php endif; ?>
				
				// PASO 2: Descuento por cumpleaños (sobre subtotal original) - con redondeo
				<?php if ($user_logged_in && isset($user_data) && is_array($user_data) && isset($user_data['fecha_nacimiento']) && !empty($user_data['fecha_nacimiento'])): ?>
				const fechaNacimientoBD = '<?php echo htmlspecialchars($user_data['fecha_nacimiento'], ENT_QUOTES, 'UTF-8'); ?>';
				if (fechaNacimientoBD) {
					const fechaNac = new Date(fechaNacimientoBD);
					const cumpleanosDia = fechaNac.getDate();
					const cumpleanosMes = fechaNac.getMonth();
					const fechaEntrada = new Date(selectedStartDate);
					const fechaSalida = new Date(selectedEndDate);
					const añoReserva = fechaEntrada.getFullYear();
					const cumpleanosActual = new Date(añoReserva, cumpleanosMes, cumpleanosDia);
					
					if (cumpleanosActual >= fechaEntrada && cumpleanosActual <= fechaSalida) {
						descuentoCumpleanos = Math.round(subtotal * <?php echo (isset($descuentos) && isset($descuentos['cumpleanos']) && $descuentos['cumpleanos']['activo']) ? $descuentos['cumpleanos']['porcentaje'] / 100 : 0.30; ?>);
					}
				}
				<?php endif; ?>
				
				// PASO 3: Crear subtotal intermedio después de fidelidad y cumpleaños - con redondeo
				const subtotalIntermedio = Math.round(subtotal - descuentoFidelidad - descuentoCumpleanos);
				
				// PASO 4: Descuento por efectivo (sobre subtotal intermedio) - con redondeo
				if (metodoPagoValue === 'transferencia') {
					descuentoEfectivo = Math.round(subtotalIntermedio * <?php echo (isset($descuentos) && isset($descuentos['promocional']) && $descuentos['promocional']['activo']) ? $descuentos['promocional']['porcentaje'] / 100 : 0.03; ?>);
				}
				
				// Mostrar descuento por fidelidad
				const descuentoFidelidadRow = document.getElementById('summaryDescuentoFidelidad');
				const descuentoFidelidadValor = document.getElementById('summaryDescuentoFidelidadValor');
				if (descuentoFidelidadRow && descuentoFidelidadValor) {
					if (descuentoFidelidad > 0) {
						descuentoFidelidadRow.style.display = 'flex';
						descuentoFidelidadValor.textContent = '-$' + Math.round(descuentoFidelidad).toLocaleString('es-CO') + ' COP';
					} else {
						descuentoFidelidadRow.style.display = 'none';
					}
				}
				
				// Mostrar descuento por cumpleaños
				const descuentoCumpleanosRow = document.getElementById('summaryDescuentoCumpleanos');
				const descuentoCumpleanosValor = document.getElementById('summaryDescuentoCumpleanosValor');
				if (descuentoCumpleanosRow && descuentoCumpleanosValor) {
					if (descuentoCumpleanos > 0) {
						descuentoCumpleanosRow.style.display = 'flex';
						descuentoCumpleanosValor.textContent = '-$' + Math.round(descuentoCumpleanos).toLocaleString('es-CO') + ' COP';
					} else {
						descuentoCumpleanosRow.style.display = 'none';
					}
				}
				
				// Mostrar descuento por efectivo y subtotal intermedio
				const descuentoEfectivoRow = document.getElementById('summaryDescuentoEfectivo');
				const descuentoEfectivoValor = document.getElementById('summaryDescuentoEfectivoValor');
				const summarySubtotalIntermedio = document.getElementById('summarySubtotalIntermedio');
				const subtotalIntermedioContainer = document.getElementById('summarySubtotalIntermedioContainer');
				
				if (descuentoEfectivo > 0) {
					// Si hay descuento por efectivo, mostrar subtotal intermedio y descuento
					if (descuentoEfectivoRow && descuentoEfectivoValor) {
						descuentoEfectivoRow.style.display = 'flex';
						descuentoEfectivoValor.textContent = '-$' + Math.round(descuentoEfectivo).toLocaleString('es-CO') + ' COP';
					}
					if (summarySubtotalIntermedio) {
						summarySubtotalIntermedio.textContent = '$' + Math.round(subtotalIntermedio).toLocaleString('es-CO') + ' COP';
					}
					if (subtotalIntermedioContainer) {
						subtotalIntermedioContainer.style.display = 'flex';
					}
				} else {
					// Si NO hay descuento por efectivo, ocultar subtotal intermedio y descuento
					if (descuentoEfectivoRow) {
						descuentoEfectivoRow.style.display = 'none';
					}
					if (subtotalIntermedioContainer) {
						subtotalIntermedioContainer.style.display = 'none';
					}
				}
				
				// Calcular y mostrar total (subtotal intermedio - descuento efectivo) - con redondeo
				const total = Math.round(subtotalIntermedio - descuentoEfectivo);
				const summaryTotal = document.getElementById('summaryTotal');
				if (summaryTotal) {
					summaryTotal.textContent = '$' + Math.round(total).toLocaleString('es-CO') + ' COP';
				}
			} else {
				// Si no hay fechas, actualizar desde el modal
				const totalElement = document.getElementById('modalTotal');
				if (totalElement) {
					document.getElementById('summaryTotal').textContent = totalElement.textContent;
				}
			}
		}
		
		// Abrir modal de reserva
		function openReservationModal() {
			// Verificar que el modal existe
			const modalElement = document.getElementById('reservationModal');
			if (!modalElement) {
				alert('Error: No se pudo encontrar el formulario de reserva. Por favor recarga la página.');
				return;
			}
			
			// Resetear al primer paso
			currentReservationSection = 1;
			const formSections = document.querySelectorAll('#reservationModal .form-section');
			formSections.forEach((section, index) => {
				section.classList.remove('active');
				section.style.display = index === 0 ? 'block' : 'none';
				if (index === 0) section.classList.add('active');
			});
			
			// Resetear indicador de progreso
			const progressSteps = document.querySelectorAll('#reservationModal .progress-step');
			progressSteps.forEach((step, index) => {
				step.classList.remove('active', 'completed');
				if (index === 0) {
					step.classList.add('active');
				} else {
					step.classList.add('inactive');
				}
			});
			
			// Actualizar fechas en el modal (si existen)
			if (selectedStartDate && selectedEndDate) {
				const checkinEl = document.getElementById('modalCheckinDate');
				const checkoutEl = document.getElementById('modalCheckoutDate');
				if (checkinEl) checkinEl.textContent = selectedStartDate.toLocaleDateString(translations.locale);
				if (checkoutEl) checkoutEl.textContent = selectedEndDate.toLocaleDateString(translations.locale);
			}
			
			// Actualizar resumen de costos
			updateModalCostSummary();
			
			// Mostrar modal - Múltiples métodos de compatibilidad
			// Método 1: Bootstrap 5
			if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
				try {
					const modal = new bootstrap.Modal(modalElement, {
						backdrop: true,
						keyboard: true
					});
					modal.show();
					// También inicializar después de mostrar
					initPlaceholderOnShow();
				} catch (error) {
					// Fallback manual
					modalElement.style.display = 'block';
					modalElement.classList.add('show');
					modalElement.style.zIndex = '1050';
					document.body.classList.add('modal-open');
					const backdrop = document.createElement('div');
					backdrop.className = 'modal-backdrop fade show';
					backdrop.style.zIndex = '1040';
					document.body.appendChild(backdrop);
					
					// Asegurar que el modal esté por encima
					if (modalElement.querySelector('.modal-dialog')) {
						modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
					}
				}
				
				// Asegurar z-index después de que Bootstrap muestre el modal
				setTimeout(() => {
					modalElement.style.zIndex = '1050';
					if (modalElement.querySelector('.modal-dialog')) {
						modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
					}
					// Ajustar backdrop si existe
					const existingBackdrop = document.querySelector('.modal-backdrop');
					if (existingBackdrop) {
						existingBackdrop.style.zIndex = '1040';
					}
				}, 100);
			}
			// Método 2: Bootstrap 4 con jQuery
			else if (typeof $ !== 'undefined' && $.fn.modal) {
				$(modalElement).modal('show');
				
				// Asegurar z-index después de que jQuery muestre el modal
				setTimeout(() => {
					modalElement.style.zIndex = '1050';
					if (modalElement.querySelector('.modal-dialog')) {
						modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
					}
					// Ajustar backdrop si existe
					const existingBackdrop = document.querySelector('.modal-backdrop');
					if (existingBackdrop) {
						existingBackdrop.style.zIndex = '1040';
					}
				}, 100);
			}
			// Método 3: Fallback manual
			else {
				modalElement.style.display = 'block';
				modalElement.classList.add('show');
				document.body.classList.add('modal-open');
				
				// Crear backdrop
				const backdrop = document.createElement('div');
				backdrop.className = 'modal-backdrop fade show';
				backdrop.id = 'modalBackdrop';
				backdrop.style.zIndex = '1040';
				document.body.appendChild(backdrop);
				
				// Asegurar que el modal esté por encima
				modalElement.style.zIndex = '1050';
				if (modalElement.querySelector('.modal-dialog')) {
					modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
				}
			}
		}
		
		// Cerrar modal
		function closeModal() {
			const modalElement = document.getElementById('reservationModal');
			if (!modalElement) return;
			
			// Método 1: Bootstrap 5
			if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
				const modal = bootstrap.Modal.getInstance(modalElement);
				if (modal) {
					modal.hide();
				} else {
					// Si no hay instancia, crear una nueva y cerrarla
					const newModal = new bootstrap.Modal(modalElement);
					newModal.hide();
				}
			}
			// Método 2: Bootstrap 4 con jQuery
			else if (typeof $ !== 'undefined' && $.fn.modal) {
				$(modalElement).modal('hide');
			}
			// Método 3: Fallback manual
			else {
				modalElement.style.display = 'none';
				modalElement.classList.remove('show');
				document.body.classList.remove('modal-open');
				document.body.style.overflow = '';
				document.body.style.paddingRight = '';
				
				// Remover todos los backdrops
				const backdrops = document.querySelectorAll('.modal-backdrop');
				backdrops.forEach(backdrop => backdrop.remove());
			}
		}
		
		// Actualizar resumen de costos en el modal
		function updateModalCostSummary() {
			if (!selectedStartDate || !selectedEndDate) {
				return;
			}
			
			// Calcular noches usando precios reales por día
			let total = 0;
			const currentDate = new Date(selectedStartDate);
			
			while (currentDate < selectedEndDate) {
				const dateString = currentDate.toISOString().split('T')[0];
				const precio = getTarifaPorFecha(dateString);
				total += precio;
				currentDate.setDate(currentDate.getDate() + 1);
			}
			
			const nights = Math.ceil((selectedEndDate.getTime() - selectedStartDate.getTime()) / (1000 * 3600 * 24));
			const subtotal = total;
			
			// Verificar método de pago seleccionado (si existe)
			const metodoPagoRadio = document.querySelector('input[name="metodoPago"]:checked');
			const metodoPago = metodoPagoRadio ? metodoPagoRadio.value : 'tarjeta_credito';
			
			let descuentoEfectivo = 0;
			let descuentoFidelidad = 0;
			let descuentoCumpleanos = 0;
			
			// PASO 1: Descuento por fidelidad (sobre subtotal original) - con redondeo
			<?php if ($user_logged_in && isset($descuentos) && isset($descuentos['fidelidad']) && $descuentos['fidelidad']['activo']): ?>
			descuentoFidelidad = Math.round(subtotal * <?php echo $descuentos['fidelidad']['porcentaje'] / 100; ?>);
			<?php endif; ?>
			
			// PASO 2: Descuento por cumpleaños (sobre subtotal original) - con redondeo
			<?php if ($user_logged_in && isset($user_data) && is_array($user_data) && isset($user_data['fecha_nacimiento']) && !empty($user_data['fecha_nacimiento'])): ?>
			// Usar fecha de nacimiento de la BD del usuario logueado (más segura)
			const fechaNacimientoBD = '<?php echo htmlspecialchars($user_data['fecha_nacimiento'], ENT_QUOTES, 'UTF-8'); ?>';
			if (fechaNacimientoBD) {
				const fechaNac = new Date(fechaNacimientoBD);
				
				// Verificar si el cumpleaños está dentro del rango de fechas de la reserva
				const cumpleanosDia = fechaNac.getDate();
				const cumpleanosMes = fechaNac.getMonth();
				
				// Verificar si el cumpleaños está entre las fechas de entrada y salida
				const fechaEntrada = new Date(selectedStartDate);
				const fechaSalida = new Date(selectedEndDate);
				
				// Crear fecha de cumpleaños para el año de la reserva
				const añoReserva = fechaEntrada.getFullYear();
				const cumpleanosActual = new Date(añoReserva, cumpleanosMes, cumpleanosDia);
				
				// Verificar si el cumpleaños está dentro del rango de la reserva
				if (cumpleanosActual >= fechaEntrada && cumpleanosActual <= fechaSalida) {
					descuentoCumpleanos = Math.round(subtotal * <?php echo (isset($descuentos) && isset($descuentos['cumpleanos']) && $descuentos['cumpleanos']['activo']) ? $descuentos['cumpleanos']['porcentaje'] / 100 : 0.30; ?>); // Descuento por cumpleaños
				}
			}
			<?php endif; ?>
			
			// PASO 3: Crear subtotal intermedio después de fidelidad y cumpleaños - con redondeo
			const subtotalIntermedio = Math.round(subtotal - descuentoFidelidad - descuentoCumpleanos);
			
			// PASO 4: Descuento por efectivo (sobre subtotal intermedio) - con redondeo
			if (metodoPago === 'transferencia') {
				descuentoEfectivo = Math.round(subtotalIntermedio * <?php echo (isset($descuentos) && isset($descuentos['promocional']) && $descuentos['promocional']['activo']) ? $descuentos['promocional']['porcentaje'] / 100 : 0.03; ?>);
			}
			
			// Calcular total con todos los descuentos (subtotal intermedio - descuento efectivo) - con redondeo
			total = Math.round(subtotalIntermedio - descuentoEfectivo);
			
			// Actualizar elementos del modal solo si existen
			const modalNights = document.getElementById('modalNights');
			if (modalNights) modalNights.textContent = nights;
			
			const modalSubtotal = document.getElementById('modalSubtotal');
			if (modalSubtotal) modalSubtotal.textContent = '$' + Math.round(subtotal).toLocaleString('es-CO') + ' COP';
			
			const modalDescuento = document.getElementById('modalDescuento');
			if (modalDescuento) modalDescuento.textContent = '$' + Math.round(descuentoEfectivo).toLocaleString('es-CO') + ' COP';
			
			const modalTotal = document.getElementById('modalTotal');
			if (modalTotal) modalTotal.textContent = '$' + Math.round(total).toLocaleString('es-CO') + ' COP';
			
			// Actualizar summaryTotal si existe (para el paso 5)
			const summaryTotal = document.getElementById('summaryTotal');
			if (summaryTotal) summaryTotal.textContent = '$' + Math.round(total).toLocaleString('es-CO') + ' COP';
			
			// Mostrar/ocultar fila de descuento por efectivo (solo si existe)
			const descuentoRow = document.getElementById('descuentoRow');
			if (descuentoRow) {
				if (metodoPago === 'transferencia' && descuentoEfectivo > 0) {
					descuentoRow.style.display = 'flex';
				} else {
					descuentoRow.style.display = 'none';
				}
			}
			
			// Mostrar descuento por fidelidad si el usuario está logueado (solo si existe)
			<?php if ($user_logged_in): ?>
			const modalFidelidad = document.getElementById('modalFidelidad');
			if (modalFidelidad) {
				modalFidelidad.textContent = '$' + descuentoFidelidad.toLocaleString('es-CO') + ' COP';
			}
			
			// Mostrar/ocultar descuento por cumpleaños (solo si existe)
			<?php if ($user_logged_in): ?>
			const cumpleanosRow = document.getElementById('cumpleanosRow');
			if (cumpleanosRow) {
				if (descuentoCumpleanos > 0) {
					cumpleanosRow.style.display = 'flex';
					const modalCumpleanos = document.getElementById('modalCumpleanos');
					if (modalCumpleanos) {
						modalCumpleanos.textContent = '$' + descuentoCumpleanos.toLocaleString('es-CO') + ' COP';
					}
					
					// Mostrar información sobre el cumpleaños (solo si existe)
					<?php if (isset($user_data) && is_array($user_data) && isset($user_data['fecha_nacimiento']) && !empty($user_data['fecha_nacimiento'])): ?>
					const cumpleanosInfo = document.getElementById('cumpleanosInfo');
					if (cumpleanosInfo) {
						const fechaNacimientoBD = '<?php echo htmlspecialchars($user_data['fecha_nacimiento'], ENT_QUOTES, 'UTF-8'); ?>';
						if (fechaNacimientoBD) {
							const fechaNac = new Date(fechaNacimientoBD);
							const cumpleanosDia = fechaNac.getDate();
							const cumpleanosMes = fechaNac.getMonth();
							const meses = translations.months || ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
							const birthdayMsg = <?php 
								// Suprimir TODOS los errores y warnings usando output buffering
								ob_start();
								$old_error_reporting = error_reporting(0);
								$old_display_errors = ini_get('display_errors');
								ini_set('display_errors', 0);
								
								try {
									$msg = __('form.birthday_in_range');
									ob_end_clean(); // Limpiar cualquier output antes del JSON
									if (is_string($msg) && !empty($msg)) {
										$json = json_encode($msg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
										if ($json !== false) {
											echo $json;
										} else {
											echo json_encode('Tu cumpleaños está dentro del rango de fechas seleccionado', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
										}
									} else {
										echo json_encode('Tu cumpleaños está dentro del rango de fechas seleccionado', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
									}
								} catch (Exception $e) {
									ob_end_clean(); // Limpiar cualquier output en caso de error
									echo json_encode('Tu cumpleaños está dentro del rango de fechas seleccionado', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
								} catch (Throwable $e) {
									ob_end_clean(); // Limpiar cualquier output en caso de error
									echo json_encode('Tu cumpleaños está dentro del rango de fechas seleccionado', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
								} finally {
									error_reporting($old_error_reporting);
									ini_set('display_errors', $old_display_errors);
								}
							?>;
							if (birthdayMsg && typeof birthdayMsg === 'string') {
								cumpleanosInfo.textContent = birthdayMsg.replace('{month}', meses[cumpleanosMes] || (cumpleanosMes + 1)).replace('{day}', cumpleanosDia);
							} else {
								cumpleanosInfo.textContent = 'Tu cumpleaños es el ' + cumpleanosDia + ' de ' + (meses[cumpleanosMes] || (cumpleanosMes + 1));
							}
						}
					}
					<?php endif; ?>
				} else {
					cumpleanosRow.style.display = 'none';
				}
			}
			<?php endif; ?>
			<?php endif; ?>
		}
		
		// Calcular costo base usando precios reales por día
		function calcularCostoBase(fechaInicio, fechaFin) {
			let total = 0;
			const currentDate = new Date(fechaInicio);
			
			while (currentDate < fechaFin) {
				const dateString = currentDate.toISOString().split('T')[0];
				const precio = getTarifaPorFecha(dateString);
				total += precio;
				currentDate.setDate(currentDate.getDate() + 1);
			}
			
			return total;
		}
		
		// Flag para evitar envíos duplicados
		let isSubmittingReservation = false;
		
		// Enviar reserva
		function submitReservation() {
			// Prevenir envíos duplicados
			if (isSubmittingReservation) {
				return;
			}
			
			const form = document.getElementById('reservationForm');
			const formData = new FormData(form);
			
			// Validaciones básicas
			if (!form.checkValidity()) {
				form.reportValidity();
				return;
			}
			
			// Marcar como enviando
			isSubmittingReservation = true;
			
			// Calcular costo base usando precios reales
			const costoBase = calcularCostoBase(selectedStartDate, selectedEndDate);
			const nights = Math.ceil((selectedEndDate.getTime() - selectedStartDate.getTime()) / (1000 * 3600 * 24));
			
			// Recopilar datos
			// Si el usuario está logueado, usar datos de la sesión para campos personales
			const userDataLocked = <?php if ($user_logged_in && isset($user_data) && is_array($user_data)): ?>{
				nombre: '<?php echo isset($user_data['nombre']) ? htmlspecialchars($user_data['nombre'], ENT_QUOTES) : ''; ?>',
				apellido: '<?php echo isset($user_data['apellido']) ? htmlspecialchars($user_data['apellido'], ENT_QUOTES) : ''; ?>',
				correo: '<?php echo isset($user_data['correo']) ? htmlspecialchars($user_data['correo'], ENT_QUOTES) : ''; ?>',
				telefono: '<?php echo isset($user_data['telefono']) ? htmlspecialchars($user_data['telefono'], ENT_QUOTES) : ''; ?>',
				fecha_nacimiento: '<?php echo (isset($user_data['fecha_nacimiento']) && $user_data['fecha_nacimiento']) ? htmlspecialchars($user_data['fecha_nacimiento'], ENT_QUOTES) : ''; ?>'
			}<?php else: ?>null<?php endif; ?>;
			
			// Obtener método de pago del radio button seleccionado
			const metodoPagoRadio = document.querySelector('input[name="metodoPago"]:checked');
			const metodoPagoValue = metodoPagoRadio ? metodoPagoRadio.value : (formData.get('metodoPago') || 'tarjeta_credito');
			
			const reservationData = {
				id_apartamento: 1,
				id_usuario: <?php echo $user_logged_in && isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>,
				nombre: userDataLocked ? userDataLocked.nombre : formData.get('nombres'),
				apellido: userDataLocked ? userDataLocked.apellido : formData.get('apellidos'),
				correo: userDataLocked ? userDataLocked.correo : formData.get('correo'),
				telefono: userDataLocked ? userDataLocked.telefono : (document.getElementById('codigoPais').value + ' ' + formData.get('celular')),
				fecha_nacimiento: userDataLocked ? userDataLocked.fecha_nacimiento : formData.get('fechaNacimiento'),
				fecha_entrada: selectedStartDate.toISOString().split('T')[0],
				fecha_salida: selectedEndDate.toISOString().split('T')[0],
				num_adultos: parseInt(formData.get('adultos')),
				num_ninos: parseInt(formData.get('ninos')),
				vive_palmira: formData.get('vivePalmira') === 'on',
				metodo_pago: metodoPagoValue,
				costo_base: costoBase,
				descuento_fidelizacion: (() => {
					<?php if ($user_logged_in): ?>
					// Calcular descuento por fidelidad como monto en pesos - con redondeo
					return Math.round(costoBase * <?php echo (isset($descuentos) && isset($descuentos['fidelidad']) && $descuentos['fidelidad']['activo']) ? $descuentos['fidelidad']['porcentaje'] / 100 : 0; ?>); // Descuento por fidelidad
					<?php endif; ?>
					return 0;
				})(),
				descuento_cumpleanios: (() => {
					<?php if ($user_logged_in && isset($user_data) && is_array($user_data) && isset($user_data['fecha_nacimiento']) && !empty($user_data['fecha_nacimiento'])): ?>
					// Usar fecha de nacimiento de la BD del usuario logueado (más segura)
					const fechaNacimientoBD = '<?php echo htmlspecialchars($user_data['fecha_nacimiento'], ENT_QUOTES, 'UTF-8'); ?>';
					if (fechaNacimientoBD) {
						const fechaNac = new Date(fechaNacimientoBD);
						
						// Verificar si el cumpleaños está dentro del rango de fechas de la reserva
						const cumpleanosDia = fechaNac.getDate();
						const cumpleanosMes = fechaNac.getMonth();
						
						// Verificar si el cumpleaños está entre las fechas de entrada y salida
						const fechaEntrada = new Date(selectedStartDate);
						const fechaSalida = new Date(selectedEndDate);
						
						// Crear fecha de cumpleaños para el año de la reserva
						const añoReserva = fechaEntrada.getFullYear();
						const cumpleanosActual = new Date(añoReserva, cumpleanosMes, cumpleanosDia);
						
						// Verificar si el cumpleaños está dentro del rango de la reserva
						if (cumpleanosActual >= fechaEntrada && cumpleanosActual <= fechaSalida) {
							// Calcular descuento por cumpleaños como monto en pesos - con redondeo
							return Math.round(costoBase * <?php echo (isset($descuentos) && isset($descuentos['cumpleanos']) && $descuentos['cumpleanos']['activo']) ? $descuentos['cumpleanos']['porcentaje'] / 100 : 0; ?>); // Descuento por cumpleaños
						}
					}
					<?php endif; ?>
					return 0;
				})(),
				descuento_promocional: (() => {
					// PASO 1 y 2: Calcular descuentos de fidelidad y cumpleaños sobre costoBase - con redondeo
					let descuentoFidelidad = 0;
					let descuentoCumpleanos = 0;
					
					<?php if ($user_logged_in): ?>
					descuentoFidelidad = Math.round(costoBase * <?php echo (isset($descuentos) && isset($descuentos['fidelidad']) && $descuentos['fidelidad']['activo']) ? $descuentos['fidelidad']['porcentaje'] / 100 : 0; ?>);
					
					<?php if (isset($user_data) && is_array($user_data) && isset($user_data['fecha_nacimiento']) && !empty($user_data['fecha_nacimiento'])): ?>
					const fechaNacimientoBD = '<?php echo htmlspecialchars($user_data['fecha_nacimiento'], ENT_QUOTES, 'UTF-8'); ?>';
					if (fechaNacimientoBD) {
						const fechaNac = new Date(fechaNacimientoBD);
						const cumpleanosDia = fechaNac.getDate();
						const cumpleanosMes = fechaNac.getMonth();
						const fechaEntrada = new Date(selectedStartDate);
						const fechaSalida = new Date(selectedEndDate);
						const añoReserva = fechaEntrada.getFullYear();
						const cumpleanosActual = new Date(añoReserva, cumpleanosMes, cumpleanosDia);
						
						if (cumpleanosActual >= fechaEntrada && cumpleanosActual <= fechaSalida) {
							descuentoCumpleanos = Math.round(costoBase * <?php echo (isset($descuentos) && isset($descuentos['cumpleanos']) && $descuentos['cumpleanos']['activo']) ? $descuentos['cumpleanos']['porcentaje'] / 100 : 0; ?>);
						}
					}
					<?php endif; ?>
					<?php endif; ?>
					
					// PASO 3: Crear subtotal intermedio - con redondeo
					const subtotalIntermedio = Math.round(costoBase - descuentoFidelidad - descuentoCumpleanos);
					
					// PASO 4: Calcular descuento promocional (efectivo) sobre subtotal intermedio - con redondeo
					if (metodoPagoValue === 'transferencia') {
						return Math.round(subtotalIntermedio * <?php echo (isset($descuentos) && isset($descuentos['promocional']) && $descuentos['promocional']['activo']) ? $descuentos['promocional']['porcentaje'] / 100 : 0.03; ?>);
					}
					return 0;
				})(),
				total: (() => {
					// PASO 1 y 2: Calcular descuentos de fidelidad y cumpleaños sobre costoBase - con redondeo
					let descuentoFidelidad = 0;
					let descuentoCumpleanos = 0;
					
					<?php if ($user_logged_in): ?>
					descuentoFidelidad = Math.round(costoBase * <?php echo (isset($descuentos) && isset($descuentos['fidelidad']) && $descuentos['fidelidad']['activo']) ? $descuentos['fidelidad']['porcentaje'] / 100 : 0; ?>);
					
					<?php if (isset($user_data) && is_array($user_data) && isset($user_data['fecha_nacimiento']) && !empty($user_data['fecha_nacimiento'])): ?>
					const fechaNacimientoBD = '<?php echo htmlspecialchars($user_data['fecha_nacimiento'], ENT_QUOTES, 'UTF-8'); ?>';
					if (fechaNacimientoBD) {
						const fechaNac = new Date(fechaNacimientoBD);
						const cumpleanosDia = fechaNac.getDate();
						const cumpleanosMes = fechaNac.getMonth();
						const fechaEntrada = new Date(selectedStartDate);
						const fechaSalida = new Date(selectedEndDate);
						const añoReserva = fechaEntrada.getFullYear();
						const cumpleanosActual = new Date(añoReserva, cumpleanosMes, cumpleanosDia);
						
						if (cumpleanosActual >= fechaEntrada && cumpleanosActual <= fechaSalida) {
							descuentoCumpleanos = Math.round(costoBase * <?php echo (isset($descuentos) && isset($descuentos['cumpleanos']) && $descuentos['cumpleanos']['activo']) ? $descuentos['cumpleanos']['porcentaje'] / 100 : 0; ?>);
						}
					}
					<?php endif; ?>
					<?php endif; ?>
					
					// PASO 3: Crear subtotal intermedio - con redondeo
					const subtotalIntermedio = Math.round(costoBase - descuentoFidelidad - descuentoCumpleanos);
					
					// PASO 4: Calcular descuento promocional (efectivo) sobre subtotal intermedio - con redondeo
					let descuentoEfectivo = 0;
					if (metodoPagoValue === 'transferencia') {
						descuentoEfectivo = Math.round(subtotalIntermedio * <?php echo (isset($descuentos) && isset($descuentos['promocional']) && $descuentos['promocional']['activo']) ? $descuentos['promocional']['porcentaje'] / 100 : 0.03; ?>);
					}
					
					// Total final = subtotal intermedio - descuento efectivo - con redondeo
					return Math.round(subtotalIntermedio - descuentoEfectivo);
				})()
			};
			
			// Enviar datos al servidor PHP
			fetch('../../app/services/process_reservation.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(reservationData)
			})
			.then(response => response.json())
			.then(data => {
				// Resetear flag de envío
				isSubmittingReservation = false;
				
				if (data.success) {
					alert(translations.messages.success_sent);
					
					// Cerrar modal y limpiar formulario
					closeModal();
					form.reset();
					
					// Limpiar selección del calendario
					clearSelection();
					updateReservationSummary();
				} else {
					alert('ERROR: ' + translations.messages.error_sending + ': ' + data.message);
				}
			})
			.catch(error => {
				// Resetear flag de envío en caso de error
				isSubmittingReservation = false;
				alert('ERROR: ' + translations.messages.error_retry);
			});
		}
		
		// Funciones del perfil de usuario - Movidas al final del archivo para asegurar disponibilidad global
		
		// Inicializar cuando el DOM esté listo
		function initializeCalendar() {
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', function() {
					initCalendar();
				});
			} else {
				// DOM ya está listo
				setTimeout(function() {
					initCalendar();
				}, 100);
			}
		}
		
		// Ejecutar inicialización
		initializeCalendar();
		
		// ==========================================================
		// INTEGRACIÓN CON BOLD - Botón de Pago
		// ==========================================================
		
		// Handler para el botón de pago
		const pagoBtn = document.getElementById('pagoBtn');
		if (pagoBtn) {
			pagoBtn.addEventListener('click', function(e) {
				e.preventDefault();
				
				// Obtener ID de reserva desde la URL o parámetro
				const urlParams = new URLSearchParams(window.location.search);
				const reservaId = urlParams.get('reserva_id') || 
								  urlParams.get('id') || 
								  (window.reservaId ? window.reservaId : null);
				
				if (!reservaId) {
					alert('No se encontró el ID de la reserva. Por favor, contacta con soporte.');
					return;
				}
				
				// Obtener el monto total (puede venir de la URL o calcularse)
				const totalAmount = urlParams.get('total') || 
									(window.reservaTotal ? window.reservaTotal : null);
				
				if (!totalAmount) {
					alert('No se pudo obtener el monto a pagar. Por favor, contacta con soporte.');
					return;
				}
				
				// Deshabilitar botón mientras se procesa
				pagoBtn.disabled = true;
				pagoBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
				
				// Crear link de pago con Bold
				fetch('../../app/api/payment/create_bold_link.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						reserva_id: reservaId,
						total_amount: parseFloat(totalAmount)
					})
				})
				.then(response => response.json())
				.then(data => {
					if (data.success && data.data && data.data.payment_url) {
						// Redirigir a la URL de pago de Bold
						window.location.href = data.data.payment_url;
					} else {
						// Error al crear el link
						alert('Error al generar el link de pago: ' + (data.message || 'Error desconocido'));
						pagoBtn.disabled = false;
						pagoBtn.innerHTML = 'Botón de Pago';
					}
				})
				.catch(error => {
					console.error('Error:', error);
					alert('Error de conexión al generar el link de pago. Por favor, intenta nuevamente.');
					pagoBtn.disabled = false;
					pagoBtn.innerHTML = 'Botón de Pago';
				});
			});
		}
		
		// Manejar mensajes de resultado de pago (desde callback)
		(function() {
			const urlParams = new URLSearchParams(window.location.search);
			if (urlParams.get('payment_success') === '1') {
				alert('¡Pago realizado exitosamente! Tu reserva ha sido confirmada.');
				// Limpiar parámetros de la URL
				window.history.replaceState({}, document.title, window.location.pathname);
			} else if (urlParams.get('payment_error')) {
				const errorType = urlParams.get('payment_error');
				let errorMessage = 'Error al procesar el pago.';
				
				switch(errorType) {
					case 'rejected':
						errorMessage = 'El pago fue rechazado. Por favor, intenta con otro método de pago.';
						break;
					case 'expired':
						errorMessage = 'El link de pago ha expirado. Por favor, solicita un nuevo link.';
						break;
					case 'no_payment_link':
						errorMessage = 'No se encontró información del pago. Por favor, contacta con soporte.';
						break;
					default:
						errorMessage = 'Ocurrió un error al procesar el pago. Por favor, contacta con soporte.';
				}
				
				alert(errorMessage);
				// Limpiar parámetros de la URL
				window.history.replaceState({}, document.title, window.location.pathname);
			}
		})();
		
		// También reinicializar cuando se vuelve a la página (para navegación desde otras páginas)
		window.addEventListener('pageshow', function(event) {
			// Reinicializar siempre que se muestre la página, especialmente si viene de otra página
			const calendar = document.getElementById('calendar');
			if (calendar) {
				initCalendar();
			}
		});
		
		// Reinicializar cuando la página se vuelve visible (útil para navegación entre pestañas/páginas)
		document.addEventListener('visibilitychange', function() {
			if (!document.hidden) {
				const calendar = document.getElementById('calendar');
				if (calendar && calendar.innerHTML === '') {
					// Si el calendario está vacío, reinicializar
					initCalendar();
				}
			}
		});
	</script>

	<?php if ($user_logged_in && $user_role !== 'admin'): ?>
	<!-- Modal de descuento para usuarios logueados -->
	<div id="discountModal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
		<div class="modal-content" style="background-color: #ffffff; margin: 15% auto; padding: 40px; border: none; border-radius: 0; width: 80%; max-width: 600px; text-align: center; box-shadow: none; font-family: 'Oxygen', sans-serif;">
			<h2 style="color: #333; margin-bottom: 20px; font-family: 'Oxygen', sans-serif; font-size: 1.5rem; font-weight: 400;"><?php echo __('discount.welcome_back'); ?></h2>
			<p style="font-size: 1rem; color: #333; margin-bottom: 30px; font-family: 'Oxygen', sans-serif; font-weight: 400;">
				<?php echo __('discount.thank_you'); ?> <strong style="font-weight: 600;"><?php echo htmlspecialchars($user_name); ?></strong>.
			</p>
			<div style="background: #ffffff; color: #333; padding: 30px 20px; border: none; border-bottom: 2px solid #333; border-radius: 0; margin: 30px 0;">
				<h3 style="margin: 0; font-size: 1.75rem; font-family: 'Oxygen', sans-serif; font-weight: 400; color: #333; border-bottom: 1px solid #e1e5e9; padding-bottom: 15px; margin-bottom: 15px;"><?php echo isset($descuentos['fidelidad']) ? $descuentos['fidelidad']['porcentaje'] : 5; ?>% <?php echo __('discount.discount_title'); ?></h3>
				<p style="margin: 15px 0 0 0; font-size: 1rem; font-family: 'Oxygen', sans-serif; font-weight: 400; color: #333;"><?php echo __('discount.on_next_reservation'); ?></p>
			</div>
			<p style="color: #333; font-size: 0.875rem; margin-bottom: 30px; font-family: 'Oxygen', sans-serif; font-weight: 400;">
				<?php echo __('discount.auto_applied'); ?>
			</p>
			<button onclick="closeDiscountModal()" style="background-color: #FFE082; color: #333; border: none; padding: 15px 40px; border-radius: 50px; font-size: 18px; font-family: 'Oxygen', sans-serif; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);">
				<?php echo __('discount.great_thanks'); ?>
			</button>
		</div>
	</div>

	<style>
		/* Estilos del modal de descuento - Aplicando estilos del calendario */
		#discountModal .modal-content button:hover {
			background-color: #FFD54F !important;
			color: #333 !important;
			box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4) !important;
			transform: translateY(-2px) !important;
		}
		#discountModal .modal-content button:active {
			background-color: #FFC107 !important;
			transform: translateY(0) !important;
		}
	</style>
	
	<script>
		// Mostrar modal de descuento después de 2 segundos
		setTimeout(function() {
			document.getElementById('discountModal').style.display = 'block';
		}, 2000);

		function closeDiscountModal() {
			document.getElementById('discountModal').style.display = 'none';
		}

		// Cerrar modal al hacer clic fuera de él
		window.onclick = function(event) {
			const modal = document.getElementById('discountModal');
			if (event.target == modal) {
				modal.style.display = 'none';
			}
		}
	</script>
	<?php endif; ?>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Función para abrir el modal de login
// Función eliminada - ahora se usa login.php

// Funciones del modal eliminadas - ahora se usa login.php

// Funciones del perfil - Asegurar que estén disponibles globalmente
// Función para hacer scroll al calendario
function scrollToCalendar() {
	const calendarContainer = document.querySelector('.calendar-container');
	if (calendarContainer) {
		calendarContainer.scrollIntoView({ 
			behavior: 'smooth', 
			block: 'start' 
		});
		// Pequeño delay para asegurar que el scroll se complete
		setTimeout(() => {
			calendarContainer.style.scrollMarginTop = '20px';
		}, 100);
	}
}

// Funciones para manejar el dropdown del perfil
function toggleProfileDropdown() {
	const dropdownMenu = document.getElementById('profileDropdownMenu');
	if (dropdownMenu) {
		if (dropdownMenu.style.display === 'none' || dropdownMenu.style.display === '') {
			dropdownMenu.style.display = 'block';
		} else {
			dropdownMenu.style.display = 'none';
		}
	}
}

function closeProfileDropdown() {
	const dropdownMenu = document.getElementById('profileDropdownMenu');
	if (dropdownMenu) {
		dropdownMenu.style.display = 'none';
	}
}

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function(event) {
	const dropdown = document.getElementById('profileDropdown');
	const dropdownMenu = document.getElementById('profileDropdownMenu');
	if (dropdown && dropdownMenu && !dropdown.contains(event.target) && !dropdownMenu.contains(event.target)) {
		closeProfileDropdown();
	}
});

// Manejar clic en el toggle del dropdown
document.addEventListener('DOMContentLoaded', function() {
	const profileDropdown = document.getElementById('profileDropdown');
	if (profileDropdown) {
		profileDropdown.addEventListener('click', function(e) {
			e.preventDefault();
			toggleProfileDropdown();
		});
	}
});

window.showProfileInfo = function() {
	<?php if ($user_logged_in && $user_data): ?>
	try {
		const userInfo = {
			nombre: '<?php echo (isset($user_data) && isset($user_data['nombre'])) ? htmlspecialchars($user_data['nombre']) : ''; ?>',
			apellido: '<?php echo (isset($user_data) && isset($user_data['apellido'])) ? htmlspecialchars($user_data['apellido']) : ''; ?>',
			correo: '<?php echo (isset($user_data) && isset($user_data['correo'])) ? htmlspecialchars($user_data['correo']) : ''; ?>',
			telefono: '<?php echo (isset($user_data) && isset($user_data['telefono'])) ? htmlspecialchars($user_data['telefono']) : ''; ?>',
			fechaNacimiento: '<?php echo (isset($user_data) && isset($user_data['fecha_nacimiento']) && $user_data['fecha_nacimiento']) ? date('d/m/Y', strtotime($user_data['fecha_nacimiento'])) : __('profile.not_registered'); ?>'
		};
		
		// Llenar el modal con los datos
		document.getElementById('profileName').textContent = userInfo.nombre + ' ' + userInfo.apellido;
		document.getElementById('profileEmail').textContent = userInfo.correo;
		document.getElementById('profilePhone').textContent = userInfo.telefono || translations.profile.not_registered;
		document.getElementById('profileBirthday').textContent = userInfo.fechaNacimiento;
		
		// Mostrar el modal
		const modalElement = document.getElementById('profileModal');
		if (modalElement) {
			// Usar Bootstrap modal si está disponible
			if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
				const profileModal = new bootstrap.Modal(modalElement);
				profileModal.show();
			} else if (typeof $ !== 'undefined' && $.fn.modal) {
				$(modalElement).modal('show');
			} else {
				// Fallback manual
				modalElement.style.display = 'block';
				modalElement.classList.add('show');
				modalElement.style.position = 'fixed';
				modalElement.style.top = '0';
				modalElement.style.left = '0';
				modalElement.style.width = '100%';
				modalElement.style.height = '100%';
				modalElement.style.zIndex = '10001';
				document.body.classList.add('modal-open');
				document.body.style.overflow = 'hidden';
				
				// Crear backdrop
				const backdrop = document.createElement('div');
				backdrop.className = 'modal-backdrop fade show';
				backdrop.style.zIndex = '1040';
				document.body.appendChild(backdrop);
				
				// Asegurar que el modal esté por encima
				modalElement.style.zIndex = '1050';
				if (modalElement.querySelector('.modal-dialog')) {
					modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
				}
			}
		}
	} catch (error) {
		alert('Error al cargar la información del perfil');
	}
	<?php else: ?>
	alert('Debes estar logueado para ver tu perfil');
	<?php endif; ?>
};

window.showMyReservations = function() {
	try {
		// Mostrar loading
		const loadingEl = document.getElementById('loadingReservations');
		const listEl = document.getElementById('reservationsList');
		const noResEl = document.getElementById('noReservations');
		
		if (loadingEl) loadingEl.style.display = 'block';
		if (listEl) listEl.style.display = 'none';
		if (noResEl) noResEl.style.display = 'none';
		
		// Mostrar el modal
		const modalElement = document.getElementById('reservationsModal');
		if (modalElement) {
			// Usar Bootstrap modal si está disponible
			if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
				const reservationsModal = new bootstrap.Modal(modalElement);
				reservationsModal.show();
			} else if (typeof $ !== 'undefined' && $.fn.modal) {
				$(modalElement).modal('show');
			} else {
				// Fallback manual
				modalElement.style.display = 'block';
				modalElement.classList.add('show');
				modalElement.style.position = 'fixed';
				modalElement.style.top = '0';
				modalElement.style.left = '0';
				modalElement.style.width = '100%';
				modalElement.style.height = '100%';
				modalElement.style.zIndex = '10001';
				document.body.classList.add('modal-open');
				document.body.style.overflow = 'hidden';
				
				// Crear backdrop
				const backdrop = document.createElement('div');
				backdrop.className = 'modal-backdrop fade show';
				backdrop.style.zIndex = '1040';
				document.body.appendChild(backdrop);
				
				// Asegurar que el modal esté por encima
				modalElement.style.zIndex = '1050';
				if (modalElement.querySelector('.modal-dialog')) {
					modalElement.querySelector('.modal-dialog').style.zIndex = '1051';
				}
			}
		}
		
		// Obtener reservas del usuario
		fetch('/app/api/user/get_my_reservations.php')
			.then(response => response.json())
			.then(data => {
				document.getElementById('loadingReservations').style.display = 'none';
				
				if (data.success && data.reservations && data.reservations.length > 0) {
					let html = '';
					data.reservations.forEach((reservation, index) => {
						const statusClass = reservation.estado === 'aprobada' ? 'success' : 
											reservation.estado === 'pendiente' ? 'warning' : 
											reservation.estado === 'cancelada' ? 'danger' : 'info';
						const statusText = reservation.estado === 'aprobada' ? translations.reservations.approved :
											reservation.estado === 'pendiente' ? translations.reservations.pending :
											reservation.estado === 'cancelada' ? translations.reservations.cancelled :
											translations.reservations.confirmed;
						const paymentStatus = reservation.estado_pago === 'pagada' ? translations.reservations.paid : translations.reservations.unpaid;
						const paymentClass = reservation.estado_pago === 'pagada' ? 'success' : 'warning';
						const paymentMethod = (reservation.metodo_pago === 'efectivo' || reservation.metodo_pago === 'transferencia') ? 'Transferencia' : translations.reservations.card;
						
						html += `
							<div class="card mb-3" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
								<div class="card-body">
									<div class="d-flex justify-content-between align-items-start mb-3">
										<div>
											<h6 class="mb-1" style="color: #1e3c72; font-weight: bold;">
												📅 Reserva #${reservation.id_reserva}
											</h6>
											<small class="text-muted">${translations.reservations.created}: ${reservation.creado_en_formatted}</small>
										</div>
										<span class="badge bg-${statusClass}">${statusText}</span>
									</div>
									<hr>
									<div class="row">
										<div class="col-md-6 mb-2">
												<small class="text-muted">📥 ${translations.reservations.checkin}:</small>
											<div style="font-weight: 600;">${reservation.fecha_entrada_formatted}</div>
										</div>
										<div class="col-md-6 mb-2">
												<small class="text-muted">📤 ${translations.reservations.checkout}:</small>
											<div style="font-weight: 600;">${reservation.fecha_salida_formatted}</div>
										</div>
										<div class="col-md-6 mb-2">
												<small class="text-muted">🌙 ${translations.reservations.nights}:</small>
											<div style="font-weight: 600;">${reservation.noches} ${reservation.noches === 1 ? 'noche' : 'noches'}</div>
										</div>
										<div class="col-md-6 mb-2">
												<small class="text-muted">👥 ${translations.reservations.adults}: ${reservation.numero_adultos} | ${translations.reservations.children}: ${reservation.numero_ninos || 0}</small>
										</div>
									</div>
									<hr>
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<small class="text-muted">${translations.reservations.payment_status}:</small>
											<span class="badge bg-${paymentClass} ms-2">${paymentStatus}</span>
											<br>
											<small class="text-muted">${translations.reservations.payment_method}: ${paymentMethod}</small>
										</div>
										<div class="text-end">
											<small class="text-muted d-block">${translations.reservations.total}:</small>
											<h5 class="mb-0" style="color: #1e3c72; font-weight: bold;">${formatearPrecioCOP(reservation.total)}</h5>
										</div>
									</div>
								</div>
							</div>
						`;
					});
					document.getElementById('reservationsList').innerHTML = html;
					document.getElementById('reservationsList').style.display = 'block';
				} else {
					document.getElementById('noReservations').style.display = 'block';
				}
			})
			.catch(error => {
				document.getElementById('loadingReservations').style.display = 'none';
				document.getElementById('noReservations').style.display = 'block';
			});
	} catch (error) {
		alert('Error al cargar las reservas');
	}
};

// Script para agregar clase 'scrolled' al header cuando se hace scroll
function updateHeaderScroll() {
	const header = document.querySelector('header');
	if (header) {
		if (window.scrollY > 50) {
			header.classList.add('scrolled');
		} else {
			header.classList.remove('scrolled');
		}
	}
}

// Verificar estado inicial
window.addEventListener('load', function() {
	updateHeaderScroll();
});

// Actualizar cuando se hace scroll
window.addEventListener('scroll', updateHeaderScroll);

// Animación de scroll para el texto del carrusel
function revealOnScroll() {
	const textoElement = document.getElementById('texto-carrusel');
	if (textoElement) {
		const elementTop = textoElement.getBoundingClientRect().top;
		const elementVisible = 150;
		
		if (elementTop < window.innerHeight - elementVisible) {
			textoElement.classList.add('revealed');
		}
	}
	
	const ctaElement = document.getElementById('texto-cta');
	if (ctaElement) {
		const elementTop = ctaElement.getBoundingClientRect().top;
		const elementVisible = 150;
		
		if (elementTop < window.innerHeight - elementVisible) {
			ctaElement.classList.add('revealed');
		}
	}
	
	const calendarioElement = document.getElementById('calendario-container');
	if (calendarioElement) {
		const elementTop = calendarioElement.getBoundingClientRect().top;
		const elementVisible = 150;
		
		if (elementTop < window.innerHeight - elementVisible) {
			calendarioElement.classList.add('revealed');
		}
	}
	
	const resumenElement = document.getElementById('resumen-reserva');
	if (resumenElement) {
		const elementTop = resumenElement.getBoundingClientRect().top;
		const elementVisible = 150;
		
		if (elementTop < window.innerHeight - elementVisible) {
			resumenElement.classList.add('revealed');
		}
	}
}

// Verificar al cargar y al hacer scroll
window.addEventListener('load', revealOnScroll);
window.addEventListener('scroll', revealOnScroll);
</script>


</body>

</html>
</html>