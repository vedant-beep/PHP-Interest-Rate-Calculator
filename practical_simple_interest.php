<?php

$invoiceAmount = 1000;
$invoiceDueDate = '2015-01-01';
$interestRates = [
    '2014-07-01' => 8.00,
    '2017-01-01' => 9.00,
    '2018-07-01' => 8.00,    
];

$payments = [
'2015-04-01' => 100,
'2017-08-15' => 100,
'2019-11-01' => 100,
];

$dueLeftToPay = $invoiceAmount; // taking initial due amount
$interest_arr = []; // intializing output array
$count = 0; 
$first_pay = '';
$f_amount = 0;

// sorting according to the dates
ksort($payments);
ksort($interestRates);

// for getting paid first amount
foreach($payments as $k=>$val){
    if($count == 0){
        $first_pay = $k;
        $f_amount = $val;
        break;
    }
}

// check if first amount paid before or after due date and then accordingly calculate the interest
if(strtotime($first_pay)>strtotime($invoiceDueDate)){
    $aggr_si = getAggregateInt($first_pay,$interestRates,$dueLeftToPay);
    array_push($interest_arr,round($aggr_si,2));
}
else{
    array_push($interest_arr,0);
}

$dueLeftToPay -= $f_amount; // deduct the due amount after first pay
$last_pay = $first_pay;

// iterating through all the payments
foreach($payments as $k=>$val){
    if($count != 0){ // skipping the first payment
        // check if the amount paid before or after due date and then accordingly calculate the interest
        if(strtotime($k)>strtotime($invoiceDueDate)){
            $aggr_si = getAggregateInt($k,$interestRates,$dueLeftToPay);
            array_push($interest_arr,round($aggr_si,2)); // push the interest of current due amount
        } 
        else{
            array_push($interest_arr,0);
        }  
        $dueLeftToPay -= $val; // deduct the due amount after payment
        $last_pay = $k; // updating the last payment date
    }
    
    $count++;
}
print_r($interest_arr); // final output array

// function to get no of days between two given dates
function getTimeDiff($t1,$t2){
    $t1 = strtotime($t1);
    $t2 = strtotime($t2);
    $datediff = $t1 - $t2;

    return round($datediff / (60 * 60 * 24));
}

// function to calculate simple interest
function si($p,$r,$t){
    return ($p*$r*$t)/36500;
}

// function to get aggregate interest till current date of payment
function getAggregateInt($currpaydate, $interestRates,$dueLeftToPay){
    $total_si = 0;
    $arr = [];
    // get an array of all the dates of change of interest rates that are less than the current payment date
    foreach($interestRates as $k=>$val){
        if(strtotime($k)<=strtotime($currpaydate)){
            array_push($arr,$k);
        }
    }
    sort($arr);
    if(!empty($arr)){
        // calculate interval wise interest
        for($i = 1; $i<count($arr); $i++){
            $days = getTimeDiff($arr[$i],$arr[$i-1]);
            $total_si += si($dueLeftToPay,$interestRates[$arr[$i-1]],$days);
        }
        // calculate interest between the last change date and current payment date
        $l_days = getTimeDiff($currpaydate,$arr[$i-1]);
        $total_si += si($dueLeftToPay,$interestRates[$arr[$i-1]],$l_days);
    }
    return $total_si;
}
