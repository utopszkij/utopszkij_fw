<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/demomodel.php';

/**
 * demo controller 
 * igényelt model (includes/models/demomodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/demobrowser, includes/views/demoform 
 *      a demoform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
 *      a browser jelenitse meg szükség szerint az errorMsg, successMsg adatot is!
 *      a form jelenitse meg szükség szerint az errorMsg adatot is, a rekord mezőivel azonos nevü
 *             input vagy select elemeket tartalmazzon 
 *      (beleértve az id -t is)
 * igényelt session adatok: loged,logedName, logedGroup
 *      opcionálisan: errorMsg, successMsg
 * 
 * A taskok public function -ként legyenek definiálva 
 *   standart taskok: items, edit, new, save, delete.
 */
class Demo extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new DemoModel();
        $this->name = "demo";
        $this->browserURL = 'index.php?task=demo.items';
        $this->addURL = 'index.php?task=demo.new';
        $this->editURL = 'index.php?task=demo.edit';
        $this->browserTask = 'demo.items';
        $this->model = new DemoModel();
        $this->ckeditorFields = []; // filedName lista
	}

    /**
     * loged user hozzáférés ellenörzése
     * @param string $action  'new'|'edit'|'delete'|'show'
     * @param RecordObject $record
     * @return bool
     */    
    protected function accessRight(string $action, $record): bool {
		// $this->loged  -- a bejelentkezett user azonosítója
		// $this->logedGroup -- '[group1,group2,...]'
		$result = true;
		if (($action == 'new') | ($action == 'edit') | ($action == 'delete')) {
			if ($this->loged <= 0) {
				$result = false;
			}
			if (strpos($this->logedGroup,'admin') <= 0) {
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
		if ($record->name == '') {
			$result .= 'NAME_REQUERED<br />';
		}
        return $result;
    }
    
    /**
     * rekord készlet lekérdezés
     * GET|POST page, order, limit, filter, 
     * POST filter_name....
     */ 
    public function items($order = 1) {
		// képernyöről POST -ban érkező filter_name paraméterek
		// átalakitása 'name|value...' string formára
		$pFilter = [];
		foreach ($_POST as $fn => $fv) {
			if (substr($fn,0,7) == 'filter_') {
				$fv = $this->request->input($fn); // sql injection szürés
				$pFilter[] = substr($fn,7,100); 
				$pFilter[] = $fv; 
			}
		}
		if ($this->request->input('filter') == '') {
			$this->request->set('filter', implode('|',$pFilter));
		}
		parent::items();
	}

    /**
     * api_upload
     * POSTs: record mezői, file, uploadDir, extensions
     * return {'error':''} vagy {error:'hibaüzenet'} és ha nincs hiba akkor sessionba succesMsg 
    */ 
	public function api_upload() {
//+ gyakran modosítandó
		$this->session->set('errorMsg','');
		$this->session->set('successMsg','');
		$error = '';
		// record kialakítása
		$record = $this->model->emptyRecord();
		foreach ($record as $fn => $fv) {
			$record->$fn = $this->request->input($fn); 
			// if ($fn == 'htmlfield') $record->$fn = $this->request->input($fn,'HTML');
		}
		// record validálás 
		$error = $this->validator($record);
		if ($error == '') {
			  	// record tárolása adatbáziba
			  	$id = $this->model->save($record);
				$record->id = $id;
				$this->session->set('successMsg','SAVED');

				/*+ ha file upload is van
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
				$uploadUrl = $uploadDir.'/'; 
			  	$uploadDir .= '/';
				$uploadFileCname = $_FILE['file'];
				$uploadFileExt = pathinfo($uploadFileCname,PATHINFO_EXTENSION);
				$uploadFile = $uploadDir.$id.'.'.$uploadFileExt;
				if (!in_array($uploadFileExt, $extensions)) {
					$result = JSON_encode(array('error'=>'UPLOAD_NOT_ENABLED'));
					$this->session->set('successMsg','');
				} else {
					if (file_exists($uploadFile)) {
						unlink($uploadFile);
					}
					if (move_uploaded_file($_FILES[$fn]['tmp_name'], $uploadFile)) {
						// record modositása a file url beírása
						$url = $uploadUrl.$record->id.'_'. preg_replace( '/[^a-z0-9\.]/i', '_',(basename($_FILES[$fn]['name'])));
						$record->fileUrl = $url;
						$record->save();
						$this->session->set('errorMsg','');
						$this->session->set('successMsg','SAVED');
					} else {
						$this->session->set('successMsg','');
						$result = JSON_encode(array('error'=>'ERROR_IN_FILEUPLOAD'));
						$this->model->delById($record->id);
					}
				}
				*/

		} else {
			$this->session->set('successMsg','');
			$result = JSON_encode(array('error'=>$error));
		}
		echo $result;
		exit();
	}		
//-	
}
	



?>
