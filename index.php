<?php
if (isset($_COOKIE['sid'])) {
	session_id($_COOKIE['sid']);
}
session_start();
global $components;

// server infok hozzáférhetővé tétele a php számára
define('DOCROOT',__DIR__);
$w1 = (int) str_replace('M', '', ini_get('post_max_size'));
$w2 = (int) str_replace('M','',ini_get('upload_max_filesize'));
define('UPLOADLIMIT',min($w1,$w2));
include_once 'config.php';
include_once 'vendor/database/db.php';
include_once('vendor/model.php');
include_once('vendor/view.php');
include_once('vendor/controller.php');
include_once('vendor/fw.php');
include_once('includes/models/statisticmodel.php');

importComponent('upgrade');

// statisztikai adatgyüjtés
// $statisticModel = new StatisticModel();
// $statisticModel->saveStatistic();

$fw = new Fw();

//+ ----------- verzio kezelés start ------------
$fileVerzio = 'v2.0.0';
$upgrade = new \Upgrade();
$dbverzio  = $upgrade->getDBVersion();
$lastVerzio = $upgrade->getLastVersion();
$upgrade->dbUpgrade($dbverzio);
$branch = $upgrade->branch;
//- ----------- verzio kezelés end ------------

// képernyő méretek elérése
if (isset($_COOKIE['screen_width'])) {
	$_SESSION['screen_width'] = $_COOKIE['screen_width'];
} else {
	$_SESSION['screen_width'] = 1024;
}
if (isset($_COOKIE['screen_height'])) {
	$_SESSION['screen_height'] = $_COOKIE['screen_height'];
} else {
	$_SESSION['screen_height'] = 800;
}

$task = $fw->task;
$comp = $fw->comp;
$title = SITETITLE;
if (method_exists($comp, 'getTitle')) {
	$title = $comp->getTitle($task);
} 

// execute API backends
if (in_array($fw->compName.'.'.$fw->task,
    ['apaTask'])) {
	$comp->$task ();
    exit();
}

?>
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
	<!-- vue -->
    <script src="vendor/vue/vue.global.js"></script>
	<!-- axios -->
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
	<!-- fontawesome --> 
	<script src="vendor/fontawesome/js/all.min.js"></script>
	<link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">

	<link rel="stylesheet" href="admin.css?t=<?php echo $fileVerzio; ?>">
	<link rel="stylesheet" href="style.css?t=<?php echo $fileVerzio; ?>">
	<!-- multi language -->
	<?php
		if (defined('LNG')) {
			if (file_exists(__DIR__.'/languages/'.LNG.'.js')) {
				echo '<script src="languages/'.LNG.'.js"></script>';
			} else {
				echo '<script> tokens = {}; </script>';
			}	
		} else {
			if (file_exists(__DIR__.'/languages/hu.js')) {
				echo '<script src="languages/hu.js"></script>';
			} else {
				echo '<script> tokens = {}; </script>';
			}	
		}
	?>
	<script type="text/javascript">
		var rewrite = <?php echo (int)REWRITE; ?>;
        var siteurl = "<?php echo SITEURL; ?>"; 
	</script>	
	<script src="index.js"></script>
</head>	 
<body>
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
						'isAdmin' => isAdmin(),
						'lastVerzio' => Upgrade::versionAdjust($lastVerzio),
						'fileVerzio' => Upgrade::versionAdjust($fileVerzio)
						],'mainmenu'); 
				?>
			</div>
		</div>
		
		<div class="page">
			<?php
				$comp->$task ();			
			?>
		</div>

		<?php 
			view('footer',[
				'fileVerzio' => Upgrade::versionAdjust($fileVerzio)
			],'footer'); 
		?>
	</div>
	<button onclick="window.scrollTo(0,0);" id="scrolltotop" title="Fel a tetejére">
		<em class="fa fa-arrow-up"></em>
	</button>
	<script>
		if (document.cookie.search('cookieEnabled=2') >= 0) {
			document.write('<p id="cookieEnabled">Csoki kezelés engedélyezve van. Letiltásához kattints ide:'+
			'<a href="index.php" onclick="setCookie(\'cookieEnabled\',0,100);">Letilt</a></p>');
		} else if (document.location.href.search('adatkezeles') < 0) {
			popupConfirm('Ennek a web oldalnak a használatához un. "munkamenet csokik" használtata szükséges.'+
			'<br />Lásd: <a href="index.php?task=adatkezeles">Adatkezelési leírás</a>'+
			'<br />Kérjük engedélyezd a csokik kezelését!',
			function() {
				setCookie('cookieEnabled',2,100);
				document.location='index.php';
			})
		}
	</script>	
</body>
<script type="text/javascript">

		// világos/sötét téma váltás
		function themeTogle() {
			const currentTheme = getCookie("theme");
			var theme = getCookie("theme");
			if (currentTheme == "dark") {
				document.body.className = 'light';
				theme = 'light';
			} else if (currentTheme == "light") {
				document.body.className = 'dark';
				theme = 'dark';
			} else {
				document.body.className = 'dark';
				theme = 'dark';
			}
			setCookie("theme", theme,100);
		}

		// mozgatható elemek
		dragElement(document.getElementById("popup"));

		// sessionId csokiba
		window.sessionId = "<?php echo session_id(); ?>";
		setCookie("sid","<?php echo session_id(); ?>", 500);

		// képek realtime betöltése, scrolltotop button megjelenítés/elrejtés
		window.onscroll = function() {
			window.scrollFunction(); window.scrollFunction()
			if (window.scrollY < 20) {
				document.getElementById('scrolltotop').style.display = 'none';
			} else {
				document.getElementById('scrolltotop').style.display = 'block';
			}
		};
		window.setTimeout('window.scrollFunction()',1000);

		window.rewrite = <?php echo (int)REWRITE; ?>;
        window.siteurl = "<?php echo SITEURL; ?>"; 
		
		// iframe elemek átméretezése a parent div mérethez
		var frames = document.getElementsByTagName("iframe");
		var sz = 0, max = 0;
		for (var i = 0; i < frames.length; i++) {
			max = frames[i].parentNode.getBoundingClientRect().width * 0.9;
			if (frames[i].width > max) {
				sz = max / frames[i].width;
				frames[i].width = Math.round(max);
				frames[i].height = Math.round(frames[i].height * sz);
			}
		}
		
		
		
</script>
</html>
