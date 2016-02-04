<?php
require_once 'SendMessage.php';

define('DB_HOST', getenv('OPENSHIFT_MYSQL_DB_HOST'));
define('DB_PORT', getenv('OPENSHIFT_MYSQL_DB_PORT'));
define('DB_USER', getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
define('DB_PASS', getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
define('DB_NAME', getenv('OPENSHIFT_APP_NAME'));




$servername = DB_HOST;
$username = DB_USER;
$password = DB_PASS;
$dbname = DB_NAME;
$dbport = DB_PORT;




$conn = mysqli_connect($servername, $username, $password,$dbname,$dbport) or die(mysqli_connect_error());


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function sanitise($string){
    global $conn;
  $string = strip_tags($string); // Remove HTML
  $string = htmlspecialchars($string); // Convert characters
  $string = trim(rtrim(ltrim($string))); // Remove spaces
  $string = mysqli_real_escape_string($conn,$string); // Prevent SQL Injection
  return $string;
}


$uniqueCodeSet=0;
$phoneSet = 0;
$affidSet = 0;
if (isset($_GET['uniquecode'])) {
     $clean_uniqueCode = sanitise($_GET['uniquecode']);
     if (strlen($clean_uniqueCode)==6) {
            $uniqueCodeSet = 1;
    // global $uniqueCode;
    $uniqueCode=$clean_uniqueCode;
        }        
    }

if (isset($_GET['phone'])) {
    $clean_phone = sanitise($_GET['phone']);
    if(preg_match("/^[0-9]{10}$/", $clean_phone)) {
  // $phone is valid
        $phoneSet = 1;
    $phone = '91'.$clean_phone;
    }else if(preg_match("/^[0-9]{12}$/", $clean_phone)) {
  // $phone is valid
        $phoneSet = 1;
    $phone = $clean_phone;
    }else{
        die('Sorry You did not enter a valid phone number. Please go back and try again :/');
    }   
}

if (isset($_GET['affid'])) {
    $clean_affid = sanitise($_GET['affid']);
     if (strlen($clean_affid)==6) {
         $sql="select * from users where affid='".$clean_affid."'";
        $result = mysqli_query($conn, $sql) or die('Cannot query database :'.mysqli_error($conn));
        if (mysqli_num_rows($result)>0) {
            $affidSet = 1;
    // global $affid;
    $affid = $clean_affid;
        }
    }
}





function promptRegistration(){
    global $conn,$uniqueCodeSet,$phoneSet,$affidSet,$uniqueCode,$phone,$affid;
?>

                        <h1 class="cover-heading">Unbelievable deals on Flipkart, Amazon &amp; Snapdeal</h1>
                        <p class="lead">Register your number here and refer 3 friends through whatsapp with your unique link and <strong>WIN TALKTIME WORTH Rs.10.</strong></p>
                        <p class="lead">
                            <form action="" method="GET" role="form">
                                <div class="form-group">
                                    <input type="phone" class="form-control" id="" name="phone" placeholder="Your 10 digit mobile number"  length="10">
                                </div>
                                <?php

                                if ($affidSet) {
                                    ?>
                                    <input type=hidden name="affid" value="<?php echo $affid ;?>" />
                                    <?php
                                }
                                 ?>
                                <button type="submit" class="btn btn-lg btn-default">Sign me up!</button>
                            </form>
                        </p>
                    
<?php

}

function verifyUser(){
    global $conn,$uniqueCodeSet,$phoneSet,$affidSet,$uniqueCode,$phone,$affid;
echo " Your Unique code is <strong> ".$uniqueCode."</strong><br/> ";

$sql = "SELECT * FROM users WHERE phone='".$phone."' and affid='".$uniqueCode."'";


$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
  
    $row = mysqli_fetch_assoc($result);

    if ($row['verified']==0) {

        $usedId=$row['id'];
    $referredby=$row['referredby'];
    mysqli_autocommit($conn,FALSE);
    $sql="UPDATE users SET verified=1 WHERE phone=".$phone;
    $newresult = mysqli_query($conn, $sql) or die("Cannot verify user because of a database error!");
      //Increment referral count

        if($referredby!='DIRECT'){
        $sql="UPDATE users SET totalreferralscount = totalreferralscount + 1,pendingreferralcount = pendingreferralcount + 1 WHERE affid='".$referredby."'";
        $result = mysqli_query($conn, $sql) or die('Cannot query database :'.mysqli_error($conn));
            }


   mysqli_commit($conn);
    echo "Thanks for subscribing to our free service.";
    echo "Invite your friends by sharing this via whatsapp and<strong> win a mobile recharge worth Rs.10</strong> for every 3 friends that you refer.";
    echo generateWhatsappLink($uniqueCode);


        
    }else{

        echo "You have already verified you number. Just Sit back and relax. All the amazing deals will be soon be on your whatsapp inbox ;)."; 
        echo "Invite your friends by sharing this via whatsapp and<strong> win a mobile recharge worth Rs.10</strong> for every 3 friends that you refer.<br>";
         generateWhatsappLink($uniqueCode);



    }

    } else {
    ?>

    <div class="alert alert-danger">

                                Error: Phone number verification <strong>failed</strong>. Your phone number and unique code do not match :(
                                
                            </div>
        

    <?php
}

}// end verifyUser()

function registerUser(){

    global $conn,$uniqueCodeSet,$phoneSet,$affidSet,$uniqueCode,$phone,$affid;


    //User has submitted a number for registration. Generate affid and send to user via whatsapp

    $sql="select * from users where phone='".$phone."'";
    $result = mysqli_query($conn, $sql) or die('Cannot query database :'.mysqli_error($conn));
    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        if ($row['verified']==0) {
            ?>
            It seems that you have already registered your number, but haven't verified it yet. Please verify your number to enjoy the most amazing online deals ;)
            <?php
        }else{
            ?>
            It seems that you have already verified your number. Please share your unique link via whatsapp to win a mobile recharge of Rs.10 for every 3 friends that sign up.

            <?php

                generateWhatsappLink($row['affid']);
        }
    }else
    {
        //User has not registered previously


    do{

        $generatedUniquecode=bin2hex(openssl_random_pseudo_bytes(3));

        $sql="select * from users where affid='".$generatedUniquecode."'";
        $result = mysqli_query($conn, $sql) or die('Cannot query database :'.mysqli_error($conn));

    }while(mysqli_num_rows($result) > 0);

    if ($affidSet) {

        $sql="insert into users (phone,affid,referredby) values ('".$phone."' , '".$generatedUniquecode."','".$affid."')"; 
        $result = mysqli_query($conn, $sql) or die('Cannot query database :'.mysqli_error($conn));
      

    } else {
        $sql="insert into users (phone,affid) values ( '".$phone."' , '".$generatedUniquecode."' )";
        $result = mysqli_query($conn, $sql) or die('Cannot query database :'.mysqli_error($conn));
    }
    

    $message = "Hey there! Thanks for signing up on LootFast. You are only one step away from getting the best online deals delivered straight to your inbox. Please open -> https://lootfast.bharathbalan.com/?phone=".$phone."&uniquecode=".$generatedUniquecode." to verify your account.(Be sure to add this number to your contacts as \"LootFast\" to view the verification link)";


     sendMessageViaWhatsapp($phone,$message,"mysecret");
     // sleep(2);
    


    echo "Hey ".$phone." ! Thank you for registering with us. Please open the verification link that you'll be receiving via whatsapp to verify your number(Be sure to add our number to your contacts as \"Loot Fast\" to view the verification link).";
    
    
    }
   
}

function generateWhatsappLink($uniqueReferralCode)
{
    $whatsappAffLinkMsg="Love shopping on Flipkart, Amazon and Snapdeal.,etc? Click here  https://lootfast.bharathbalan.com/?affid=".$uniqueReferralCode." to get to the most amazing deals delivered to your whatsapp daily. Wait there's more. REFER 3 FRIENDS TO WIN A RECHARGE OF Rs.10";
    $whatsappAffLinkMsg=urlencode($whatsappAffLinkMsg);
    $whatsappAffLink = "<a href=whatsapp://send?text='$whatsappAffLinkMsg'>here</a>";
    echo '<a href=whatsapp://send?text='.$whatsappAffLinkMsg.'><img src="wa_logo.png" height="48" width="254" /></a>';
}



if($phoneSet && $uniqueCodeSet){
      verifyUser();    
}else if($phoneSet){
    registerUser();
}else{
    promptRegistration();
}

mysqli_close($conn);


?>

