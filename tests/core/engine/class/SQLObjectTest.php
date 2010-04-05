<?php
require_once 'PHPUnit/Framework.php';

#####################################

require_once 'tests/config/auto_include.php';

#####################################

class sqlObjectTest extends PHPUnit_Framework_TestCase
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
            'useTable' => 'textos'
        );
        $this->Model = new Model($params);
        $this->Model->useTable = 'textos';
        //var_dump($this->Model->tableDescribed);
        /*
         * Informações de conexão com banco de dados
         */
        $this->obj = new SQLObject();
    }

    function testSelect(){

        /*
         * TEST 1 - SIMPLE
         */
        $params = array(
            'mainModel' => $this->Model,
            'conditions' => array(
                'Model.id' => '1',
            ),
            'fields' => array(
                'Model.id'
            ),

        );
        $sql = $this->obj->select($params);
        $this->assertType('array', $sql);
        
        $sql = preg_replace('/\n|\t/Us', "", preg_replace('/\s{2,}/s', " ", $sql));
        $this->assertEquals(
                trim("SELECT Model.id AS 'Model.id' FROM textos AS Model ".
                "WHERE (Model.id IN ('1'))" ),
                trim( reset( $sql ) )
            );

        /*
         * TEST 2 - Using OR and AND
         */
        $params = array(
            'mainModel' => $this->Model,
            'conditions' => array(
                'OR' => array(
                    'Model.id' => array('1', '2'),
                ),
                'Model.id' => '3',
            ),
            'fields' => array(
                'Model.id'
            ),

        );
        $sql = $this->obj->select($params);
        $this->assertType('array', $sql);

        $sql = preg_replace('/\n|\t/Us', "", preg_replace('/\s{2,}/s', " ", $sql));
        $this->assertEquals(
                trim("SELECT Model.id AS 'Model.id' FROM textos AS Model ".
                "WHERE (Model.id IN ('1', '2')) AND (Model.id IN ('3'))" ),
                trim( reset( $sql ) )
            );


    }
}
?>