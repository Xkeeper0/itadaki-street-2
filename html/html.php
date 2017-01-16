<?php


function pageHeader($title = "") {
	$header	= file_get_contents("html/header.html");
	$title	= "the itadaki street 2 project". ($title ? " - ". $title : "");
	return str_replace('{$title}', $title, $header);
}

function pageFooter() {
	$footer	= file_get_contents("html/footer.html");
	return $footer;
}
