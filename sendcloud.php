<?php

$xsmtpapi = '{"to": [';
$host = '';
$db_name = '';
$username = 'sspanel';
$password = '';
$now_time=time();
  	try {
		$conn = new PDO("mysql:host=$host; dbname=$db_name", $username, $password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$now_t = time();
		$sql = "SELECT * FROM user WHERE expire_time BETWEEN '$now_time'+172800 AND '$now_time'+259200";
		$stmt = $conn->query($sql);
		if ($stmt->rowCount() ==0){exit ("no user is expiring");}
		//读取用户信息
		foreach ($stmt as $row) {
			$xsmtpapi=$xsmtpapi .'"' .$row['email'] . '",';
		}

	}
	catch (PDOException $e) {
		echo $e->getMessage();
	}
	$conn = null;
$xsmtpapi = rtrim($xsmtpapi, ",");
$xsmtpapi = $xsmtpapi . ']}';

echo $xsmtpapi;
$url = 'http://api.sendcloud.net/apiv2/mail/sendtemplate';
$data = array(
//        'apiUser'=>'',
//       'apiKey'=>'',
	'apiUser'=>'drice_test_7TWJuk',
	'apiKey'=>'',
	'from'=>'support@sendcloud.org',
	'templateInvokeName'=>'notice',
        'xsmtpapi'=>$xsmtpapi);
$data = http_build_query($data);
$opts = array(
'http' => array(
        'method' =>'POST',
        'header' =>"Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-Length: " . strlen($data) . "\r\n", 
        'content'=>$data
        )
);
$ctx = stream_context_create($opts);
$html = @file_get_contents($url,'',$ctx);
$obj = json_decode($html, true);
echo $obj["message"];
?>
