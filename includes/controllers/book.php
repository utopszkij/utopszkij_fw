<?php
use \RATWEB\DB\Query;
use \RATWEB\DB\Record;

include_once __DIR__.'/../models/bookmodel.php';
include_once __DIR__.'/../models/categorymodel.php';
include_once __DIR__.'/../models/authormodel.php';
include_once __DIR__.'/../uploader.php';

/**
 * book controller 
 * igényelt model (includes/models/bookmodel.php))
 *      methodusok: emptyRecord(), save($record), 
 *      getById($id), deleteById($id), getItems($page,$limit,$filter,$order), 
 *      getTotal($filter)
 * igényelt viewerek includes/views/bookbrowser, includes/views/bookform 
 *      a bookform legyen alkalmas show funkcióra is a record, loged, logedAdmin -tól függően
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
class Book extends Controller {

	function __construct() {
		parent::__construct();
		// $this->model = new BookModel();
        $this->name = "book";
        $this->browserURL = SITEURL.'/index.php?task=book.items';
        $this->addURL = SITEURL.'/index.php?task=book.new';
        $this->editURL = SITEURL.'/index.php?task=book.edit';
        $this->browserTask = 'book.items';
        $this->model = new BookModel();
        $this->ckeditorFields = []; // filedName lista
	}

	public function setup() {	
		echo '<div class="row">
			<div class="col-12">
				<h2>Book setup</h2>
				<ul>
					<li><a href="'.SITEURL.'/index.php?task=storage.items">'.lng('STORAGES').'</a></li>
					<li><a href="'.SITEURL.'/index.php?task=category.items">'.lng('CATEGORIES').'</a></li>
					<li><a href="'.SITEURL.'/index.php?task=author.items">'.lng('AUTHORS').'</a></li>
				</ul>
			</div>	
		</div>
		<div class="row">
			<div class="col-12">
				<a href="'.SITEURL.'/index.php?task=book.items"><em class="fas fa-reply"></em>'.lng('BOOKS').'</a>
			</div>
		</div>
		';
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
			if (strpos(' '.$this->logedGroup,'admin') <= 0) {
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
		if ($record->title == '') {
			$result = 'TITLE_REQUERED';
		}
		if ($record->storage == '') {
			$result = 'STORAGE_REQUERED';
		}
        return $result;
    }
    
    /**
     * rekord készlet lekérdezés
     * GET|POST page, order, limit, filter, 
     * POST filter_name....
     */ 
    public function items($order = 1) {

        // paraméter olvasása get vagy sessionból
        $page = $this->session->input($this->name.'page',1);
        $page = $this->request->input('page',$page);
        $limit = round((int)$_SESSION['screen_height'] / 80);
        $limit = $this->session->input($this->name.'limit',$limit);
        $limit = $this->request->input('limit',$limit);
        $order = $this->session->input($this->name.'order',$order);
        $order = $this->request->input('order',$order);

		// filter kezelés	
        $sFilter = $this->session->input($this->name.'filter',''); // 'name|value...'
        $sFilterArray = $this->filterParse($sFilter); // [name => value,...]
		$rFilterArray = [];
        $rFilterArray['title'] = urldecode($this->request->input('filter_title','*'));
        $rFilterArray['author'] = urldecode($this->request->input('filter_author','*'));
        $rFilterArray['storage'] = urldecode($this->request->input('filter_storage','*'));
        $rFilterArray['category'] = urldecode($this->request->input('filter_category','*'));
		if ($this->request->input('filter') == 'all') {
			$sFilterArray = [];
			$filter = '';
		} else {
			foreach ($rFilterArray as $fn => $fv) {
				if ($fv != '*') {
					$sFilterArray[$fn] = $fv;
				}	
			}
			$filter = $this->filterToStr($sFilterArray); // 'name|value...'
		}
		// adatok a paginátor számára
        $total = $this->model->getTotal($filter);
        $pages = [];
        $p = 1;
        while ((($p - 1) * $limit) < $total) {
            $pages[] = $p;
            $p++;
        }
        $p = $p - 1;

        // hibás paraméter kezelés
        if ($page > $p) { 
            $page = $p; 
        }
        if ($page < 1) {
            $page = 1;
        }

        // paraméter tárolás sessionba
        $this->session->set($this->name.'page',$page);
        $this->session->set($this->name.'limit',$limit);
        $this->session->set($this->name.'filter',$filter);
        $this->session->set($this->name.'order',$order);
        // rekordok olvasása az adatbázisból
        $items = $this->model->getItems($page,$limit,$filter,$order);

        // megjelenítés
        view($this->name.'browser',[
            "items" => $items,
			"storages" => $this->model->getStorages(),
			"categories" => $this->model->getCategories(0),
            "page" => $page,
            "total" => $total,
            "pages" => $pages,
            "task" => $this->browserTask,
            "filter" => $filter,
            "loged" => $this->loged,
            "logedName" => $this->loged,
            "logedAdmin" => $this->logedAdmin(),
            "previous" => SITEURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
            "successMsg" => $this->session->input('successMsg','')
        ]);
        $this->session->delete('errorMsg');
        $this->session->delete('successMsg');
        $this->session->delete('oldRecord');

	}

    public function new() {
        $item = $this->model->emptyRecord();
        if (!$this->accessRight('new',$item)) {
            $this->session->set('errorMsg','ACCESDENIED');
            $this->items();
        }
        if ($this->session->isset('oldRecord')) {
            $item = $this->session->input('oldRecord');
        }
        $this->browserURL = $this->request->input('browserUrl', $this->browserURL);
        view($this->name.'form',[
            "flowKey" => $this->newFlowKey(),
            "record" => $item,
            "storages" => $this->model->getStorages(),
            "categories" => $this->model->getCategories($item->id),
            "authors" => $this->model->getAuthors($item->id),
            "loged" => $this->loged,
            "logedName" => $this->loged,
            "logedAdmin" => $this->logedAdmin(),
            "previous" => $this->browserURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg','')
        ]);
        foreach ($this->ckeditorFields as $ckeditorField) {
            echo '<script type="text/JavaScript">
            if (window.editor == undefined) {
                ClassicEditor
                .create( document.querySelector( "textarea#'.$ckeditorField.'" ), {
                    toolbar: [ "heading", "|", "bold" , "italic", "link", "bulletedList", "numberedList",
                       "imageUpload","insertTable","sourceEditing","mediaEmbed","undo","redo"],
                    language: "hu",
                    extraPlugins: [ MyCustomUploadAdapterPlugin ],
                    mediaEmbed: { extraProviders: window.myExtraProviders 	}
                } )
                .then( editor => {
                    window.editor = editor;
                } )
                .catch( err => {
                    console.log("ckeditor error");
                    console.log( err.stack );
                } );
            }            
            </script>
            ';
        }
        $this->session->delete('errorMsg');
    }

