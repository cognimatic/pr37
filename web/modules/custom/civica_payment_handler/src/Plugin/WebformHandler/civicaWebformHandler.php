<?php

namespace Drupal\civica_payment_handler\Plugin\WebformHandler;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\webform\Annotation\WebformHandler;
use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\Plugin\WebformHandlerInterface;
use Drupal\webform\Plugin\WebformHandlerMessageInterface;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use \GuzzleHttp\Exception\RequestException;

/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "civica_webform_handler",
 *   label = @Translation("Civica payment handler"),
 *   category = @Translation("Payments"),
 *   description = @Translation("Sends payment info to Civica."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */


final class civicaWebformHandler extends WebformHandlerBase {

  protected function callToCivica($url, $body) {

    $data = [];
      try {
       $result = \Drupal::httpClient()->post($url, [
          'body' => $body,
          'headers' => [
            'Content-Type' => 'application/json'
          ],
        ]);
        $data = json_decode($result->getBody()->getContents());
    }
    catch (RequestException $e) {
      watchdog_exception('abc_payment_civica', $e);
     }
    if (isset($error_msg)) {
      Print_r($error_msg);
    }
    return $data;
  }

  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    
       $config = \Drupal::config('civica_payment_handler.settings');
       $url = $config->get('civica_payment_endpoint');
       $notifyURL = $config->get('notifyURL');
       $returnUrl = $config->get('returnUrl');
       $customerId = $config->get('customerId');
       $apiPass = $config->get('apiPassword');

        // Get an array of the values from the submission.
        //$url = 'https://www.civicaepay.co.uk/ArgyllButeEstoreTest/TransportableBasket/api/BasketApi/CreateImmediatePaymentBasket';
        $values = $webform_submission->getData();
        $webform_submission->save();
        //dpm($values);
        $sid = $webform_submission->id();

        if(!empty($values["full_name"]["first"])){
          $first_name = $values["full_name"]["first"];
        }else{
          $first_name = "Business Name";
        }
        
        $catalogueId = $values["catalogueid"];
        $costcode = $values["costcode"];
        $paymentNarrative = $values["webform_title"].$sid;
        $user_email = $values["e_mail"];
        
        
        if (!$values["amount"]){
          $cost = $values["type_of_application"];
        }else{
        $cost = $values["amount"];
        }
        $jsonstr  = <<<DATA
{
    "callingAppIdentifier": "Drupal",
    "customerId": "$customerId",
    "apiPassword": "$apiPass",
    "callingAppTranReference": "$sid",
    "returnUrl": "$returnUrl",
    "notifyURL": "$notifyURL",
    "userName": "$first_name",
    "PaymentItems":[
    {
      "PaymentDetails":
      {
        "catalogueId": "$catalogueId",
        "accountReference": "$costcode",
        "PaymentAmount":"$cost",
        "Quantity":"1",
        "VATCode":"",
        "PaymentNarrative":"$paymentNarrative",
        "EmailAddress":"$user_email",
        "PaymentNotificationURL":"https://www.test.argyll-bute.gov.uk/thanks-civica",
        "CallingAppTranReference":"$sid",
      },
      "AddressDetails":
      {
        "Name":"$first_name",
        "HouseNo":"",
        "HouseName":"",
        "Street":"",
        "Area":"",
        "Town":"",
        "County":"",
        "Postcode":"",
        "Country":"UK"
      }
    }
   ]
  }
DATA;

        //$this->messenger()->addStatus($this->t('Time for payment!'));
      //dpm($jsonstr);

       $paymentReturn = $this->callToCivica($url, $jsonstr);
       //dpm($paymentReturn);

    
       //dpm($values);
       //$values["ref"] = $paymentReturn->{'BasketReference'};
       $webform_submission->setElementData('ref', $paymentReturn->{'BasketReference'});
       $webform_submission->save();
       $myurl = 'https://www.civicaepay.co.uk/ArgyllButeEstore/estore/default/Remote/Fetch?basketreference="'.$paymentReturn->{'BasketReference'}.'"&baskettoken="'.$paymentReturn->{'BasketToken'}.'&CallingAppTxnReference='.$sid; 
       
       $response = new TrustedRedirectResponse(Url::fromUri($myurl)->toString());
       $form_state->setResponse($response);
  }
  
    public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
      }
      

}



