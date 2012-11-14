<?php defined('SYSPATH') OR die('No direct script access.');

$config = array();

$config['area'] = array(
	// TODO
);

$energy['energy'] = array(
	// TODO
);

$config['force'] = array(
	// TODO
);

$config['length'] = array(
	'centimeters' => '0.01',
	'feet' => '0.3048',
	'inches' => '0.3048 / 12.0',
	'kilometers' => '1000.0',
	'leagues' => '4828.0417',
	'meters' => '1.0',
	//'microinches' => '2.54e-08',
	'miles' => '0.3048 * 5280.0',
	'millimeters' => '0.001',
	'nautical leagues' => '5556.0',
	'yards' => '0.9144',
);

$config['power'] = array(
	// TODO
);

$config['pressure'] = array(
	// TODO
);

$config['temperature'] = array(
	// TODO
);

$config['volume'] = array(
	'centimeters' => '0.000001',
	'decimeters' => '0.001',
	'dekameters' => '1000.0',
	'feet' => '(0.003785411784 / 231) * 1728',
	'inches' => '0.003785411784/231',
	//'kilometers' => '1.0e+9',
	'meters' => '1',
	'miles' => '(((0.003785411784 / 231) * 1728) * 43560) * 3379200',
	//'micrometers' => '1.0e-18',
	//'millimeters' => '1.0e-9',
	'yards' => '((0.003785411784 / 231) * 1728) * 27',
);

$config['weight'] = array(
	'centigrams' => '0.00001',
	'decigrams' => '0.0001',
	'dekagrams' => '0.01',
	'drams' => '0.45359237 / 256.0',
	'grains' => '0.00006479891',
	'grams' => '0.001',
	'hectograms' => '0.1',
	'kilograms' => '1.0',
	'megagrams' => '1000.0',
	//'micrograms' => '1.0e-9',
	'milligrams' => '0.000001',
	'ounces' => '0.45359237 / 16.0',
	'pounds' => '0.45359237',
	'tons' => '1000.0',
);

return $config;
?>