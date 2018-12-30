<?php

function get_ec_config($name) {
    $properties = parse_ini_file(APPPATH.'config/ducksmanager.ini', true);
	
	return $properties[$name];
} 