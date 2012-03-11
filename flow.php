<?php
	
	class Flow {

		var $title   = "Le nid du Geek";
		var $url = "/flow/";
		var $datadir = "data/";
    	var $imgtype = array("image/jpeg", "image/gif", "image/png");
		var $finfo;

		var $gallery = false;
		var $galleryItem = array();
		var $photo = false;
		var $exif = array();
		var $galitem = array();

		var $thumbdir  = "thumb/";
		var $quality   = 75;
		var $optimopts = "--strip-all";
		var $colsize   = 3;

		// ---- ----
		// Flow, constructor
		function Flow($_params) {
			
			$this->finfo = finfo_open(FILEINFO_MIME_TYPE);

			// route
			if(isSet($_params['g']) || isSet($_params['p'])) {

				if(isSet($_params['g'])) {
					$this->gallery = urldecode(strip_tags($_params['g']));
					if(!is_dir($this->datadir . $this->gallery)) {
						$this->gallery = false;
						$this->makeGalleryIndex();
					} else {
						$this->makePhotoIndex();
					}
				}

				if(isSet($_params['p'])) {
					$this->photo = urldecode(strip_tags($_params['p']));
					if(!is_file($this->datadir . $this->gallery . "/" . $this->photo) && in_array(finfo_file($this->finfo,$this->datadir . $this->gallery . "/" . $this->photo),$this->imgtype)) {
						$this->photo = false;
						$this->makeGalleryIndex();
					} else {
						$this->getExif();
					}
				}
			} else {
				$this->makeGalleryIndex();
			}

			// navigation bar
			$this->nav = "<a href=\"".$this->url."\">$this->title</a>";
			if($this->gallery) {
				if($this->photo) {
					$this->nav .= " > <a href=\"?g=".$this->gallery."\" />". $this->gallery ."</a>";
				} else {	
					$this->nav .= " > ". $this->gallery;
				}
			}
		}
		
		// ---- ----
		// randomThumb, display a random file from a directory
		function randomThumb($_dir) {

			$_dir .= "/";
			$item = array();

			if(is_dir($this->datadir . $_dir)) {
				if($dh = opendir($this->datadir . $_dir)) {
					while(($file = readdir($dh)) !== false) {
						if(is_file($this->datadir . $_dir . $file)) {
							if(in_array(finfo_file($this->finfo,$this->datadir . $_dir . $file),$this->imgtype)) {
								array_push($item,$file);
							 }
						}
					}
					closedir($dh);
				}
			}
			return($item[rand(0, sizeof($item)-1)]);
		}

		// ---- ----
		// makeGalleryIndex, read $datadir and print a random thumbnail for each gallery
		private function makeGalleryIndex() {

			if (is_dir($this->datadir)) {
				if ($dh = opendir($this->datadir)) {
					while (($dir = readdir($dh)) !== false) {
						if(is_dir($this->datadir . $dir) && ($dir != ".") && ($dir != "..")) {
							if($gh = opendir($this->datadir . $dir)) {
								if(is_file($this->datadir . $dir . "/config.ini")) {
	
									// configuration
									$iniConfig = parse_ini_file($this->datadir . $dir . "/config.ini", true);

									$this->galitem[$iniConfig['configuration']['name']]['description'] = $iniConfig['configuration']['description'];
									$this->galitem[$iniConfig['configuration']['name']]['path'] 		= urlencode($dir);
									$this->galitem[$iniConfig['configuration']['name']]['thumb'] 		= $this->randomThumb($dir);
								}
							}
						}
					}
					closedir($dh);
				}
			}
			return $item;
		}

		// ---- ----
		//
		function makePhotoIndex() {

			if(is_dir($this->datadir . $this->gallery)) {
				if($dh = opendir($this->datadir . $this->gallery)) {
					while(($file = readdir($dh)) !== false) {
						if(is_file($this->datadir . $this->gallery ."/". $file)) {
							if(in_array(finfo_file($this->finfo,$this->datadir . $this->gallery ."/". $file),$this->imgtype)) {
								array_push($this->galleryItem,$file);
							 }
						}
					}
					closedir($dh);
				}
			}
//
//              $i=0;
//
//              while(false !== ($file = readdir($handle))) {
//
//                  // we have a image file
//                  if(in_array(finfo_file($fileinfo,$datadir."/".$file),$imgtype)) {
//
//                          $exif_data = exif_read_data($datadir."/".$file);
//
//                      $slimtitle = ($exif_data['Model'] != "") ? "<br />Camera: ". $exif_data['Model'] : "<br />Camera: n/a";
//                      $slimtitle .= ($exif_data['ExposureTime'] != "") ? "<br />Speed: ". $exif_data['ExposureTime'] : "<br />Speed: n/a";
//                      $slimtitle .= ($exif_data['COMPUTED']['ApertureFNumber'] != "") ? "<br />Aperture: ". $exif_data['COMPUTED']['ApertureFNumber'] : "<br />Aperture: n/a";
//                      $slimtitle .= ($exif_data['ISOSpeedRatings'] != "") ? "<br />ISO: ". $exif_data['ISOSpeedRatings'] : "<br />ISO: n/a";
//
//                      $imgtitle = "img title";
//
//                      // print thumbnail
//                      echo "<a href=\"".$datadir."/".$file."\" rel=\"lightbox-thumb\" title=\"".strtoupper($imgtitle)." ".$slimtitle."\"><img src=\"".thumbnail($datadir,$file)."\" /></a>";
//
//                      if($i++ == $colsize) {
//                          echo "<br />";
//                          $i=0;
//                      }
//                  }
//              }
//          }
//
//          closedir($handle);
		}

		// ---- ----
		// thumbnail
		function thumbnail($_file, $_dir = NULL) {

			if($_dir == NULL) $_dir = $this->gallery;

			if(!is_dir($this->datadir . $_dir ."/". $this->thumbdir)) {
				mkdir($this->datadir . $_dir ."/". $this->thumbdir);
			}

			// thumbnail already exists ?
			if(!is_file($this->datadir . $_dir ."/". $this->thumbdir . $_file)) {
	
				$imgfile = new Imagick($this->datadir . $_dir ."/". $_file);

				if($imgfile->getImageHeight() <= $imgfile->getImageWidth()) {
					$exec="convert -resize x280 -resize '280x<'";
				} else {
					$exec="convert -resize 280x -resize 'x280<'";
				}

				exec($exec." -gravity center -crop 280x280+0+0 +repage -quality ".$this->quality." ". $this->datadir . $_dir ."/". $_file ." ". $this->datadir . $_dir ."/". $this->thumbdir . $_file." ");

				exec("jpegoptim -o ".$this->optimopts." ". $this->datadir . $_dir ."/". $this->thumbdir . $_file);

				$imgfile->destroy();
			}

			return $this->datadir . $_dir ."/". $this->thumbdir . $_file;
    	}

		// ---- ----
		// getExif, get EXIF data from an image file
		private function getExif() {

			$exif_data = exif_read_data($this->datadir.$this->gallery."/".$this->photo);

			$this->exif['camera'] 	= ($exif_data['Model'] != "") 						? $exif_data['Model'] 						: "n/a";
			$this->exif['speed'] 	= ($exif_data['ExposureTime'] != "") 				? $exif_data['ExposureTime'] 				: "n/a";
			$this->exif['aperture'] = ($exif_data['COMPUTED']['ApertureFNumber'] != "") ? $exif_data['COMPUTED']['ApertureFNumber'] : "n/a";
			$this->exif['iso'] 		= ($exif_data['ISOSpeedRatings'] != "") 			? $exif_data['ISOSpeedRatings'] 			: "n/a";
		}

	}
?>
