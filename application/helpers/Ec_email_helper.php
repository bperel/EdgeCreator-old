<?php

function get_admin_email() {
    $properties = parse_ini_file(APPPATH.'config/ducksmanager.ini', true);
	
	return $properties['dm_email'];
} 