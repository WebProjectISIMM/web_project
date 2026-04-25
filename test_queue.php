<?php
session_start();
$_SESSION['user_id'] = 11;
$_SESSION['user_name'] = 'test';
$_SESSION['user_role'] = 'admin';
$_SESSION['sector'] = 'banque';
$_SESSION['establishment'] = 'BIAT Marina';

// Then fetch the API output by including the script.
// Actually, it's better to use file_get_contents with cookies or just cURL.
$cookie_file = __DIR__ . '/cookie.txt';
// Login to get cookies
$ch = curl_init("http://localhost/Web_Project/api/tickets.php?action=get-queue&establishment=biat_marina");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=" . session_id());
$response = curl_exec($ch);
curl_close($ch);
echo "Queue JSON response:\n";
echo $response;
?>
