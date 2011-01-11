<?php
class ImageRendererComponent extends Component
{
    function __construct($params = ""){
        parent::__construct($params);

		$this->controller->setAjax();
		$this->controller->render(false);
    }
	
	/**
	 * render()
	 * 
	 * Carrega as imagens de diversas fontes diferentes, seja de arquivos
	 * físicos, seja de um model com o endereço de um arquivo físico,
	 * seja de um model com dados binários da imagem.
	 * 
	 * @param array $options
	 *
	 *		opt object 	'model': referência ao model onde está a imagem
	 *		opt int		'id': id do campo de imagem
	 *		opt string	'fieldPath': campo com endereço absoluto da imagem
	 *		opt string	'fieldSize': campo com tamanho da imagem
	 *		opt string	'fieldType': campo com o tipo da imagem
	 *		opt string 	'maxxsize' e 'maxysize': tamanho máximo da imagem
	 *		opt string 	'xsize' e 'ysize': tamanho definido da imagem
	 *
	 * 
	 */
	function render($options){
		
		/* if from model */
     	$model = ( empty($options["model"]) ) ? false : $options["model"];
     	$id = ( empty($options["id"]) ) ? false : $options["id"];
		
		/* if from file */
     	$path = ( empty($options["path"]) ) ? false : $options["path"];

		/* formatting options */
     	$crop = ( empty($options["crop"]) ) ? false : $options["crop"];
     	$quality = ( empty($options["quality"]) ) ? 100 : $options["quality"];
     	$watermark = ( empty($options["watermark"]) ) ? false : $options["watermark"];

		$optionsForRendering = array(
			'crop' 		=> $crop,
			'quality'	=> $quality,
			'watermark' => $watermark,
		);
		
		if( empty($options["xsize"]) || empty($options["xsize"]) ){
			$optionsForRendering['maxxsize'] = ( empty($options["maxxsize"]) ) ? '800' : $options["maxxsize"];
	     	$optionsForRendering['maxysize'] = ( empty($options["maxysize"]) ) ? '600' : $options["maxysize"];
			
		}
		
     	$optionsForRendering['xsize'] = ( empty($options["xsize"]) ) ? '' : $options["xsize"];
     	$optionsForRendering['ysize'] = ( empty($options["ysize"]) ) ? '' : $options["ysize"];
     	
		if( $model && $id ){
			$image = $this->renderFromModel($options);
			$optionsForRendering['type'] = $image['file_type'];
		} else if( $path ){
			$image = $this->renderFromFile($options);
			$optionsForRendering['type'] = $image['file_type'];
		}

		$imageData = $this->resampleImage($image['data'], $optionsForRendering);
        header("Content-Type: ".$image["file_type"]);
        echo($imageData);
		
	}
	
	function renderFromModel($options){

     	$model = ( empty($options["model"]) ) ? false : $options["model"];
     	$id = ( empty($options["id"]) ) ? false : $options["id"];

     	$fieldPath = ( empty($options["fieldPath"]) ) ? 'systempath' : $options["fieldPath"];
     	$fieldSize = ( empty($options["fieldSize"]) ) ? 'file_size' : $options["fieldSize"];
     	$fieldType = ( empty($options["fieldType"]) ) ? 'file_type' : $options["fieldType"];

		$modelName = get_class($model);
		
		
		$fields = array(
			$modelName.'.'.$fieldPath,
			$modelName.'.'.$fieldSize,
			$modelName.'.'.$fieldType,
		);
		
		$result = $model->find(array(
				'conditions' => array(
					$modelName.'.id' => $id
				),
				'fields' => $fields,
				'limit' => '1'
			)
		);
		
		$result = reset($result);
		
		if( !file_exists($result[$modelName][$fieldPath]) )
			return false;
		
		$data = $result[$modelName][$fieldPath];
		
		return array(
			'file_type' => $result[$modelName][$fieldType],
			'data' => $data
		);
	}
	
	function renderFromFile($options){

     	$path = ( empty($options["path"]) ) ? false : $options["path"];

     	$fieldType = ( empty($options["fieldType"]) ) ? 'file_type' : $options["fieldType"];

		if( !file_exists($path) )
			return false;
		
		$data = $path;
		
		$extension = array_reverse( explode(".", $path) );
		$extension = $extension[0];
		
		if( $extension == "png" )
			$mimeType = 'image/png';
		else if( $extension == "gif" )
			$mimeType = 'image/gif';
		else
			$mimeType = 'image/jpg';
		
		return array(
			'file_type' => $mimeType,
			'data' => $data
		);
	}
	
