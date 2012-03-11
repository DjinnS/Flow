<?php

	include "flow.php";

	$flow = new Flow($_GET);
?>
<html>
	<head>
		<title><?php echo $flow->title; ?></title>

		<link rel="stylesheet" href="css/styles.css" type="text/css" media="screen" />

		<script src="js/jquery.js" type="text/javascript"></script>
		<script src="js/jquery.lazyload.js" type="text/javascript"></script>
	</head>

	<body>
		<div id="ontop">
			<h1>
				<?php
					echo $flow->nav;
				?>
			</h1>
		</div>

		<div id="content">
		<?php

			// print gallery index
			if(!$flow->gallery || !$flow->photo) {

				$i=0;
				foreach($flow->galitem as $key => $gallery) {
		?>
			<div class="col<?php echo $i; ?>">
			<div>
				<a href="?g=<?php echo $gallery['path']; ?>" alt="<?php echo $gallery['description']; ?>">
					<img class="lazy" src="css/loading.jpg" data-original="<?php echo $flow->thumbnail($flow->randomThumb($gallery['path']),$gallery['path']); ?>"  width="280" heigh="280" />
				</a>
			</div>
			<div class="desc"><span><?php echo $key; ?></span></div>
			</div>
		<?php
					if($i++ >= $flow->colsize) { $i=0; }
				}
			}

			// print photo
			if($flow->photo && $flow->gallery) {
		?>
		<div>
			<div class="photo">
				<img class="lazy" src="css/loading.jpg" data-original="<? echo $flow->datadir.$flow->gallery."/".$flow->photo; ?>" />
			</div>
			<div class="meta">
				<img src="css/camera.png" /> <?php echo $flow->exif['camera']; ?>  
				<img src="css/speed.png" /> <?php echo $flow->exif['speed']; ?> 
				<img src="css/aperture.png" /> <?php echo $flow->exif['aperture']; ?> 
				<img src="css/iso.png" /> <?php echo $flow->exif['iso']; ?>
			</div>
		</div>
		<?php
			}

			// print gallery
			if($flow->gallery && !$flow->photo) {

                $i=0;

                foreach($flow->galleryItem as $key => $photo) {
		?>
			<div class="col<?php echo $i; ?>">
				<a href="?g=<?php echo $flow->gallery; ?>&p=<?php echo $photo; ?>">
					<img class="lazy" src="css/loading.jpg" data-original="<?php echo $flow->thumbnail($photo,$flow->gallery); ?>" />
				</a>
			</div>
		<?php
					if($i++ == $flow->colsize) { $i=0; }
				}
			}
		?>
		</div>

    	<div id="onbottom">
	        <p>Powered by <a href="https://github.com/DjinnS/Flow">Flow</a></p>
	    </div>

		<script type="text/javascript">
		<!--
			$("img").lazyload({effect: "fadeIn"});
		-->
		</script>
	</body>
</html>
