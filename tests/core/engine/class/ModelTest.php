<?php
require_once 'PHPUnit/Framework.php';

#####################################

require_once 'tests/config/auto_include.php';

#####################################

class ModelTest extends PHPUnit_Framework_TestCase
{

    public $obj;

    public function setUp(){
    
        /*
         * Informações de conexão com banco de dados
         */
        $params = array(
            'recursive' => 0,
            'params' => array(
                'controller' => 'controller1',
                'action' => 'action1',
                'webroot' => 'app/',
            ),
            'useTable' => 'textos',
        );
        $this->obj = new Model($params);
        //var_dump($this->obj->tableDescribed);
    }

    function testDescribeTable(){
        //var_dump($this->obj->_describeTable());

        $this->assertType('array', $this->obj->tableDescribed);
    }


}
?>