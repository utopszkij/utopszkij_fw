<?php

use PHPUnit\Framework\TestCase;
use RATWEB\DB\Query;
use RATWEB\DB\Record;

include_once __DIR__.'/mock.php';

include_once('config_test.php');
include_once('./includes/models/visitormodel.php');

class VisitormodelTest extends TestCase {
	protected $controller;
	
	function __construct() {
		parent::__construct();
		$this->model = new VisitorModel();
        $_SERVER['REMOTE_ADDR'] = '192.168.0.13';
	}
    
	// ez csak egyszer fut
	public function test_start()  {
		$db = new Query('groups');
		$db->delete();
        $rec = new Record();
        $rec->id = 1;
        $rec->name = 'admin';
        $db->insert($rec);
        $rec->id = 2;
        $rec->name = 'guest';
        $db->insert($rec);

		$db = new Query('users');
		$db->delete();

        $db = new Query('user_group');
		$db->delete();

		$record = $this->model->emptyRecord();
		$record->id = 0;
		$record->username = 'admin';
        $record->enabled = 1;
        $record->deleted = 0;
        $record->email_verifyed = 1;
        $record->groups = [JSON_decode('{"id":1,"name":"admin"}')];
		$this->model->save($record);

        $recs = $this->model->getBy("username","admin");
		$this->assertEquals(1,count($recs));
		$this->assertEquals('admin',$recs[0]->username);
    }

	// ez minden egyes test rutin előtt lefut
	public function setup():void {
	}

	public function test_add_user1()  {
        // új IP, "user1" felvitele groups nincs definiálva
        $_SERVER['REMOTE_ADDR'] = '192.168.0.14';
		$record = $this->model->emptyRecord();
		$record->id = 0;
		$record->username = 'user1';
        $record->enabled = 1;
        $record->deleted = 0;
        $record->email_verifyed = 1;
		$this->model->save($record);

        $recs = $this->model->getBy("username","user1");
		$this->assertEquals(1,count($recs));
		$this->assertEquals('user1',$recs[0]->username);
		$this->assertEquals(0,count($recs[0]->groups));
    }

	public function test_update_user1_add_to_groups()  {
        $recs = $this->model->getBy("username","user1");
        $record = $recs[0];
        $record->groups = [JSON_decode('{"id":2,"name":"guest"}')];
		$this->model->save($record);

        $recs = $this->model->getBy("username","user1");
		$this->assertEquals(1,count($recs));
		$this->assertEquals('user1',$recs[0]->username);
		$this->assertEquals(1,count($recs[0]->groups));
		$this->assertEquals(2,$recs[0]->groups[0]->id);
    }

	public function test_update_user1_del_from_groups()  {
        $recs = $this->model->getBy("username","user1");
        $record = $recs[0];
        $record->groups = [];
		$this->model->save($record);

        $recs = $this->model->getBy("username","user1");
		$this->assertEquals(1,count($recs));
		$this->assertEquals('user1',$recs[0]->username);
		$this->assertEquals(0,count($recs[0]->groups));
    }

	public function test_update_user1_locktime_and_error_count()  {
        $recs = $this->model->getBy("username","user1");
        $record = $recs[0];
        $record->locktime = 1;
        $record->error_count = 2;
		$this->model->saveToIp($record);

        $recs = $this->model->getBy("username","user1");
		$this->assertEquals(1,count($recs));
		$this->assertEquals('user1',$recs[0]->username);
		$this->assertEquals(1,$recs[0]->locktime);
		$this->assertEquals(2,$recs[0]->error_count);
    }

    public function test_add_delete() {
		$record = $this->model->getById(0);
		$record->id = 0;
		$record->username = 'user2';
        $record->enabled = 1;
        $record->deleted = 0;
        $record->email_verifyed = 1;
		$id = $this->model->save($record);
        $recs = $this->model->getBy("username","user2");
		$this->assertEquals(1,count($recs));
		$this->assertEquals('user2',$recs[0]->username);
		$this->assertEquals(0,count($recs[0]->groups));
        $this->model->delById($id);
        $recs = $this->model->getBy("username","user2");
		$this->assertEquals(0,count($recs));
    }


