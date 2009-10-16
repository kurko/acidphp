<?php
/**
 * COMPONENT UPLOAD
 *
 * Realiza upload de arquivos.
 *
 * @package Components
 * @name Upload
 * @author Lucas Pelegrino <lucas_wxp@hotmail.com>
 * @since v0.1, 29/09/2009
 */
/**
 * Configura��es b�sicas necess�rias
 *
 *      var $components = array("Upload") -> propriedade de AppController
 */

class UploadComponent extends Component
{
/**
 *
 * @var <array> Configura��es para upload
 */
    public $config = array(
        'file' => NULL, // Arquivo
        'file_name' =>  NULL, // Nome ao qual ser� salvo
        'upload_path' => './uploads/', // Caminho de onde salvar�
        'overwrite' => FALSE // Se ir� sobrescrever se um arquivo com mesmo nome existir
    );

/**
 * 
 */
    private $file_name;
/**
 *
 * @param <array> $config Dados para realizar upload
 */
    function upload($config){

        if (is_array($this->config)):
            if (isset($this->config['file']) && !$this->config['file']):
               // Pega nome do arquivo
               if(!$this->config['file_name']):
                $this->config['file_name'] = $this->getName();
               endif;
              endif;
        endif;
        print_r($this->config);
    }

    private function getName(){
        return $this->data['name']['site']['arquivo'];
    }
}
?>