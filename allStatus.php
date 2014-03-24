<?php
require('./machines.php');
require('./rpccall.php');

$statuses = array();

foreach ($machines as $machine) {
	$status = $machine;
	$state = call($machine['rpc'], "supervisor.getState", array());
	if (!is_array($state)) {
		$state = array(
			'statecode' => 2,
			'statename' => "FATAL",
		);
	}
	$status['state'] = $state;
	$status['processes'] = call($machine['rpc'], "supervisor.getAllProcessInfo", array());
	$statuses[] = $status;
}

header("Content-Type: application/json");
echo json_encode($statuses);

