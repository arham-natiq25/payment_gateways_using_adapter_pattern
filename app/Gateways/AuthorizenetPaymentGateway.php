<?php

namespace App\Gateways;

use App\Models\CustomerProfile;
use App\Models\TransactionRecords;
use Illuminate\Support\Facades\Auth;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;


class AuthorizenetPaymentGateway{

    function chargeUsingAuthorizenet($request) {

        $user = Auth::user();
        /* Create a merchantAuthenticationType object with authentication details
           retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('authorizenet.login_id'));
        $merchantAuthentication->setTransactionKey(config('authorizenet.transaction_key'));

        // Set the transaction's refId
        $refId = 'ref' . time();

        try {
            if ($request->method===0) {
                $creditCard = new AnetAPI\CreditCardType();
                $creditCard->setCardNumber($request->cardNumber);
                $creditCard->setExpirationDate($request->expiryYear."-".$request->expiryMonth);
                $creditCard->setCardCode($request->cvv);

                $paymentOne = new AnetAPI\PaymentType();
                $paymentOne->setCreditCard($creditCard);


                // Set the customer's Bill To address
                $customerAddress = new AnetAPI\CustomerAddressType();
                $customerAddress->setFirstName($request->name);
                $customerAddress->setLastName($request->name);

                $customerData = new AnetAPI\CustomerDataType();
                $customerData->setType("individual");
                $customerData->setId($user->id); // give own user id
                $customerData->setEmail($request->email); // user emial

                $duplicateWindowSetting = new AnetAPI\SettingType();
                $duplicateWindowSetting->setSettingName("duplicateWindow");
                $duplicateWindowSetting->setSettingValue("60");

                $billTo = new AnetAPI\CustomerAddressType();
                $billTo->setFirstName($request->name);
                $billTo->setLastName($request->name);

                $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
                $paymentProfile->setCustomerType('individual');
                $paymentProfile->setBillTo($billTo);
                $paymentProfile->setPayment($paymentOne);
                $paymentProfiles[] = $paymentProfile;

                $customerProfile = new AnetAPI\CustomerProfileType();
                $customerProfile->setDescription("Payment Using laravel");
                $customerProfile->setMerchantCustomerId("M_" . time());
                $customerProfile->setEmail($request->email); // Set the email for the customer
                $customerProfile->setPaymentProfiles($paymentProfiles);

                $REQUEST = new AnetAPI\CreateCustomerProfileRequest();
                $REQUEST->setMerchantAuthentication($merchantAuthentication);
                $REQUEST->setRefId($refId);
                $REQUEST->setProfile($customerProfile);
                $controller = new AnetController\CreateCustomerProfileController($REQUEST);
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

                if ($response != null && $response->getMessages()->getResultCode() == "Ok") {
                    // here successfully get customer profile id
                    $customerProfileIDFromAuthorize = $response->getCustomerProfileId();
                    $paymentProfileId = $response->getCustomerPaymentProfileIdList()[0];
                } else {
                    // Handle the case where the API request was not successful
                    $errorMessages = $response->getMessages()->getMessage();
                }
                $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
                $profileToCharge->setCustomerProfileId($customerProfileIDFromAuthorize);
                $paymentProfile = new AnetAPI\PaymentProfileType();
                $paymentProfile->setPaymentProfileId($paymentProfileId);
                $profileToCharge->setPaymentProfile($paymentProfile);

                $transactionRequestType = new AnetAPI\TransactionRequestType();
                $transactionRequestType->setTransactionType("authCaptureTransaction");
                $transactionRequestType->setAmount($request->payment);
                $transactionRequestType->setProfile($profileToCharge);

                $REQUEST = new AnetAPI\CreateTransactionRequest();
                $REQUEST->setMerchantAuthentication($merchantAuthentication);
                $REQUEST->setRefId($refId);
                $REQUEST->setTransactionRequest($transactionRequestType);
                $controller = new AnetController\CreateTransactionController($REQUEST);
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
                if ($response != null) {
                    if ($response->getMessages()->getResultCode() == "Ok") {
                        $tresponse = $response->getTransactionResponse();
                        $lastFourDigitsFull = $tresponse->getAccountNumber(); // Assuming this contains the full card number
                        $lastFourDigits = substr($lastFourDigitsFull, -4);
                        $cardType = $tresponse->getAccountType();
                        $trx_id =  $tresponse->getTransId() . "\n";

                        CustomerProfile::create([
                            'user_id'=>$user->id,
                            'last_four_digit'=>$lastFourDigits,
                            'customer_profile_id'=>$customerProfileIDFromAuthorize,
                            'payment_method_id'=>$paymentProfileId,
                            'method'=>'authorizenet'
                        ]);
                        TransactionRecords::create([
                            'user_id'=>$user->id,
                            'payment'=>$request->payment,
                            'trx_id'=>$trx_id,
                            'method'=>'authorizenet'
                        ]);

                        return response()->json(['message' => 'Payment successful'], 200);
                    }else {
                        $errorMessages = $response->getMessages()->getMessage();
                        return response()->json(['error' => $errorMessages], 400);
                    }
                }
            }elseif($request->method===1){
                $customerProfileId = $request->card['customer_profile_id'];
                $lastFourDigits = $request->card['last_four_digit'];
                $user_id = $request->card['user_id'];
                $paymentProfileId = $request->card['payment_method_id'];

                $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
                $profileToCharge->setCustomerProfileId($customerProfileId);
                $paymentProfile = new AnetAPI\PaymentProfileType();
                $paymentProfile->setPaymentProfileId($paymentProfileId);
                $profileToCharge->setPaymentProfile($paymentProfile);

                $transactionRequestType = new AnetAPI\TransactionRequestType();
                $transactionRequestType->setTransactionType("authCaptureTransaction");
                $transactionRequestType->setAmount(240);
                $transactionRequestType->setProfile($profileToCharge);

                $REQUEST = new AnetAPI\CreateTransactionRequest();
                $REQUEST->setMerchantAuthentication($merchantAuthentication);
                $REQUEST->setRefId($refId);
                $REQUEST->setTransactionRequest($transactionRequestType);
                $controller = new AnetController\CreateTransactionController($REQUEST);
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

                if ($response != null) {
                    if ($response->getMessages()->getResultCode() == "Ok") {
                        $tresponse = $response->getTransactionResponse();
                        $trx_id =  $tresponse->getTransId() . "\n";
                        TransactionRecords::create([
                            'user_id'=>$user->id,
                            'payment'=>$request->payment,
                            'trx_id'=>$trx_id,
                            'method'=>'authorizenet'
                        ]);


                        return response()->json(['success' => true, 'message' => 'Payment successfully Recieved']);
                    }
                else {
                    $errorMessages = $response->getMessages()->getMessage();
                    return response()->json(['error' => $errorMessages], 400);
                }
                }
            }

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);

        }


    }
}
