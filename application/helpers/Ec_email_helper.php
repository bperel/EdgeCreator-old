<?php

function get_admin_email() {
    $properties = parse_ini_file(BASEPATH.'../application/config/ducksmanager.ini', true);
	
	return $properties['dm_email'];
} 