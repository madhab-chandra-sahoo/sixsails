<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('WEIGHT_OF_BALLS', ['red' => 1, 'yellow' => 0.5, 'green' => 0.25]);
function getFormattedData($input_array){
	$unique_data_with_count = array_count_values($input_array);
	$total_balls = array_sum($unique_data_with_count);

	$output_data = array();

	foreach (WEIGHT_OF_BALLS as $ball => $weight) {
		$weights[$ball] = $weight * $unique_data_with_count[$ball];
	}

	$total_weight = array_sum($weights);

	foreach ($unique_data_with_count as $key => $value) {
		$output_data['counts'][] = 'Number of '.$key.' balls: '.$value."<br>";

		$output_data['average'][] = 'Average of '.$key.' balls: '.$value / $total_balls."<br>";

		$output_data['weighted_average'][] = 'Weighted average of '.$key.' balls: '.number_format($weights[$key] / $total_weight, 2)."<br>";

	}
	$response_data[] = implode($output_data['counts']);
	$response_data[] = implode($output_data['average']);
	$response_data[] = implode($output_data['weighted_average']);

	return implode("<br>", $response_data);
}

function checkInputTypeToGetData($input){
	if(is_file($input)){
		$file = fopen($input, "r");
		while(!feof($file))
		{
			$temp_data[] = checkInputTypeToGetData(fgets($file));
		}
		fclose($file);
		$response = implode("<br>", $temp_data);
	}elseif(is_string($input)){
		$array = array_map('trim', explode(',', $input));
		$response = getFormattedData($array);
	}elseif(is_array($input)){
		$response = getFormattedData($input);
	}else{
		$response = "Not a valid input<br/>";
	}

	return $response;

}

echo checkInputTypeToGetData('test_input-1.txt'); // file or string or array
?>