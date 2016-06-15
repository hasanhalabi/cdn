<?php
//header('Content-Type: text/html; charset=utf-8');
include ("SimpleImage.php");
$image = isset($_GET['img']) ? $_GET['img'] : "";
$width = isset($_GET['w']) ? $_GET['w'] : "";
$height = isset($_GET['h']) ? $_GET['h'] : "";
$quality = isset($_GET['q']) ? $_GET['q'] : "";
$direct_output = isset($_GET['o']) ? $_GET['o'] : 1;

$base_url = "http://fourhpc/cdn";
$base_directory = ".";
$default_error_image = $base_url . '/originals/std-img.jpg';

if ($image == "") {
	if ($direct_output) {
		$image_handler = new abeautifulsite\SimpleImage($base_directory . '/originals/std-img.jpg');
	} else {
		echo $default_error_image;
	}
	return;
}


$image = base64_decode($image);


if (!is_numeric($width)) {
	$width = -1;
}
if (!is_numeric($height)) {
	$height = -1;
}
if (!is_numeric($quality) || $quality < 0 || $quality > 1) {
	$quality = 70;
}

// Download Image
$filenameIn = $image;

$ext = strtolower(pathinfo($filenameIn, PATHINFO_EXTENSION));
$hashed_file_name = hash("md5", $filenameIn) . '.' . $ext;

$original_file = $base_directory . '/originals/' . $hashed_file_name;

if (!file_exists($original_file)) {
	// Download the file

	$original_file = $base_directory . '/originals/' . $hashed_file_name;
	if (get_http_response_code($filenameIn) != "200") {
		$original_file = $base_directory . '/originals/std-img.jpg';
		$hashed_file_name = "std-img.jpg";
	} else {
		try {
			$contentOrFalseOnFailure = file_get_contents($filenameIn);
			$byteCountOrFalseOnFailure = file_put_contents($original_file, $contentOrFalseOnFailure);

		} catch(Exception $ex) {
			$original_file = $base_directory . '/originals/std-img.jpg';
		}
	}
}

// Reset Width & Height
if ($width == 0) {
	$width == -1;
}

if ($height == 0) {
	$height = -1;
}

try {
	$image_handler = new abeautifulsite\SimpleImage($original_file);
} catch(Exception $ex) {
	$image_handler = new abeautifulsite\SimpleImage($base_directory . '/originals/std-img.jpg');
}

if ($width < 0 && $height < 0) {
	if ($direct_output) {
		$image_handler -> output();
	} else {
		echo $base_url . '/originals/' . $hashed_file_name;
	}
	return;
}

$info = $image_handler -> get_original_info();

$old_width = $info['width'];
$old_height = $info['height'];

if ($width == -1 && $height > 0) {
	$new_height = $height;
	$new_width = round(($new_height * $old_width) / $old_height);

	$image_handler -> fit_to_height($new_height);
} elseif ($width > 0 && $height == -1) {
	$new_width = $width;
	$new_height = round(($new_width * $old_height) / $old_width);

	$image_handler -> fit_to_width($new_width);
} else {
	$new_width = $width;
	$new_height = $height;

	$image_handler -> thumbnail($new_width, $new_height);
}

$directory_to_save_in = "/$new_width-x-$new_height/";
$resized_image_path = $base_directory . $directory_to_save_in . $hashed_file_name;
$resized_image_url = $base_url . $directory_to_save_in . $hashed_file_name;

if (!file_exists($base_directory . $directory_to_save_in)) {
	mkdir($base_directory . $directory_to_save_in, 0755);
}

$image_handler -> save($resized_image_path, $quality);

if ($direct_output) {
	$image_handler -> output();
} else {
	echo $resized_image_url;
}

function get_http_response_code($url) {
	if ($url == "")
	{
		return "no URL";
	}
	$headers = get_headers($url);
	return substr($headers[0], 9, 3);
}
?>