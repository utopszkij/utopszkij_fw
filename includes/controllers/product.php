<?php

include_once 'vendor/controller.php';
include_once 'includes/models/productmodel.php';
include_once 'includes/models/tagmodel.php';
class Product extends Controller {
	
   function __construct() {
        $this->request = new Request();
        $this->session = new Session();
        $this->loged = $this->session->input('loged',0,INTEGER);
        $this->logedName = $this->session->input('logedName','Látogató');
        $this->logedAdmin = $this->session->input('admin',false);
        $this->logedGroup = $this->session->input('logedGroup','');
        $this->logedAvatar = $this->session->input('logedGroup','');
        $this->model = new ProductModel();
        $this->name = 'product';
        $this->browserURL = SITEURL.'/task/product.list';
        $this->formURL = SITEURL.'/task/product.show';
        $this->browserTask = 'product.list';
        $this->uploadDir = __DIR__.'/../../img/'.PRODUCTS;

    }
    
	/**
     * loged user hozzáférés ellenörzése
     * @param string $action  'new'|'edit'|'delete'|'show'
     * @param RecordObject $record
     * @return bool
     */    
    protected function accessRight(string $action, $record): bool {
		$result = true;
		if (($action == 'add') | ($action == 'edit') | ($action == 'delete')) {
			if (!$this->session->input('admin')) {
				$result = false;
			}
		}
        return $result;
    }

    /**
     * rekord ellenörzés (update vagy insert előtt)
     * @param RecordObject $record
     * @return string üres ha minden OK, egyébként hibaüzenet
     */    
    protected function validator($record): string {
		$result = '';
		if ($record->desc == '') {
			$result = 'leírás nem lehet üres ';
		}
		if ($record->date == '') {
			$result .= 'a "készült" adat nem lehet üres ';
		}
		if ($record->price == '') {
			$result .= 'ár nem lehet üres ';
		}
		if (($record->favorit != 'I') & ($record->favorit != 'N')) {
			$result .= '"kedvenc" adat nincs beállítva ';
		}
		if ($record->tag == '?') {
			$result .= '"kategória" adat nincs beállítva ';
		}
        return $result;
    }    

	public function show() {
		$model = new ProductModel();
		$products =  $model->getBy('img',$this->request->input('id',''));
		
		if (count($products) > 0) {
			view('product',['admin' => $_SESSION['admin'],
							'product' => $products[0],
							'productsName' => PRODUCTS,
							'siteurl' => SITEURL]);
		} else {
			echo '<div class="alert alert-danger">Nincs ilyen</div>';
		}			
	}
	
	public function list() {
		$this->request->set('filter', $this->request->input('tag','all'));	
		$this->items();
	}
	
	/**
	 * editor képernyő
	 * ez fut akkor is ha felvitel közben a validator hibát talált
	 * @GET id, error
	 * @session product
	 * @return void
	 */ 
	public function edit() {
		$model = new ProductModel();
		$error = urldecode($this->request->input('error'));
		if ($error != '') {
			echo '<div class="alert alert-danger">'.$error.'</div>';
			$products = [$this->session->input('product')];
		} else {
			$products =  $model->getBy('img',$this->request->input('id',''));
		}	
		$tagModel = new TagModel();
		$tags = $tagModel->getItems(0,0,'','');
		if (count($products) > 0) {
			if ($this->accessRight('edit',$products[0])) {
					view('productform',['admin' => $_SESSION['admin'],
									'product' => $products[0],
									'productsName' => PRODUCTS,
									'tags' => $tags,
									'siteurl' => SITEURL]);
			} else {
				echo 'Access violation'; exit();
			}
		} else {
			echo '<div class="alert alert-danger">Nincs ilyen</div>';
		}			
	}
	
	public function add() {
		$product = new \stdClass();
		$product->img = '';
		$product->desc = '';
		$product->tag = '?';
		$product->favorit = '?';
		$product->date = '';
		$product->price = '';
		$tagModel = new TagModel();
		$tags = $tagModel->getItems(0,0,'','');
		if ($this->accessRight('add',$product)) {
			view('productform',['admin' => $_SESSION['admin'],
							'product' => $product,
							'productsName' => PRODUCTS,
							'tags' => $tags,
							'siteurl' => SITEURL]);
		} else {
			echo 'Access violation'; exit();
		}
	}
	
	public function productsave() {
		$img = $this->request->input('img');
		$recs = $this->model->getBy('img',$img);
		if (count($recs) > 0) {
			$rec = $recs[0];
		} else {
			$rec = new \RATWEB\DB\Record();
			$rec->img = $img;
		}
		$rec->desc = $_POST['desc'];
		$rec->price = $this->request->input('price');
		$rec->tag = $this->request->input('tag');
		$rec->date = $this->request->input('date');
		$rec->favorit = $this->request->input('favorit');
		$this->session->set('product',$rec);
		$q = new \RATWEB\DB\Query(PRODUCTS);
		if ($this->accessRight('edit',$rec)) {	
			$error = $this->validator($rec);
			if ($error == '') {
				if (count($recs) > 0) {
					$q->where('img','=',$img)->update($rec);
					$this->session->delete('product',$rec);
					echo '<script>location="'.SITEURL.'/task/product.show/id/'.$img.'";</script>';
				} else {
				  $uploadDir = $this->uploadDir.'/';
				  $uploadFile = $uploadDir . $this->clearFileName(basename($_FILES['newimg']['name']));
				  $uploadFileExt = pathinfo($uploadFile,PATHINFO_EXTENSION);
				  $rec->img = $this->clearFileName(basename($_FILES['newimg']['name']));
				  if (!in_array($uploadFileExt, Array('jpg','jpeg','png','gif'))) {
							echo JSON_encode(array('error'=>'upload not enabled'));
							exit();	
				  }
				  $i=0;
				  while (file_exists($uploadFile)) {
					$i++;
					$uploadDir = $this->uploadDir.'/';
					$uploadFile = $uploadDir.$i.$this->clearFileName(basename($_FILES['newimg']['name']));
					$rec->img = $i.$this->clearFileName(basename($_FILES['newimg']['name']));
				  }
				  $img = $rec->img;
				  if (move_uploaded_file($_FILES['newimg']['tmp_name'], $uploadFile)) {
					$q->insert($rec);
					$this->session->delete('product',$rec);
					echo '<script>location="'.SITEURL.'/task/product.show/id/'.$img.'";</script>';
				  } else {
					echo '<div class="aler alert-danger">Hiba a képfájl feltötlése közben</div>';
				  }	
				}
			} else {
				echo '<script>location="'.SITEURL.'/task/product.edit/id/0/error/'.urlencode($error).'";</script>';
			}
		} else {
			echo 'Access violation'; exit();
		}	
	}
	
	public function delete() {
		$model = new ProductModel();
		$img = $this->request->input('id','');
		$products =  $model->getBy('img',$this->request->input('id',''));
		if (count($products) > 0) {
			if ($this->accessRight('delete',$products[0])) {
				unlink($this->uploadDir.'/'.$img);
				$this->model->delByImg($img);
				echo '<script>location="'.SITEURL.'/task/product.list/page/1";</script>';
			} else {
				echo 'Access violation'; exit();
			}	
		} else {
			echo '<div class="alert alert-danger">Nincs ilyen</div>';
		}			
	}
	
	protected function remove_accent($str) {
		$a=array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
		$b=array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
		return str_replace($a,$b,$str);
	}
	protected function clearFileName($s) {
		return preg_replace("/[^a-z0-9._-]/", '', strtolower($this->remove_accent($s)));
	}
	
}
?>
