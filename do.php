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

$params = array($in->procname, false);

switch ($in->action) {
	// Singular process management
	case 'start':
		send(call($rpc, 'supervisor.startProcess', $params), true);
	break;
	case 'restart':
		$results = array(
			'stop'  => call($rpc, 'supervisor.stopProcess', $params),
			'start' => call($rpc, 'supervisor.startProcess', $params),
		);
		send($results, true);
	break;
	case 'stop':
		send(call($rpc, 'supervisor.stopProcess', $params), true);
	break;
	// Group process management
	case 'startgroup':
		send(call($rpc, 'supervisor.startProcessGroup', $params), true);
	break;
	case 'restartgroup':
		$results = array(
			'stop'  => call($rpc, 'supervisor.stopProcessGroup', $params),
			'start' => call($rpc, 'supervisor.startProcessGroup', $params),
		);
		send($results, true);
	break;
	case 'stopgroup':
		send(call($rpc, 'supervisor.stopProcessGroup', $params), true);
	break;
	// Log management (currently doesn't work?)
	case 'viewlog':
		$log_params = array($in->procname, 0, 1024);
		$results = array(
			'stdout' => call($rpc, 'supervisor.tailProcessStdoutLog', $log_params),
			'stderr' => call($rpc, 'supervisor.tailProcessStderrLog', $log_params),
		);
		send($results, false);
	break;
	case 'clearlog':
		send(call($rpc, 'supervisor.clearProcessLogs', array($in->procname)), true);
	break;
	default:
		fail("Invalid action: {$in->action}", true);
	break;
}

function send($result, $redir = false) {
	if ($redir) {
		header("Location: /");
		exit;
	}
	echo json_encode(array(
		'success' => true,
		'result'  => $result,
	));
}

function fail($reason, $redir = false) {
	if ($redir) {
		header("Location: /");
		exit;
	}
	echo json_encode(array(
		'success' => false,
		'result'  => $reason,
	));
	exit;
}
