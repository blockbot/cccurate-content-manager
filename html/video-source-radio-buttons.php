<?php 

	$video_sources = array(
		"Instagram",
		"Vimeo",
		"Vine",
		"Youtube"
	);
	$video_source = get_post_meta(get_the_ID(), 'Video Source');

?>

<div id="curate-video-sources">

	<?php foreach ($video_sources as $source): ?>
		
		<div class="curate-video-source-inputs">
			
			<input type="radio" 
				   name="curate-video-sources" 
				   id="curate-video-source-<?php echo $source; ?>" 
				   value="<?php echo $source; ?>"
				   <?php echo $video_source[0] == $source ? "checked='checked'" : "" ?>>

			<label for="curate-video-source-<?php echo $source; ?>">
				<?php echo $source; ?>
			</label>
		
		</div>

	<?php endforeach; ?>

</div>