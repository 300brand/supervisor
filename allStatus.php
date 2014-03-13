<?php
$machines = array(
	array(
		'alias' => "Sable",
		'rpc'   => "127.0.0.1:9001/RPC2",
	),
	array(
		'alias' => "rpi-0000.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.65:9001/RPC2",
	),
	array(
		'alias' => "rpi-0001.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.66:9001/RPC2",
	),
	array(
		'alias' => "rpi-0002.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.67:9001/RPC2",
	),
	array(
		'alias' => "rpi-0003.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.68:9001/RPC2",
	),
	array(
		'alias' => "rpi-0004.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.69:9001/RPC2",
	),
	array(
		'alias' => "rpi-0005.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.70:9001/RPC2",
	),
	array(
		'alias' => "rpi-0006.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.71:9001/RPC2",
	),
	array(
		'alias' => "rpi-0007.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.72:9001/RPC2",
	),
	array(
		'alias' => "rpi-0008.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.73:9001/RPC2",
	),
	array(
		'alias' => "rpi-0009.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.74:9001/RPC2",
	),
	array(
		'alias' => "rpi-000a.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.75:9001/RPC2",
	),
	array(
		'alias' => "rpi-000b.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.76:9001/RPC2",
	),
	array(
		'alias' => "rpi-000c.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.77:9001/RPC2",
	),
	array(
		'alias' => "rpi-000d.node.pipod00.coverage.net",
		'rpc'   => "192.168.20.78:9001/RPC2",
	),
);

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

function call($rpc, $method, array $params) {
	$data_in = xmlrpc_encode_request($method, $params);
	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL            => $rpc,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 1,
		CURLOPT_POSTFIELDS     => $data_in,
		CURLOPT_HTTPHEADER     => array(
			"Content-Type: text/xml",
			"Content-Length: " + strlen($data_in),
		),
	));
	$data_out = curl_exec($ch);
	if (curl_errno($ch)) {
		return null;
	} else {
		curl_close($ch);
		return xmlrpc_decode($data_out);
	}
}
