<?php


namespace Drupal\custom_form_handler\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Links to capita payment portal.
 *
 * @WebformHandler(
 *   id = "payment portal",
 *   label = @Translation("payment portal"),
 *   category = @Translation("Action"),
 *   description = @Translation("links webform submission to payment portal."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class CustomWebformHandler extends WebformHandlerBase {
	

  /**
   * {@inheritdoc}
   */

  // Function to be fired after submitting the Webform.
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get an array of the values from the submission.
    $values = $webform_submission->getData();

    $node_args = [
      'type' => 'webform',
      'langcode' => 'en',
      'created' => time(),
      'uid' => 1,
	  'sid' => $webform_submission->id(),
      'title' => $values['webform_title'],
      //'field_amount' => $values['amount'],['type_of_application'],
      ];
	  if (array_key_exists('type_of_application',$values)) {
		  $node_args += [ "field_amount" => $values['type_of_application']];
	  } elseif (array_key_exists('amount',$values)) {
		  $node_args += [ "field_amount" => $values['amount']];
	  } else {
	  echo "price not found";
	  }
	 
     print_r($node_args);
	  echo "hello world";
/**	  
	 $xmlstring = '<?xml version="1.0"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sim="http://www.capita-software-services.com/scp/simple" xmlns:com="https://support.capita-software.co.uk/selfservice/?commonFoundation" xmlns:base="http://www.capita-software-services.com/scp/base">
   <soapenv:Header/>
   <soapenv:Body>
 <scpSimpleInvokeRequest xmlns="http://www.capita-software-services.com/scp/simple"
                        xmlns:scpbase="http://www.capita-software-services.com/scp/base">
                        <credentials xmlns = "https://support.capita-software.co.uk/selfservice/?commonFoundation">
                        <subject>
                            <subjectType>CapitaPortal</subjectType>
                            <identifier>373183790</identifier>
                            <systemCode>SCP</systemCode>
                        </subject>
                        <requestIdentification>
                            <uniqueReference>'.$uniqueReference.'</uniqueReference>
                            <timeStamp>'.$timestamp.'</timeStamp>
                        </requestIdentification>
                        <signature>
                            <algorithm>Original</algorithm>
                            <hmacKeyID>456</hmacKeyID>
                            <digest>'.$digest.'</digest>
                        </signature>
                    </credentials>
    <scpbase:requestType>payOnly</scpbase:requestType>
    <scpbase:requestId>12345</scpbase:requestId>
    <scpbase:routing>
        <scpbase:returnUrl>http://localhost:8080/thankyou.php</scpbase:returnUrl>
        <scpbase:siteId>147</scpbase:siteId>
        <scpbase:scpId>373183790</scpbase:scpId>
    </scpbase:routing>
    <scpbase:panEntryMethod>ECOM</scpbase:panEntryMethod>
    <scpbase:additionalInstructions>
        <scpbase:systemCode>AIP</scpbase:systemCode>
    </scpbase:additionalInstructions>
    <sale>
        <scpbase:saleSummary>
            <scpbase:description>Stuff</scpbase:description>
            <scpbase:amountInMinorUnits>'.$cost.'</scpbase:amountInMinorUnits>
            <scpbase:reference>reference</scpbase:reference>
        </scpbase:saleSummary>
    </sale>
</scpSimpleInvokeRequest>
   </soapenv:Body>
</soapenv:Envelope>
'; 

 **/
  
}



	  
  
  }




	
	
