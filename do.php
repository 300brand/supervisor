<?php
require('./machines.php');
require('./rpccall.php');


$in = filter_input_array(INPUT_GET, array(
	'server'   => FILTER_SANITIZE_STRING,
	'procname' => FILTER_SANITIZE_STRING,
	'action'   => FILTER_SANITIZE_STRING,
));
$in = (object)$in;

header('Content-Type: application/json');

// Find the RPC address from the supplied alias
$rpc = '';
foreach ($machines as $m) {
	if ($m['alias'] == $in->server) {
		$rpc = $m['rpc'];
	}
}
if ($rpc == '') {
	fail("Invalid server: {$in->server}");
}

$params = array(
	$in->procname,
	true,
);

switch ($in->action) {
	case 'start':
		send(call($rpc, 'supervisor.startProcess', $params));
	break;
	case 'restart':
		$results = array(
			'stop'  => call($rpc, 'supervisor.stopProcess', $params),
			'start' => call($rpc, 'supervisor.startProcess', $params),
		);
		send($results);
	break;
	case 'stop':
		send(call($rpc, 'supervisor.stopProcess', $params));
	break;
	default:
		fail("Invalid action: {$in->action}");
	break;
}

function send($result) {
	header("Location: /");
	exit;
	echo json_encode(array(
		'success' => true,
		'result'  => $result,
	));
}

function fail($reason) {
	header("Location: /");
	exit;
	echo json_encode(array(
		'success' => false,
		'result'  => $reason,
	));
}
