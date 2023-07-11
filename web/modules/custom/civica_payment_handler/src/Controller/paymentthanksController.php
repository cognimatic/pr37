<?php
namespace Drupal\civica_payment_handler\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\RequestException;
/**
 * Provides route responses for the Example module.
 */
class paymentthanksController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */

protected function callToCivica($url, $body, $stringLen) {


      try {

       $result = \Drupal::httpClient()->post($url, [
          'body' => $body,
          'headers' => [
            'Content-Type' => 'text/xml',
            'SOAPAction' => 'http://tempuri.org/QueryAuthRequests/Service1/Query',
            'Accept' => 'application/soap+xml',
            'curl' => [CURLOPT_SSL_VERIFYPEER => 0],
          ],
        ]);

      $data = (string) $result->getBody()->__toString();


    }
    catch (RequestException $e) {
      watchdog_exception('abc_ctax_login', $e);
     }

    if (isset($error_msg)) {
      echo $error_msg;
    }

    return $data;
  }

public function getPaymentDetails($url, $pagemarkup){

$sid = $_GET['CallingAppTxnRef'];
$BasketRef = $_GET['BasketRef'];

    $soapcall = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
      <soap:Body>
        <Query xmlns="http://tempuri.org/QueryAuthRequests/Service1">
          <ReqMessage xmlns="http://altsql71/XMLSchema/epayments/standard">
            <CallingApplicationIdentifier>Drupal</CallingApplicationIdentifier>
            <CriteraList>
              <Column>CallingApplicationTransactionReference</Column>
              <Value>'.$sid.'</Value>
              <Operator>=</Operator>
              <BooleanOp>And</BooleanOp>
            </CriteraList>
            <pageNum>1</pageNum>
            <pageSize>10</pageSize>
          </ReqMessage>
        </Query>
      </soap:Body>
    </soap:Envelope>';
   //print_r($soapcall);
    $stringLen = strlen($soapcall);
      
    $data = $this -> callToCivica($url, $soapcall, $stringLen);

  preg_match('/(?<=<RequestStatus>)(.*)(?=<\/RequestStatus>)/', $data, $requestStatus);
  preg_match('/(?<=<AccountPaymentAmount>)(.*)(?=<\/AccountPaymentAmount>)/', $data, $payment_amount);
  preg_match('/(?<=<OriginatorsReference>)(.*)(?=<\/OriginatorsReference>)/', $data, $originatorsRef);

  if ($requestStatus[0] == 'A') {
    $pagemarkup = "Payment Successful. <p>Civica eStore Originators Reference: " .$originatorsRef[0]."</p>";
    $pagemarkup .= "<p>Thank you for your payment of £<strong>" . $payment_amount[0] . "</strong>, this has been successfully processed and the payment reference number is <strong>" . $BasketRef . "</strong>.</p>";      
  } else {
    $failedReasons = array("A"=>"Authorised, payment completed", 
                "P"=>"Pending Authorisation", 
                "D"=>"Bank has declined this payment", 
                "T"=>"The payment has timed out whilst awaiting authorisation", 
                "C"=>"Could not contact the CommsXL/bank", 
                "S"=>"Could not contact the CommsXL server", 
                "R"=>"The payment has been referred for voice authorisation by the bank", 
                "M"=>"The bank is not responding", 
                "E"=>"The card has expired", 
                "I"=>"PIN Authorisation in progress", 
                "N"=>"PIN authorisation cancelled", 
                "W"=>"Payment authorised, awaiting signature verification", 
                "L"=>"Signature has been declined",
                "3DP" => "3D Secure Challenge incomplete");

    $pagemarkup .= "Payment unsuccessful. \nReason: ";
    //Check if array key exists - if it does append to $incidentMessage, if not then append generic help message.
    if(array_key_exists($requestStatus[0], $failedReasons)) {
      $pagemarkup .= $requestStatus[0] . ": " . $failedReasons[$requestStatus[0]];
    } else {
      $pagemarkup .= "Not Known - contact Jamie Robertson in Digital Services Team to investigate.";
    }
  }
    
        return $pagemarkup;
}


  public function paymentthanksPage() {
    $pagemarkup = 'Payment successful';
    $url = 'https://www.civicaepay.co.uk/ArgyllButeXML/QueryAuthRequests/QueryAuthRequests.asmx';
    $pagemarkup = $this -> getPaymentDetails($url, $pagemarkup);
    return [
        '#type' => 'markup',
        '#markup' => $pagemarkup
    ];
  }




}