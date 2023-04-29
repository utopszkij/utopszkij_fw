<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/demomodel.php';
include_once __DIR__.'/../urlprocess.php';


/**
 * demo manager ontroller (vue -ban  megvalósított controller funkciók
 * igényelt model (includes/models/demomodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order,$orderDir), 
 *      getTotal($filter)
 * igényelt session adatok: loged,logedName, logedGroup
 *      opcionálisan: errorMsg, successMsg
 * igényelt viewer: denomanager.html 
 * - showform,
 * - editorform,
 * - broserform
 * - "üzleti logika" funkciók
 * Hivó url-elk:
 *  /task/demo.manager 
 *  /task/demo.manager/show/id
 *  /task/demo.manager/edit/id
 *  /task/demo.manager/browse/pno/order/ord/orderdir/ordDir/limit/lim/filter/fil
 *       fil: 'all' vagy  'fieldName|value.....'
 * */
class Demo extends Controller {

    /**
     * constructor
     */
	function __construct() {
		parent::__construct();
		$this->model = new DemoModel();
        $this->name = "demo";
        $this->ckeditorFields = ['description']; // filedName lista
	}

    /**
     * api getItem 
     * POSTs: id
     * @return JSON record or {} or {error:'ACCES_DENIED'}
     */
    public function api_getItem() {
        $id = $this->request->input('id');
        $result = $this->model->getById($id);
        // ckeditor fields kezelése
        foreach ($this->ckeditorFields as $field) {
            $field2 = $field.'2';
            $result->$field2 = urlprocess($result->$field);
        }
        if ($this->accessRight('show',$result)) {
            echo JSON_encode($result);
        } else {
            echo JSON_encode(['error'=>'ACCES_DENIED']);
        }
    }

    /**
     * api getItems 
     * POSTs: page, limit, order, filter
     * @return JSON array of record
     */
    public function api_getItems() {
        $page = $this->request->input('page',1);
        $limit = $this->request->input('limit',20);
        $order = $this->request->input('order','id');
        $orderDir = $this->request->input('orderdir','id');
        $filter = $this->request->input('filter','all');
        $result = $this->model->getItems($page,$limit,$filter,$order, $orderDir);
        echo JSON_encode($result);
    }

    /**
     * api getTotal 
     * POSTs: filter
     * @return JSON total
     */
    public function api_getTotal() {
        $filter = $this->request->input('filter');
        $result = $this->model->getTotal($filter);
        echo JSON_encode($result);
    }

    /**
     * api save 
     * POSTs: record mezői
     * @return JSON {id:##} or {error:xxxxxx}
     */
    public function api_save() {
        $record = $this->model->emptyRecord();
        foreach ($record as $key => $value) {
            if (isset($_POST[$key])) {
                $record->$key = urldecode($this->request->input($key,'',HTML));
            }
        }

        $error = '';
        if ($record->id > 0) {
            if (!$this->accessRight('edit',$record)) {
                $error = 'ACCES_DENIED';
            }
        } else {
            if (!$this->accessRight('new',$record)) {
                $error = 'ACCES_DENIED';
            }
        }
        if ($error == '') {
            $error = $this->validator($record);
        }    

        if ($error != '') {
            echo '{"error":"'.$error.'"}';
        } else {
            $result = $this->model->save($record);
            echo '{"id":'.$result.'}';
        }    
    }

    /**
     * api delete 
     * GET: id
     * @return JSON {error:xxxxxx} or {error:""}
     */
    public function api_delete() {
        $id = $this->request->input('id');
        $record = $this->model->getById($id);
        if (!$this->accessRight('delete',$record)) {
            echo '{"error":"ACCES_DENIED"}';
        } else {
            $result = $this->model->delById($id);
            echo '{"error":""}';
        }    
    }

    /**
     * api emptyRecord 
     * @return JSON empty record
     */
    public function api_emptyRecord() {
        $record = $this->model->emptyRecord();
        echo JSON_encode($record);
    }

    /**
     * loged user hozzáférés ellenörzése
     * @param string $action  'new'|'edit'|'delete'|'show'
     * @param RecordObject $record
     * @return bool
     * ================== Gyakran javítandó ==================
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
     * ================= Rendszerint javítandó =================
     */    
    protected function validator($record): string {
		$result = '';
		if ($record->name == '') {
			$result = 'NAME_REQUERED';
		}
        return $result;
    }

    public function manager() {
        echo '<script type="text/javascript" src="vendor/axios/axios.js"></script>';
        view('demomanager',[
            'show' => $this->request->input('show',''),
            'edit' => $this->request->input('edit',''),
            'page' => $this->request->input('browse',1),
            'order' => $this->request->input('order','id'),
            'orderDir' => $this->request->input('orderdir','ASC'),
            'filter' => $this->request->input('filter','all'),
            'limit' => $this->request->input('limit',0),
            'loged' => $this->loged,
            'logedName' => $this->logedName,
            'logedGroup' => $this->logedGroup,
            'logedAdmin' => $this->logedAdmin,
            'errorMsg' => $this->session->input('errorMsg'),
            'successMsg' => $this->session->input('successMsg'),
            'items' => [],
            'record' => [],
            'total' => 0,
            'ckeditorFields' => $this->ckeditorFields
        ]);
    }
	
}


?>
