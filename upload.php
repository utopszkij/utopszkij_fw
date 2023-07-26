<?php

// file upload server (a ckeditor is ezt használja)
// $_POST: 
//   fileNamePrefix(elhagyható)
//   uploadDir (elhagyható, relativ könyvtár utvonal "/" ne legyen a végén és az elején)
//   extensions (elhagyható, megengedett kiterjesztések json array) 
//   valamint egy tetszőleges nevű nevü "file"
// a max.file méretet és upload timelimit-et a php.ini határozza meg, .htaccess -ben felülbírálható:
//   php_value upload_max_filesize 50M
//   php_value post_max_size 55M
//   php_value max_input_time 3000
//   php_value max_execution_time 3000
// result: {"url":"relativ_file_url"} vagy {"error":"hibaüzenet"}

	function remove_accent($str) {
		// ékezetes betük helyettesítése az ékezet nélkülivel
		$a=array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
		$b=array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
		return str_replace($a,$b,$str);
	}
	function clearFileName($s) {
		// string érvényes filenévvé alakítása
		return preg_replace("/[^a-z0-9._-]/", '', strtolower(remove_accent($s)));
	}
	
   
  if (isset($_POST['fileNamePrefix'])) {
	  $fileNamePrefix = $_POST['fileNamePrefix'];
  } else {
	  $fileNamePrefix = '';
  }
  if (isset($_POST['uploadDir'])) {
	  $uploadDir = $_POST['uploadDir'];
  } else {
	  $uploadDir = 'images/uploads';
  }
  if (isset($_POST['extensions'])) {
	  $extensions = JSON_decode($_POST['extensions']);
  } else {
	  $extensions = Array('jpg','jpeg','png','gif','tif');
  }
  $uploadUrl = $uploadDir.'/'.$fileNamePrefix; 
  $uploadDir = __DIR__.'/'.$uploadDir;
  if (!is_dir($uploadDir)) {
		mkdir($uploadDir,0755);
  }
  $uploadDir .= '/'.$fileNamePrefix;
  foreach ($_FILES as $fn => $fv) {
		$uploadFile = $uploadDir . clearFileName(basename($_FILES[$fn]['name']));
		$uploadFileExt = pathinfo($uploadFile,PATHINFO_EXTENSION);
		if (!in_array($uploadFileExt, $extensions)) {
			echo JSON_encode(array('error'=>'upload_not_enabled'));
			exit();	
		}
		if (file_exists($uploadFile)) {
			unlink($uploadFile);
		}
		if (move_uploaded_file($_FILES[$fn]['tmp_name'], $uploadFile)) {
			$url = $uploadUrl.clearFileName(basename($_FILES[$fn]['name']));
			echo JSON_encode(array('url'=>$url));
			exit();
		} else {
			echo JSON_encode(array('error'=>'error_in_upload'));
			exit();
		}
	}
	echo JSON_encode(array('error'=>'not_uploaded_file'));
	exit();
?>
