<?php
function process_request($image_handler, $direct_output, $width, $height, $quality, $base_directory, $base_url, $hashed_file_name) {
	if ($width < 0 && $height < 0) {
		if ($direct_output) {
			$image_handler->output ();
		} else {
			echo $base_url . '/originals/' . $hashed_file_name;
		}
		return;
	}
	
	$info = $image_handler->get_original_info ();
	
	$old_width = $info ['width'];
	$old_height = $info ['height'];
	
	if ($width == - 1 && $height > 0) {
		$new_height = $height;
		$new_width = round ( ($new_height * $old_width) / $old_height );
		
		$image_handler->fit_to_height ( $new_height );
	} elseif ($width > 0 && $height == - 1) {
		$new_width = $width;
		$new_height = round ( ($new_width * $old_height) / $old_width );
		
		$image_handler->fit_to_width ( $new_width );
	} else {
		$new_width = $width;
		$new_height = $height;
		
		$image_handler->thumbnail ( $new_width, $new_height );
	}
	
	$directory_to_save_in = "/$new_width-x-$new_height/";
	$resized_image_path = $base_directory . $directory_to_save_in . $hashed_file_name;
	$resized_image_url = $base_url . $directory_to_save_in . $hashed_file_name;
	
	if (! file_exists ( $base_directory . $directory_to_save_in )) {
		mkdir ( $base_directory . $directory_to_save_in, 0755 );
	}
	
	$image_handler->save ( $resized_image_path, $quality );
	
	if ($direct_output) {
		$image_handler->output ();
	} else {
		echo $resized_image_url;
	}
}
function get_http_response_code($url) {
	if ($url == "") {
		return "no URL";
	}
	$headers = get_headers ( $url );
	return substr ( $headers [0], 9, 3 );
}