<?php

require '../vendor/paymill/paymill/autoload.php';


  // Private Key
  $apiKey = "";

  $request = new Paymill\Request($apiKey);

  $transaction = new Paymill\Models\Request\Transaction();
  $transaction->setAmount($_POST['amount'])
              ->setCurrency($_POST['currency'])
              ->setToken($_POST['token'])
              ->setDescription($_POST['description']);

  try {
    $response = $request->create($transaction);
    include("guide_payment_end.php");
  } catch(\Paymill\Services\PaymillException $e){
    echo("An error occured while processing the transaction: ");
    echo($e->getErrorMessage());
  }
