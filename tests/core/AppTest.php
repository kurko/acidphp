<?php
require_once 'tests/config/auto_include.php';

class AppTest extends PHPUnit_Framework_TestCase
{

    public function setUp(){

		$_SERVER['REQUEST_URI'] = '';
		$_GET['url'] = '';

		$this->obj = null;
        $this->obj = new App();
		$this->obj->appDirs = array(
			'app', 'world'
		);


    }

	function testIsAppDir(){
		$this->assertTrue( $this->obj->isAppDir('app') );
		$this->assertTrue( $this->obj->isAppDir('app/hey/hey2') );
		$this->assertTrue( $this->obj->isAppDir('world/hey/hey2') );
		$this->assertTrue( $this->obj->isAppDir('world') );
	}
	
	function test_GetScriptName(){
//		$_SERVER['REQUEST_URI'] = '/acid/testapp/site/index'; // o que é digitado no navegador
//		$_GET['url'] = 'site/index';
//		$this->assertEquals('/acid/testapp/app', $this->obj->_getScriptName() );
	}

	function testGetCurrentAppDir(){
		$_SERVER['REQUEST_URI'] = '/acid/testapp/site/index'; // o que é digitado no navegador
		$_GET['url'] = 'site/index';
//		$this->assertEquals('app', $this->obj->getCurrentAppDir() );
	}
	
	function testGetRootDir(){

		$expected = str_replace('public/index.php', '', getcwd() );
		$this->assertEquals( $expected.'/', $this->obj->getRootDir() );
		$this->assertEquals( $expected.'/', ROOT );
		
	}

	function testSetWEBROOTAndSetUrl(){

		$_SERVER['REQUEST_URI'] = '/acid/testapp/site/index'; // o que é digitado no navegador
		$_GET['url'] = 'site/index';
		$this->assertEquals( '/acid/testapp/', $this->obj->setWEBROOT() );
		$this->assertEquals( 'site/index', $this->obj->url );
//		$this->assertEquals( 'site/index', $_GET['url'] );

		
		$_SERVER['REQUEST_URI'] = '/acid/testapp/app/site/index'; // o que é digitado no navegador
		$_GET['url'] = 'site/index';
		$this->assertEquals( '/acid/testapp/app/', $this->obj->setWEBROOT() );
		$this->assertEquals( 'site/index', $this->obj->url );
//		$this->assertEquals( 'site/index', $_GET['url'] );

		// supondo que há uma pasta de app chamada world
		$_SERVER['REQUEST_URI'] = '/acid/testapp/world/site/index';
		$_GET['url'] = 'world/site/index';
		$this->assertEquals( '/acid/testapp/world/', $this->obj->setWEBROOT() );
		$this->assertEquals( 'site/index', $this->obj->url );
//		$this->assertEquals( 'site/index', $_GET['url'] );
		
	}
	
	function test_GetSystemPublicDir(){

		$_GET['url'] = 'site/index';
		$_SERVER['REQUEST_URI'] = '/acid/testapp/app/site/index'; // o que é digitado no navegador

		$expected = str_replace('public/index.php', '', getcwd() ).'/public';
//		$this->assertEquals( $expected, $this->obj->_getSystemPublicDir() );

		$_SERVER['REQUEST_URI'] = '/acid/testapp/world/site/index'; // o que é digitado no navegador
		$_GET['url'] = 'world/site/index';
//		$this->assertEquals( '/Library/WebServer/clients/webapp/world/public/', $this->obj->_getSystemPublicDir() );
	}

	function testGetAppDir(){

		$_SERVER['REQUEST_URI'] = '/acid/testapp/site/index'; // o que é digitado no navegador
		$_GET['url'] = 'site/index';
		$this->assertEquals( THIS_PATH_TO_ROOT.'app/', $this->obj->getAppDir() );
		$this->setUp();

		$_SERVER['REQUEST_URI'] = '/acid/testapp/app/site/index'; // o que é digitado no navegador
		$_GET['url'] = 'app/site/index';
		$this->assertEquals( THIS_PATH_TO_ROOT.'app/', $this->obj->getAppDir() );
		$this->setUp();

		$_SERVER['REQUEST_URI'] = '/acid/testapp/world/site/index'; // o que é digitado no navegador
		$_GET['url'] = 'world/site/index';
		$this->assertEquals( THIS_PATH_TO_ROOT.'world/', $this->obj->getAppDir() );
		$this->setUp();

		$_SERVER['REQUEST_URI'] = '/acid/testapp/'; // o que é digitado no navegador
		$_GET['url'] = '';
		$this->assertEquals( THIS_PATH_TO_ROOT.'app/', $this->obj->getAppDir() );

	}
	
}
?>