    /*
	protected function loginAdmin() {
		$_SESSION['loged'] = 1;
		$_SESSION['logedName'] = ADMIN;
		$_SESSION['logedAdmin'] = true;
		$_SESSION['logedGroup'] = 'admin';
		$this->controller = new Group();
	}

	protected function logout() {
		$_SESSION['loged'] = 0;
		$_SESSION['logedName'] = 'guest';
		$_SESSION['logedAdmin'] = false;
		$_SESSION['logedGroup'] = '';
		$this->controller = new Group();
	}

	protected function loginNotAdmin() {
		$_SESSION['loged'] = 2;
		$_SESSION['logedName'] = 'user';
		$_SESSION['logedAdmin'] = false;
		$_SESSION['logedGroup'] = '';
		$this->controller = new Group();
	}

    public function test_add_notlogged() {
		$this->logout();
		$this->controller->add();
		$this->expectOutputRegex('/group.groups/'); 
	}

    public function test_add_logedNotAdmin() {
		$this->loginNotAdmin();
		$this->controller->add();
		$this->expectOutputRegex('/group.groups/'); 
	}

    public function test_add_logedAdmin() {
		$this->loginAdmin();
		$this->controller->add();
		$this->assertEquals(checkview()['name'],'groupform');
		$this->assertEquals(checkview()['params']['record']->name,'');
		$this->expectOutputRegex('//'); 
	}

    public function test_store_notlogedk() {
		$this->logout();		
        $_POST['id'] = 0;
        $_POST['name'] = 'admin';
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->controller->store();
		$this->expectOutputRegex('/ACCESSDENIED/'); 
	}

    public function test_store_logedNotAdmin() {
		$this->loginNotAdmin();		
        $_POST['id'] = 0;
        $_POST['name'] = 'admin';
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->controller->store();
		$this->expectOutputRegex('/ACCESSDENIED/'); 
	}

	public function test_store_new_ok() {
		$this->loginAdmin();		
        $_POST['id'] = 0;
        $_POST['name'] = 'admin';
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->controller->store();
        $_POST['id'] = 0;
        $_POST['name'] = 'moderator';
		$this->controller->store();
		$this->expectOutputRegex('/group.groups/'); 
        $q = new Query('groups');
        $rec = $q->where('name','=','admin')->first();
        $this->assertEquals($rec->name,'admin');
        $q = new Query('groups');
        $rec = $q->where('name','=','moderator')->first();
        $this->assertEquals($rec->name,'moderator');
	}

	public function test_store_editAdmin_logedAdmin() {
		$this->loginAdmin();		
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
        $_POST['id'] = $adminGroup->id;
        $_POST['name'] = 'admin_javitva';
		$this->controller->store();
		$this->expectOutputRegex('/ADMIN_CANNOT_UPDATE/'); 
		$db = new Query('groups');
		$adminGroup = $db->where('id','=',$adminGroup->id)->first();
        $this->assertEquals($adminGroup->name,'admin');
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin_javitva')->first();
        $this->assertEquals(isset($adminGroup->name),false);
	}	

	public function test_update_notloged() {
		$this->logout();
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
		$_GET['id'] = $adminGroup->id;
		$this->controller->update();
		$this->assertEquals(checkview()['name'],'groupform');
	}

	public function test_update_logedNotAdmin() {
		$this->loginNotAdmin();
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
		$_GET['id'] = $adminGroup->id;
		$this->controller->update();
		$this->assertEquals(checkview()['name'],'groupform');
	}

	public function test_update_logedAdmin() {
		$this->loginAdmin();
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
		$_GET['id'] = $adminGroup->id;
		$this->controller->update();
		$this->assertEquals(checkview()['name'],'groupform');
	}	

	public function test_remove_notloged() {
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->logout();
		$db = new Query('groups');
		$moderatorGroup = $db->where('name','=','moderator')->first();
		$_GET['id'] = $moderatorGroup->id;
		$this->controller->remove();
		$this->assertEquals('ACCESDENIED',$_SESSION['errorMsg']); 
		$this->expectOutputRegex('/group.groups/'); 
	}

	public function test_remove_logedNotAdmin() {
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->loginNotAdmin();
		$db = new Query('groups');
		$moderatorGroup = $db->where('name','=','moderator')->first();
		$_GET['id'] = $moderatorGroup->id;
		$this->controller->remove();
		$this->assertEquals('ACCESDENIED',$_SESSION['errorMsg']); 
		$this->expectOutputRegex('/group.groups/'); 
	}

	public function test_remove_admin_logedAdmin() {
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->loginAdmin();
		$db = new Query('groups');
		$adminGroup = $db->where('name','=','admin')->first();
		$_GET['id'] = $adminGroup->id;
		$this->controller->remove();
		$this->assertEquals('ACCESDENIED',$_SESSION['errorMsg']); 
		$this->expectOutputRegex('/group.groups/'); 
	}

	public function test_remove_logedAdmin_ok() {
		$_SESSION['key'] = '123';
		$_POST['key'] = '123';
		$this->loginAdmin();
		$db = new Query('groups');
		$moderatorGroup = $db->where('name','=','moderator')->first();
		$_GET['id'] = $moderatorGroup->id;
		$this->controller->remove();
		$this->assertEquals('DELETED',$_SESSION['successMsg']); 
		$this->expectOutputRegex('/group.groups/'); 
	}
    */

}
