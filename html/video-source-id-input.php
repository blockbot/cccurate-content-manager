<?php
	$video_source = get_post_meta(get_the_ID(), 'Video Source');
	$video_id = get_post_meta(get_the_ID(), $video_source[0] . ' ID');
?>

<div id="curate-video-id">
<?php //diebug(!empty($video_id)); ?>
	<label>Video ID</label>
	<input type="text" 
		   name="curate-video-id" 
		   placeholder="Video ID"  
		   <?php echo !empty($video_id) ? "value='" . $video_id[0] . "'" : "" ?>>

</div>