<?php
// header('Content-Type: text/html; charset=utf-8');
error_reporting ( 0 );
include ("SimpleImage.php");
include ("functions.php");
$image = isset ( $_GET ['img'] ) ? $_GET ['img'] : "";
$width = isset ( $_GET ['w'] ) ? $_GET ['w'] : "";
$height = isset ( $_GET ['h'] ) ? $_GET ['h'] : "";
$quality = isset ( $_GET ['q'] ) ? $_GET ['q'] : "";
$direct_output = isset ( $_GET ['o'] ) ? $_GET ['o'] : 1;

$base_url = "http://localhost/cdn";
// $base_url = "http://cdn.edowntown.me";
$base_directory = ".";
$default_error_image = $base_url . '/originals/std-img.jpg';

if ($image == "") {
	if ($direct_output) {
		$image_handler = new abeautifulsite\SimpleImage ( $base_directory . '/originals/std-img.jpg' );
	} else {
		echo $default_error_image;
	}
	return;
}

$image = base64_decode ( $image );

if (! is_numeric ( $width )) {
	$width = - 1;
}
if (! is_numeric ( $height )) {
	$height = - 1;
}
if (! is_numeric ( $quality ) || $quality < 0 || $quality > 1) {
	$quality = 70;
}

// Download Image
$filenameIn = $image;

$ext = strtolower ( pathinfo ( $filenameIn, PATHINFO_EXTENSION ) );
$hashed_file_name = hash ( "md5", $filenameIn ) . '.' . $ext;

$original_file = $base_directory . '/originals/' . $hashed_file_name;

if (! file_exists ( $original_file )) {
	// Download the file
	
	$original_file = $base_directory . '/originals/' . $hashed_file_name;
	if (get_http_response_code ( $filenameIn ) != "200") {
		$original_file = $base_directory . '/originals/std-img.jpg';
		$hashed_file_name = "std-img.jpg";
	} else {
		try {
			$contentOrFalseOnFailure = file_get_contents ( $filenameIn );
			$byteCountOrFalseOnFailure = file_put_contents ( $original_file, $contentOrFalseOnFailure );
		} catch ( Exception $ex ) {
			$original_file = $base_directory . '/originals/std-img.jpg';
		}
	}
}

// Reset Width & Height
if ($width == 0) {
	$width == - 1;
}

if ($height == 0) {
	$height = - 1;
}

try {
	try {
		$image_handler = new abeautifulsite\SimpleImage ( $original_file );
	} catch ( Exception $ex ) {
		$image_handler = new abeautifulsite\SimpleImage ( $base_directory . '/originals/std-img.jpg' );
	}
	
	process_request ( $image_handler, $direct_output, $width, $height, $quality, $base_directory, $base_url, $hashed_file_name );
} catch ( Exception $ex ) {
	$image_handler = new abeautifulsite\SimpleImage ( $base_directory . '/originals/std-img.jpg' );
	process_request ( $image_handler, $direct_output, $width, $height, $quality, $base_directory, $base_url, $hashed_file_name );
}

?>