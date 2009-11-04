<?php
/**
 * Behavior para Upload de arquivos
 *
 * IMPORTANTE:
 *
 *      As URLs HTTP e do sistema são diferentes, obviamente. Para o Upload de
 *      arquivos, o PHP não leva em consideração o .htaccess. Entretanto, para
 *      amostragem em <img>, é necessário um endereço compatível. Portanto,
 *      é usando $this->path para salvar no DB, mas não é salvo o endereço do
 *      DB no sistema, mas sim um gerado para ser compatível com .htaccess.
 *
 *      É usado WEBROOT_ABSOLUTE.
 *
 * @package Behaviors
 * @name Upload
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.0.6, 16/10/2009
 */
class UploadBehavior extends Behavior
{

    /*
     * OPÇÕES
     */
    /**
     * Endereço onde serão salvos os arquivos. Por padrão, uploads/.
     * 
     * @var string
     */
    private $path = UPLOADS_PUBLIC_DIR;

    /**
     * Tamanho máximo permitido do arquivo. Valor em bytes.
     *
     * @var string
     */
    public $max_filesize = "10000000"; // in bytes

    /**
     * Largura máxima permitida caso o arquivo seja imagem.
     *
     * @var string
     */
    public $max_width;
    /**
     * Altura máxima permitida caso o arquivo seja imagem.
     *
     * @var string
     */
    public $max_height;

    public $filenameType;
    /**
     * Última mensagem de erro.
     *
     * @var string
     */
    public $error;

    /**
     * Todas as mensagens de erro, se ocorreram.
     *
     * @var array
     */
    public $allErrors = array();

    /**
     * Endereço do último arquivo inserido
     *
     * @var string
     */
    public $lastInsertPath;

    public $lastFilename;
    public $lastSize;
    public $lastType;

    /**
     * Funcionalidade que cria subdiretórios em app/public/uploads
     * automaticamente, ficando no formato app/public/uploads/ano/mês/
     *
     * @var bool
     */
    public $autoOrganizeFolders = true;

    function __construct(&$model) {
        parent::__construct($model);
    }

    /**
     * file()
     *
     * Realiza o upload do arquivo passado como argumento.
     *
     * @param array $file O mesmo formato vindo do formulário
     * @return mixed Retorna o endereço do arquivo salvo
     */
    public function file($file = ""){

        if( empty($file) )
            return false;

        /*
         * VALIDAÇÃO
         */
        if( !$this->validate($file) )
            return false;

        /*
         * Gera um nome único para a imagem SHA1
         */
        if( $this->filenameType == "sha1" )
            $fileName = sha1(uniqid(time())) . "." . $this->getExtension($file["name"]);
        else if( $this->filenameType == "md5" )
            $fileName = md5(uniqid(time())) . "." . $this->getExtension($file["name"]);
        else
            $fileName = $file["name"];

        /*
         * Caminho de onde a imagem ficará
         */
        $fileDir = $this->path ;

        /*
         * autoOrganizaFolders
         *
         * Cria diretório ano/mês/ para separar e organizar melhor os uploads
         */

        $fileDir = $this->_organizeFolders($fileDir);
        $filePath = $fileDir . $fileName;

        $systemFilePath = getcwd() . "/" . $filePath;
        $webFilePath = WEBROOT_ABSOLUTE . $filePath;

        
        /*
         * Salva informações da imagem
         */
        $this->lastSize = $file["size"];
        $this->lastType = $file["type"];
        $this->lastFilename = $file["name"];
        /*
         * UPLOAD DA IMAGEM
         */
        if( move_uploaded_file($file["tmp_name"], $filePath) ){

            $this->lastSystemPath = $systemFilePath;
            $this->lastWebPath = $webFilePath;
            return $webFilePath;
        }

        return false;
    }