	/**
	 * resampleImage()
	 *
	 * Retorna imagem no formato binário
	 *
	 * @param $fileContent string Endereço físico até a imagem
	 */
	function resampleImage($fileContent, $options = array()){
		
		if( !empty($options["maxxsize"]) || !empty($options["maxxsize"]) ){
			$maxxsize = ( empty($options["maxxsize"]) ) ? 800 : $options["maxxsize"];
			$maxysize = ( empty($options["maxysize"]) ) ? 600 : $options["maxysize"];
		}
		
		$xsize = ( empty($options["xsize"]) ) ? '' : $options["xsize"];
		$ysize = ( empty($options["ysize"]) ) ? '' : $options["ysize"];
		
		$x = ( empty($maxxsize) ) ? $xsize : $maxxsize;
		$y = ( empty($maxysize) ) ? $ysize : $maxysize;
		
		$crop = ( empty($options["crop"]) ) ? false : $options["crop"];

        /*
         *
         * TRATAMENTO DA IMAGEM
         *
         */
        function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality) {
            // Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
            // Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
            // Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
            // Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable.
            //
            // Optional "quality" parameter (defaults is 3).  Fractional values are allowed, for example 1.5.
            // 1 = Up to 600 times faster.  Poor results, just uses imagecopyresized but removes black edges.
            // 2 = Up to 95 times faster.  Images may appear too sharp, some people may prefer it.
            // 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled.
            // 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
            // 5 = No speedup.  Just uses imagecopyresampled, highest quality but no advantage over imagecopyresampled.

            if (empty($src_image) || empty($dst_image)) { return false; }
            if ($quality <= 1) {
                $temp = imagecreatetruecolor ($dst_w + 1, $dst_h + 1);
                imagecopyresized ($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
                imagecopyresized ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
                imagedestroy ($temp);
            } elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
                $tmp_w = $dst_w * $quality;
                $tmp_h = $dst_h * $quality;
                $temp = imagecreatetruecolor ($tmp_w + 1, $tmp_h + 1);
                imagecopyresized ($temp, $src_image, $dst_x * $quality, $dst_y * $quality, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
                imagecopyresampled ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
                imagedestroy ($temp);
            } else {
                imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
            }
            return true;
        }

		$quality = 100;

		if( $options['type'] == 'image/png' ){
	        $im = imagecreatefrompng($fileContent); //criar uma amostra da imagem original
			$quality = floor($options['quality']/10);
			if( $quality >= 10 ) $quality = 9;
			if( $quality < 0 ) $quality = 1;
			
		} else if( $options['type'] == 'image/gif' ){
 	        $im = imagecreatefromgif($fileContent); //criar uma amostra da imagem original
		} else {
	        $im = imagecreatefromjpeg($fileContent); //criar uma amostra da imagem original
			$quality = $options['quality'];
		}

        $largurao = imagesx($im);// pegar a largura da amostra
        $alturao = imagesy($im);// pegar a altura da amostra
		$prop = $largurao/$alturao;
		$yMargin = 0;
		$xMargin = 0;

		/*
		 * CROP
		 */
        if( $crop === true or $crop == 'true' or $crop == 'crop' ){

			$largurad = $largurao;
			$alturad = $alturao;

			if( $alturad > $maxysize ){
				$alturad = $maxysize;
                $largurad = $alturad*$prop;// calcula a largura da imagem a partir da altura da miniatura
			}

			if( $largurad < $maxxsize ){
				$largurad = $maxxsize;
                $alturad = $largurad/$prop;// calcula a largura da imagem a partir da altura da miniatura
			}

			$leftOffset = ($largurad-$maxxsize)/2;//(200 - $largurao/2);
			$topOffset = ($alturad-$maxysize)/2;

			if( $topOffset > 0 )
				$yMargin = -$topOffset;

			if( $leftOffset > 0 )
				$xMargin = -$leftOffset;
//			if( )
//			echo $topOffset . ' - '. $maxysize;
//			echo '<br>';
//			echo '<br>';
			
			$x_mid = $largurad/2;  //horizontal middle
			$y_mid = $alturad/2; //vertical middle


			$topOffset = ($y_mid-($maxysize/2));
			$leftOffset = ($x_mid-($maxxsize/2));


		}
		else if(!empty($xsize) AND !empty($ysize)){
            $largurad = $xsize; // definir a altura da miniatura em px
            $alturad = $ysize;// calcula a largura da imagem a partir da altura da miniatura
        } else if(!empty($maxxsize) AND !empty($maxysize)){
            if($largurao > $maxxsize){
                $largurad = $maxxsize; // definir a altura da miniatura em px
                $alturad = ($alturao*$largurad)/$largurao;// calcula a largura da imagem a partir da altura da miniatura
            } else {
                $largurad = $largurao;
                $alturad = $alturao;
            }

            if($alturad > $maxysize){
                $alturad = $maxysize; // definir a altura da miniatura em px
                $largurad = ($largurao*$alturad)/$alturao;// calcula a largura da imagem a partir da altura da miniatura
            }
			$newimage_w = $largurad;
			$newimage_h = $alturad;

        } else if(!empty($xsize)){
            $largurad = $xsize; // definir a altura da miniatura em px
            $alturad = ($alturao*$largurad)/$largurao;// calcula a largura da imagem a partir da altura da miniatura
        } else if(!empty($ysize)){
            $alturad = $ysize; // definir a altura da miniatura em px
            $largurad = ($largurao*$alturad)/$alturao;// calcula a largura da imagem a partir da altura da miniatura
        } else {
            $largurad = 60; // definir a altura da miniatura em px
            $alturad = ($alturao*$largurad)/$largurao;// calcula a largura da imagem a partir da altura da miniatura
        }

		if( empty($newimage_h) OR empty($newimage_w) ){
			
			$newimage_w = $x;
			$newimage_h = $y;
		}

		if( empty($leftOffset) AND empty($topOffset) ){
			$leftOffset = 0;
			$topOffset = 0;
		}
		
		if( $largurad > $largurao ){
			$newimage_w = $largurao;
			$largurad = $largurao;
			$leftOffset = 0;
		}

		if( $alturad < $newimage_h ){
			$newimage_h = $alturao;
			$alturad = $alturao;
			$topOffset = 0;
		}
		
//		echo $newimage_h.' - '.$alturao;
			
//		$newimage_w = 20;
//		$newimage_h = 15;
//		$alturad = 20;
//		$largurad = 20;
//		$topOffset = -4.5;
		if( 0 ){
			echo 'offset: ';
			echo $leftOffset;
			echo 'x';
			echo $topOffset;
			echo '<br>';
			echo 'created_image: ';
			echo $newimage_w;
			echo 'x';
			echo $newimage_h;
			echo '<br>';
			echo 'original size: ';
			echo $largurao;
			echo 'x';
			echo $alturao;
			echo '<br>';
			echo 'new_size: ';
			echo $largurad;
			echo 'x';
			echo $alturad;
			echo '<br>';
			echo 'required image: ';
			echo $x;
			echo 'x';
			echo $y;
			echo '<br>';
			echo $options['type'];
			
			exit();
			
		}
        $nova = imagecreatetruecolor($newimage_w,$newimage_h);//criar uma imagem em branco

		// PNG ou GIF, ajusta transparência
		if( in_array($options['type'], array('image/png', 'image/gif') ) ){
			imagealphablending($nova, false);
			imagesavealpha($nova,true);
			$transparent = imagecolorallocatealpha($nova, 255, 255, 255, 127);
			imagefilledrectangle($nova, 0, 0, $largurad, $alturad, $transparent);
		}

        imagecopyresampled($nova,$im, $xMargin, $yMargin , $leftOffset,$topOffset,$largurad,$alturad,$largurao,$alturao);//copiar sobre a imagem em branco a amostra diminuindo conforma as especificações da miniatura

        /* Watermark */
        if( $options['watermark'] ){
            $watermark = imagecreatefrompng( $options['watermark'] );
            $watermark_width = imagesx($watermark);  
            $watermark_height = imagesy($watermark);
            $dest_x = $largurad - $watermark_width - 35;
            $dest_y = $alturad - $watermark_height - 10;

            imagecopy($nova, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);
			imagesavealpha($nova, true);

            imagedestroy($watermark);            
		}

        ob_start();

			if( $options['type'] == 'image/png' )
	        	imagepng($nova, null, $quality);
			else if( $options['type'] == 'image/gif' )
	    		imagegif($nova);
			else
	        	imagejpeg($nova, null, $quality);

            $content = ob_get_contents();

        ob_end_clean();

        $result = $content;

        return $result;

	}
	


}
?>