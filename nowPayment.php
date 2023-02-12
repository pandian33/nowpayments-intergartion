<?php
/**
 * This class is sending payemnts for crypto
 */
class Payments {
    /*constant values declaration*/
    public $headers_REST;
    public $payMainUrl;
    public $nowPaymentToken;
    public $userEmail;
    public $password;

    /*set constant values*/
    public function setValues($connection, $payMainUrl, $nowPaymentToken, $userEmail, $password){
        $this->connection = $connection;
        $this->payMainUrl = $payMainUrl;
        $this->nowPaymentToken = $nowPaymentToken;
        $this->userEmail = $userEmail;
        $this->password = $password;
    }
    /*Get access token*/
    public function generateToken(){
        $payments = array(
            "email" => $this->userEmail,
            "password" => $this->password,
        );
        $postDatas =  json_encode($payments, true);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->payMainUrl.'auth',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postDatas,
        CURLOPT_HTTPHEADER => array(
            'x-api-key: '.$this->nowPaymentToken,
            'Api-Token: '.$this->nowPaymentToken,
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;

        session_start();
        $_SESSION["nowPaymentToken"] = $response;  

        // Initial Token time is stored in a session variable
        $_SESSION["tokenTimeStamp"] = time();
        return $response;


    }

    public function createPlan(){
        session_start();
        if(time()-$_SESSION["tokenTimeStamp"] >300) {
            //echo "aaa";
            session_unset();
            session_destroy();
            $responseToken = $this->generateToken();
        }else {
            //echo "bbb";
            $responseToken = $_SESSION["nowPaymentToken"];
        }
        $tokens = json_decode($responseToken); 
        //echo $tokens->token;    exit;
        $this->input = file_get_contents('php://input');
        $postDatas = $this->input;
        $data = json_decode($postDatas, true);
        $amount = $data['amount'];
        $interval_day = $data['interval_day'];
        $currency = $data['currency'];
        $title = $data['title'];
        $payments = array(
            "title" => $title,
            "interval_day" => $interval_day,
            "amount"=> $amount,
            "currency" => $currency
        );
        $postDatas =  json_encode($payments, true);
        //echo  $postDatas; exit;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->payMainUrl.'subscriptions/plans',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postDatas,
            CURLOPT_HTTPHEADER => array(
            'x-api-key: '.$this->nowPaymentToken,
            'Authorization: Bearer '.$tokens->token,
            'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        //echo $response;
        $this->createSubscription($response,$tokens->token);
        // $output = curl_exec($curl);
        // $returnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // curl_close($curl);
        // if($returnCode==200){
        //     $response['status'] = true;
        //     $response['code'] = $returnCode;
        //     $response['message'] = "Payment Success";
        //     $response['data'] =  json_decode($output);
        // } else {
        //     $response['status'] = false;
        //     $response['code'] = $returnCode;
        //     $response['message'] = "Payment Failed";
        //     $response['data'] =  json_decode($output);
        // }
        
        //echo json_encode($response);
    }

    public function updatePlan()
    { 
        session_start();
        if(time()-$_SESSION["tokenTimeStamp"] >300) {
            echo "aaa";
            session_unset();
            session_destroy();
            $responseToken = $this->generateToken();
        }else {
            echo "bbb";
            $responseToken = $_SESSION["nowPaymentToken"];
        }
        $tokens = json_decode($responseToken); 
        //echo $tokens->token;    exit;
        $id = $_REQUEST['id'];
       // echo $id; exit;
        $this->input = file_get_contents('php://input');
        $postDatas = $this->input;
        $data = json_decode($postDatas, true);
        $amount = $data['amount'];
        $interval_day = $data['interval_day'];
        $currency = $data['currency'];
        $title = $data['title'];
        $payments = array(
            "title" => $title,
            "interval_day" => $interval_day,
            "amount"=> $amount,
            "currency" => $currency
        );
        $paymentDatas =  json_encode($payments, true);
        //echo  $postDatas; exit;
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->payMainUrl.'subscriptions/plans/'.$id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => $paymentDatas,
        CURLOPT_HTTPHEADER => array(
            'x-api-key: '.$this->nowPaymentToken,
            'Authorization: Bearer '.$tokens->token,
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

    }

    public function createSubscription($response,$token){
        $tokens = json_decode($response); 
        //echo  $tokens->result->id; exit;
        // session_start();
        // if(time()-$_SESSION["accessTimeStamp"] >3600) {
        //     session_unset();
        //     session_destroy();
        //     //$token = $this->getAccessToken();
        // }else {
        //     $token = $_SESSION["token"];
        // }
        //$token = $this->generateToken();
        //$tokens = json_decode($token);
        //echo $tokens->access_token;
        // exit;
        
        // $this->input = file_get_contents('php://input');
        // $postDatas = $this->input;
        // $data = json_decode($postDatas, true);
        // $userCompanyAssignedUniqueKey = $data['userCompanyAssignedUniqueKey'];
        // $notificationEmail = $data['userNotificationEmailAddress'];
        $subscriptionData = array(
            "subscription_plan_id" => $tokens->result->id,
            "email" => "pandianoptisol@gmail.com"
        );
        $postDatas =  json_encode($subscriptionData, true);
        //echo  $postDatas; exit;
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->payMainUrl.'subscriptions/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postDatas,
        CURLOPT_HTTPHEADER => array(
            'x-api-key: '.$this->nowPaymentToken,
            'Authorization: Bearer '.$token,
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

        // if($returnCode==201){
        //     $response['status'] = true;
        //     $response['code'] = $returnCode;
        //     $response['message'] = "Sent Invitation Success";
        //     $response['data'] =  json_decode($output);
        // } else {
        //     $response['status'] = false;
        //     $response['code'] = $returnCode;
        //     $response['message'] = "Sent Invitation Failed";
        //     $response['data'] =  json_decode($output);
        // }
        
        // echo json_encode($response);

    }

}
?>