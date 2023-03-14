<html lang="en">
<head>
  <meta>
    <meta charset="UTF-8">
	<meta property="og:title"  content="<?php echo $title; ?>" />
	<base href="<?php echo SITEURL; ?>/">
	<link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- bootstrap -->	
	<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
	<!-- jquery -->
	<script src="vendor/jquery/jquery-2.2.4.min.js"></script>
	<!-- vue -->
    <script src="vendor/vue/vue.global.js"></script>
	<!-- axios -->
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
	<!-- fontawesome --> 
	<script src="vendor/fontawesome/js/all.min.js"></script>
	<link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">

	<link rel="stylesheet" href="<?php echo SITEURL; ?>/styles/default/style.css?t=<?php echo $fileVerzio.date('Ymdhis'); ?>"
	
	<!-- multi language -->
	<?php
		if (defined('LNG')) {
			if (file_exists('languages/'.LNG.'.js')) {
				echo '<script src="languages/'.LNG.'.js"></script>';
			} else {
				echo '<script> tokens = {}; </script>';
			}	
		} else {
			if (file_exists('languages/hu.js')) {
				echo '<script src="languages/hu.js"></script>';
			} else {
				echo '<script> tokens = {}; </script>';
			}	
		}
	?>
	
	<!-- REWRITE és SITEURL -->
	<script type="text/javascript">
		var rewrite = <?php echo (int)REWRITE; ?>;
        var siteurl = "<?php echo SITEURL; ?>"; 
	</script>	

	<!-- ckeditor -->
	<?php if (count($comp->ckeditorFields) > 0) : ?>
		<script src="vendor/ckeditor/build/ckeditor.js"></script>
		<script src="vendor/ckeditor/myckeditor.js"></script>
	<?php endif; ?>	

	<!-- utopszkij_fw standart js -->
	<script src="index.js"></script>
</head>	 
<body>
	<div id="fixBg"></div>
	<div id="popup">
		<div style="text-align:right">
			<button type="button" onclick="popupClose()" 
				title="Bezár" style="margin:0px 0px 0px 0px; padding:0px 5px 0px 5px"
				class="btn btn-secondary">X</button>
		</div>
		<div id="popupTxt"></div>
		<div>
		<button type="button" id="popupOkBtn" class="btn btn-danger">Igen</button>
			&nbsp;
			<button type="button" id="popupNoBtn"class="btn btn-primary" onclick="popupClose()">Nem</button>
		</div>
	</div>

	<?php
	// extra html -ek betöltése (pl extra js -ek belodolása)
	if (file_exists(__DIR__.'/includes/extras/'.$task.'.html')) {
		include __DIR__.'/includes/extras/'.$task.'.html';
	}
	?>

	<div class="container" id="container">
		<div class="row">
			<div class="col-12">
				<div id="header" onclick="document.location='index.php';"></div>
			</div> 
		</div>
		<div class="row">
			<div class="col-12">
				<?php 
					if (($_SESSION['loged'] > 0) & ($_SESSION['logedAvatar'] == '')) {
						$_SESSION['logedAvatar'] = 'noavatar.png';
					}
					view('mainmenu',[
						'MULTIUSER' => MULTIUSER,
						'loged' => $_SESSION['loged'],
						'logedAvatar' => $_SESSION['logedAvatar'],
						'logedName' => $_SESSION['logedName'],
						'logedGroup' => $_SESSION['logedGroup'],
						'isAdmin' => isAdmin(),
						'lastVerzio' => Upgrade::versionAdjust($lastVerzio),
						'fileVerzio' => Upgrade::versionAdjust($fileVerzio)
						],'mainmenu'); 
				?>
			</div>
		</div>
		
		<div class="page" id="page_<?php echo $compName.'_'.$task; ?>">
			<?php
				$comp->$task ();			
			?>
		</div>

		<?php 
			view('footer',[
				'fileVerzio' => Upgrade::versionAdjust($fileVerzio)
			],'footer'); 
		?>
		<div class="row themeTogle text-center">
			<div class="col-12">
				<button type="button" class="btn btn-secondary" onclick="themeTogle()" style="width:auto">
					<em class="fas fa-adjust"></em>Világos/sötét téma váltás</button>
					<!-- cookie engedélyeztetés -->
					<script type="text/javascript">
						if (document.cookie.search('cookieEnabled=2') >= 0) {
							document.write('<p id="cookieEnabled">Süti kezelés engedélyezve van. Letiltásához kattints ide:'+
							'<a href="index.php" onclick="setCookie(\'cookieEnabled\',0,100);">Letilt</a></p>');
						} else if (document.location.href.search('adatkezeles') < 0) {
							popupConfirm('Ennek a web oldalnak a használatához un. "munkamenet sütik" használtata szükséges.'+
							'<br />Lásd: <a href="index.php?task=adatkezeles">Adatkezelési leírás</a>'+
							'<br />Kérjük engedélyezd a sütik kezelését!',
							function() {
								setCookie('cookieEnabled',2,100);
								document.location='index.php';
							})
						}
					</script>	
			</div>
		</div>
	</div>
	<button onclick="window.scrollTo(0,0);" id="scrolltotop" title="Fel a tetejére">
		<em class="fa fa-arrow-up"></em>
	</button>

</body>
<script type="text/javascript">

		// sessionId csokiba
		window.sessionId = "<?php echo session_id(); ?>";
		setCookie("sid","<?php echo session_id(); ?>", 500);
		
	
</script>
</html>