/**
     * meglévő item edit/show képernyő
     * a viewernek a record, loged, loagedAdmin alapján vagy editor vagy show
     * képernyőt kell megjelenitenie
     * GET: id, displaymode
     */
    public function edit() {
        $id = $this->request->input('id',0, INTEGER);
        $record = $this->model->getById((int)  $id);
        // $record->displayMode = $this->request->input('displaymode','show');
        $record->displayMode = 'edit';
        if (!$this->accessRight('edit',$record) & !$this->accessRight('show',$record)) {
            $this->session->set('errorMsg','ACCESDENIED');
            $this->items();
        }
        if ($this->session->isset('oldRecord')) {
            $record = $this->session->input('oldRecord');
        }
        foreach ($this->ckeditorFields as $ckeditorField) {
			$fn2 = $ckeditorField.'2';
			$record->$fn2 = urlprocess($record->$ckeditorField);
		}
        $this->browserURL = $this->request->input('browserUrl', $this->browserURL);
        if ($record->displayMode == 'edit') {
            $viewName = $this->name.'form';
        } else {
            $viewName = $this->name.'show';
        }
        
        view($viewName,["flowKey" => $this->newFlowKey(),
            "record" => $record,
            "storages" => $this->model->getStorages(),
            "categories" => $this->model->getCategories($record->id),
            "authors" => $this->model->getAuthors($record->id),
            "logedAdmin" => $this->logedAdmin(),
            "loged" => $this->loged,
            "previous" => $this->browserURL,
            "browserUrl" => $this->browserURL,
            "errorMsg" => $this->session->input('errorMsg',''),
        ]);
        $this->session->delete('errorMsg');
    }

    public function save($record = '') {
        $flowKey = $this->request->input('flowKey','');
        if (!$this->checkFlowKey($flowKey)) {
            $this->session->set('errorMsg','FLOWKEY');
            $this->items();
        }
        $item = $this->model->emptyRecord();
        $item->id = $this->request->input('id',0);
        $item->title = $this->request->input('title','');
        $item->storage = $this->request->input('storage','');
        $item->image_url = $this->request->input('image_url','');
        $item->book_url = $this->request->input('book_url','');
        $item->volumes = $this->request->input('volumes','');
        $item->year = $this->request->input('year','');

        // ha kép upload volt tárol és beir a rekord-ba
        $result = Uploader::doImgUpload('image','images/uploads',$item->title.'.*');
        if (!isset($result->errorMsg) & (isset($result->name)))  {
            if ($result->name != '') {
                $item->image_url = 'images/uploads/'.$result->name;
            }
        }
        $item->id = $this->model->save($item);
        // kategóriák kezelése
        $categoryModel = new CategoryModel();
        $newCategories = [];
        for ($i = 0; $i < 30; $i++) {
            $category = $this->request->input('category'.$i,'');
            if ($category != '') {
                $newCategories[] = (int)$category;
            }
        }
        $oldCategories = $categoryModel->getBookCategories($item->id);
        // újak kapcsoláa
        foreach ($newCategories as $newCategory) {
            if (!in_array($newCategory,$oldCategories)) {
                $categoryModel->addBookCategory($item->id,(int)$newCategory);
            }
        }
        // régi felsleges kapcsolatok törlése
        foreach ($oldCategories as $oldCategory) {
            if (!in_array($oldCategory,$newCategories)) {
                $categoryModel->deleteBookCategory($item->id,(int)$oldCategory);
            }
        }



        // szerzők kezelése
        $authorModel = new AuthorModel();
        $newAutors = [];
        for ($i = 0; $i < 30; $i++) {
            $author = $this->request->input('author'.$i,'');
            if ($author != '') {
                $newAuthors[] = (int)$authorModel->addAuthor($author);
            }
        }
        $oldAuthors = $authorModel->getBookAuthors($item->id);
        // újak kapcsoláa
        foreach ($newAuthors as $newAuthor) {
            if (!in_array($newAuthor,$oldAuthors)) {
                $authorModel->addBookAuthor($item->id,(int)$newAuthor);
            }
        }
        // régi felsleges kapcsolatok törlése
        foreach ($oldAuthors as $oldAuthor) {
            if (!in_array($oldAuthor,$newAuthors)) {
                $authorModel->deleteBookAuthor($item->id,(int)$oldAuthor);
            }
        }

        $this->session->set('successMsg','SAVED');
        $this->items();

    }

    public function api_upload() {
        /*
        $flowKey = $this->request->input('flowKey','');
        if (!$this->checkFlowKey($flowKey)) {
            $this->session->set('errorMsg','FLOWKEY');
            // $this->items();
            echo '{"error":"FLOWKEY_ERROR"}';
            return;
        }
        */
        $item = $this->model->emptyRecord();
        $item->id = $this->request->input('id',0);
        $item->title = $this->request->input('title','');
        $item->storage = $this->request->input('storage','');
        $item->image_url = $this->request->input('image_url','');
        $item->book_url = $this->request->input('book_url','');
        $item->volumes = $this->request->input('volumes','');
        $item->year = $this->request->input('year','');

        // ha kép upload volt tárol és beir a rekord-ba
        $result = Uploader::doImgUpload('image','images/uploads','');
        if (!isset($result->errorMsg) & (isset($result->name)))  {
            if ($result->name != '') {
                $item->image_url = 'images/uploads/'.$result->name;
            }
        }
        $item->id = $this->model->save($item);
        // kategóriák kezelése
        $categoryModel = new CategoryModel();
        $newCategories = [];
        for ($i = 0; $i < 30; $i++) {
            $category = $this->request->input('category'.$i,'');
            if ($category != '') {
                $newCategories[] = (int)$category;
            }
        }
        $oldCategories = $categoryModel->getBookCategories($item->id);
        // újak kapcsoláa
        foreach ($newCategories as $newCategory) {
            if (!in_array($newCategory,$oldCategories)) {
                $categoryModel->addBookCategory($item->id,(int)$newCategory);
            }
        }
        // régi felesleges kapcsolatok törlése
        foreach ($oldCategories as $oldCategory) {
            if (!in_array($oldCategory,$newCategories)) {
                $categoryModel->deleteBookCategory($item->id,(int)$oldCategory);
            }
        }



        // szerzők kezelése
        $authorModel = new AuthorModel();
        $newAutors = [];
        for ($i = 0; $i < 30; $i++) {
            $author = $this->request->input('author'.$i,'');
            if ($author != '') {
                $newAuthors[] = (int)$authorModel->addAuthor($author);
            }
        }
        $oldAuthors = $authorModel->getBookAuthors($item->id);
        // újak kapcsoláa
        foreach ($newAuthors as $newAuthor) {
            if (!in_array($newAuthor,$oldAuthors)) {
                $authorModel->addBookAuthor($item->id,(int)$newAuthor);
            }
        }
        // régi felsleges kapcsolatok törlése
        foreach ($oldAuthors as $oldAuthor) {
            if (!in_array($oldAuthor,$newAuthors)) {
                $authorModel->deleteBookAuthor($item->id,(int)$oldAuthor);
            }
        }

        $this->session->set('successMsg','SAVED');
        echo '{"error":""}';
        return;
    }

	
}


?>
