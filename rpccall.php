<?php
function call($rpc, $method, array $params) {
	$data_in = xmlrpc_encode_request($method, $params);
	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL            => $rpc,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 10,
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