    /*
     *
     * VALIDAÇÃO
     *
     * Valida se o arquivo pode ser uploaded
     *
     */
    public function validate($file){

        $valid = true;

        /*
         * Verifica tamanho do arquivo
         */
        if($file["size"] > $this->max_filesize ){
            $this->_setError("max_filesize");
            $valid = false;
        }

        /*
         * SE IMAGEM
         */
        /*
         * É imagem
         *
         * Verifica se o mime-type do arquivo é de imagem
         */
        if( $this->isImage($file["type"]) ){

            /*
             * Dimensões da imagem
             */
            $imageSize = getimagesize($file["tmp_name"]);

            /*
             * Verifica largura do arquivo
             */
            if( !empty($this->max_width)
                AND $imageSize[0] > $this->max_width ){
                $this->_setError("max_width");
                $valid = false;
            }

            /*
             * Verifica altura do arquivo
             */
            if( !empty($this->max_height)
                AND $imageSize[1] > $this->max_height ){
                $this->_setError("max_height");
                $valid = false;
            }
        }

        return $valid;

    }

    /**
     * isImage()
     *
     * Verifica se um arquivo é imagem.
     *
     * @param string $fileType O tipo mimetype do arquivo
     * @return bool
     */
    public function isImage($fileType){
        if( eregi("^image\/(tiff|pjpeg|jpeg|png|gif|bmp)$", $fileType) ){
            return true;
        }

        return false;
    }

    /**
     * getExtension()
     *
     * Retorna a extensão de um arquivo de acordo com seu nome.
     *
     * @param string $fileName
     * @return string
     */
    public function getExtension($fileName){
        $ext = explode('.', $fileName);
        $ext = array_reverse($ext);
        return $ext[0];
    }


    /*
     *
     * COMANDOS INTERNOS
     *
     */
    /**
     * _organizeFolders()
     *
     * Organiza os diretórios dentro da pasta de upload para melhor
     * visualização.
     *
     * @param string $dirToUpload Diretório a ser organizado
     * @return string Diretório final criado
     */
    public function _organizeFolders($dirToUpload){
        //$dirToUpload = getcwd()."/".$dirToUpload;
        //$dirToUpload = getcwd()."/".$dirToUpload;
        //$dirToUpload = getcwd()."/".$dirToUpload;
        //$dirToUpload = "/acidphp/app/public/".$dirToUpload;
        //$dirToUpload = "app/public/".$dirToUpload;

        if( $this->autoOrganizeFolders ){
            $dirToUpload.= date("Y")."/";

            if( !is_dir($dirToUpload) ){
                if( mkdir($dirToUpload, 0755) ){
                    chmod($dirToUpload, 0777);

                    $dirToUpload.= date("m") . "/";
                    if( !is_dir($dirToUpload) ){
                        if( mkdir($dirToUpload, 0755) ){
                            chmod($dirToUpload, 0777);
                        } else {
                            showError("Permission denied on creating year/ dir for uploading files. Verify this.");
                            return false;
                        }
                    }
                } else {
                    showError("Permission denied on creating month/ dir for uploading files. Verify this.");
                    return false;
                }

            } else {
                $dirToUpload.= date("m") . "/";
                if( !is_dir($dirToUpload) ){
                    if( mkdir($dirToUpload, 0755) ){
                        chmod($dirToUpload, 0777);
                    } else {
                        showError("Permission denied on creating month/ dir for uploading files. Verify this.");
                        return false;
                    }
                }
            }
        }

        return $dirToUpload;
        
    }

    /**
     * _setError()
     *
     * Ajusta os erros ocorridos e salva em $this->error.
     *
     * @param string $str Mensagem de erro.
     */
    public function _setError($str){
        $this->error = $str;
        
        if( empty($this->allErrors) ){
            $this->allErrors[] = $str;
        } else if( is_string($this->allErrors) ){
            $tmp = $this->allErrors;
            $this->allErrors = null;
            $this->allErrors[] = $tmp;
            $this->allErrors[] = $str;
        } else if( is_array($this->allErrors) ) {
            $this->allErrors[] = $str;
        }
    }

}
?>