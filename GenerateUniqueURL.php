<html>
<head>
    <title>Thank you!</title>
</head>
<body>


<?php

if(!(isset($_GET['affid']) && $_GET['phone'])){
die('Direct url access not allowed!');
}

$verificationCode = $_GET['affid'];
$phoneNumber = $_GET['phone'];

/*

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "AffiliateAudienceGathering";


$conn = mysqli_connect($servername, $username, $password,$dbname) or die(mysqli_connect_error());

 Check connection
 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
 } 
echo "Connected successfully<br>";


$sql = "SELECT * FROM users where phone=$phone and affid=$verificationCode";


$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
  
    $row = mysqli_fetch_assoc($result);

    } else {
    echo "0 results";
}

*/
    $whatsappAffLinkMsg="Love shopping on Flipkart, Amazon and Snapdeal.,etc? Click offer.bharathbalan.com/?affid=".$verificationCode." to get to the most amazing deals delivered to your whatsapp daily. Wait there's more. REFER 3 FRIENDS TO WIN A RECHARGE OF Rs.10";
    $whatsappAffLinkMsg=urlencode($whatsappAffLinkMsg);
    $whatsappAffLink = "<a href=whatsapp://send?text='$whatsappAffLinkMsg'>here</a>";
    echo "Thank you for registering with us.";
    echo "Invite your friends via Whatsapp by clicking $whatsappAffLink and win a mobile recharge worth Rs.10 for every 3 friends that you refer.";
    echo '<a href=whatsapp://send?text='.$whatsappAffLinkMsg.'><img src="wa_logo.png" height="48" width="254" /></a>';

    
    

// mysqli_close($conn);

?>



</body>
</html>
