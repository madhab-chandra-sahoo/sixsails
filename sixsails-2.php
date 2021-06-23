<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// assumption priorities in ascending order
function breakIntoSprint($input_array){
	$priority_wise_tasks = [];	
	$sprint = 1;
	$prev_sprint = 0;
	$max_sprint_days = 10;
	$current_priority = 1;

	foreach ($input_array['priorities'] as $key => $value) {
		// Assign developer based on key is odd or even
		$index = $key;
		if(++$index % 2 == 0){
			$dev = 2;
			$prev_dev = 1;
		}else{
			$dev = 1;
			$prev_dev = ($key > 0) ? 2:0;
		}

		//if empty developer sprint days then set to 0
		if(empty($dev_sprint_days[$dev]))
			$dev_sprint_days[$dev] = $remaining_days[$dev] = 0;

		$dev_sprint_days[$dev] += $input_array['efforts'][$key];

		//If current task is added to other developer check key
		$added = false;

		//If current developer days is greater than max sprint days then add Miscellaneous task to max sprint days
		if($dev_sprint_days[$dev] > $max_sprint_days){
			$priority_wise_tasks['developer '.$dev]['sprint '.$sprint][$max_sprint_days] = "Miscellaneous tasks";
			$dev_sprint_days[$dev] = $input_array['efforts'][$key];
			$prev_sprint = $sprint;
			++$sprint; //increase sprint value

			//if remaining days of other developer is greater than required days & priority of task is same then add task to other developer
			if($remaining_days[$prev_dev] > $input_array['efforts'][$key] && $prev_dev > 0 && $current_priority == $value){
				$dev_sprint_days[$prev_dev] += $input_array['efforts'][$key];
				$priority_wise_tasks['developer '.$prev_dev]['sprint '.$prev_sprint][$dev_sprint_days[$prev_dev]] = $input_array['tasks'][$key];
				$added = true;
			}

			//Add Miscellaneous task to previous developer
			if(empty($priority_wise_tasks['developer '.$prev_dev]['sprint '.$prev_sprint][$max_sprint_days]) && $dev_sprint_days[$prev_dev] < $max_sprint_days && $prev_sprint > 0){
				$priority_wise_tasks['developer '.$prev_dev]['sprint '.$prev_sprint][$max_sprint_days] = "Miscellaneous tasks";
				$dev_sprint_days[$prev_dev] = 0;
			}
		}

		//Adding miscellaneous task to dev if no task done for the prediod based upon other developer days
		if($prev_dev > 0 && $dev_sprint_days[$dev] > 0 && $dev_sprint_days[$prev_dev] > 0 && $dev_sprint_days[$dev] > $dev_sprint_days[$prev_dev]){
			if(empty($priority_wise_tasks['developer '.$dev]['sprint '.$sprint][$dev_sprint_days[$prev_dev]]) 
				&& isset($priority_wise_tasks['developer '.$dev]['sprint '.$sprint]) && max(array_keys($priority_wise_tasks['developer '.$dev]['sprint '.$sprint])) < $dev_sprint_days[$prev_dev]){
				$dev_sprint_days[$dev] = $dev_sprint_days[$prev_dev];

				$priority_wise_tasks['developer '.$dev]['sprint '.$sprint][$dev_sprint_days[$dev]] = "Miscellaneous tasks";
				$dev_sprint_days[$dev] += $input_array['efforts'][$key];
			}
		}

		//If sprint does not exists then reset dev days to current task days
		if(empty($priority_wise_tasks['developer '.$dev]['sprint '.$sprint])){
			$dev_sprint_days[$dev] = $input_array['efforts'][$key];
		}

		$remaining_days[$dev] = $max_sprint_days - $dev_sprint_days[$dev];

		//chnage priority value
		$current_priority = $value;
		
		if(!$added)
			$priority_wise_tasks['developer '.$dev]['sprint '.$sprint][$dev_sprint_days[$dev]] = $input_array['tasks'][$key];

		//If last task & developer has few days for sprint complete add Miscellaneous task to remaining days
		if($index == count($input_array['priorities']) && empty($priority_wise_tasks['developer '.$dev]['sprint '.$sprint][$max_sprint_days]))
			$priority_wise_tasks['developer '.$dev]['sprint '.$sprint][$max_sprint_days] = "Miscellaneous tasks";
		
	}

	// echo "<pre>";print_r($priority_wise_tasks);echo "</pre>";
	foreach ($priority_wise_tasks as $developer => $sprints) {
		echo "<b>".ucfirst($developer)."</b><br><br>";
		foreach ($sprints as $sprint => $days) {
			$prev_day = 0;
			foreach ($days as $day => $task) {
				if(($prev_day + 1) == $day)
					echo strtoupper($sprint)." - Days ".$day." - ".$task."<br>";
				else
					echo strtoupper($sprint)." - Days ".($prev_day + 1)." - ".$day." - ".$task."<br>";
				$prev_day = $day;
			}
			echo "<br>";
		}
		echo "<br>";
	}
}

function checkInputTypeToGetData($input){
	if(is_file($input)){
		$file = fopen($input, "r");
		while(!feof($file))
		{
			list($temp_data['tasks'][],$temp_data['efforts'][],$temp_data['priorities'][]) = array_map('trim',explode(",", fgets($file)));
		}
		fclose($file);
		$response = breakIntoSprint($temp_data);
	}else{
		$response = "Not a valid input<br/>";
	}

	return $response;

}

echo checkInputTypeToGetData('test_input-2.txt'); // file
?>