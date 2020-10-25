<?php



function validate_mobile($mobile)
{
    return preg_match('/^[0-9]{12}+$/', $mobile);
}

if (!IS_LOGGED) {

    $response_data    = array(
        'api_status'  => '400',
        'api_version' => $api_version,
        'errors' => array(
            'error_id' => '1',
            'error_text' => 'Not logged in'
        )
    );
} else {
    $postdata = file_get_contents("php://input");

    $plus_month = date('Y-m-d h:i:s', strtotime('+30 days', strtotime(date('Y-m-d h:i:s'))));
    $plus_year = date('Y-m-d h:i:s', strtotime('+365 days', strtotime(date('Y-m-d h:i:s'))));
    $end_date = (($period == "year") ? $plus_year : $plus_month);
    $diff = abs(strtotime($end_date) - strtotime(date('Y-m-d H:i:s')));
    $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));


    if (!empty($_POST['ref_id']) && !empty($_POST['user_id'])) {
        $refID = PT_Secure($_POST['ref_id']);
        $user_id = PT_Secure($_POST['user_id']);

        $data = RequestPayStatus($refID);
        $mydata = json_decode(json_encode($data), true);
        $end_date = (($mydata['payerMessage'] == "year") ? $plus_year : $plus_month);
        $insert_data      = array(
            'user_id'    => $user_id,
            'payment_id'    => $refID,
            'amount'    => $mydata['amount'],
            'currency'    => $mydata['currency'],
            'provider'    => "MTN",
            'end_date'    => $end_date,
            'status'    => $mydata['status'],
            'period'    => $mydata['payerMessage'],
            'phone_number'    => $mydata['payer']['partyId'],
        );

        // if ($mydata['status'] == "SUCCESS") {
        if ($mydata['status'] == "FAILED") {
            $db->where('payment_id', $refID);
            $exist_payment = $db->getOne(T_APPPAY);
            $payment_id;
            $payment;
            $newdays;
            if ($exist_payment) {
                $db->where('payment_id', $refID);
                $payment_id  = $db->update(T_APPPAY, ['status' => $mydata['status'],]);
                $db->where('payment_id', $refID);
                $payment = $db->getOne(T_APPPAY);

                $diff = abs(strtotime($payment->end_date) - strtotime(date('Y-m-d H:i:s')));
                if ($diff > 0) {
                    $newdays = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
                } else {
                    $newdays = 0;
                }
            } else {
                $payment_id  = $db->insert(T_APPPAY, $insert_data);
                $db->where('id', $payment_id);
                $payment = $db->getOne(T_APPPAY);

                $diff = abs(strtotime($payment->end_date) - strtotime(date('Y-m-d H:i:s')));
                if ($diff > 0) {
                    $newdays = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
                } else {
                    $newdays = 0;
                }
            }

            $response_data     = array(
                'api_status'   => '200',
                'api_version'  => $api_version,
                'message' => 'Payment completed successfully',
                'data'      => $payment,
                'days' => $newdays,
            );
        } else {
            $response_data    = array(
                'api_status'         => '424',
                'api_version'        => $api_version,
                'ref_id' => $refID,
                'message' => 'Payment request failed, please try again',
            );
        }
    } else if (empty($_POST['user_id']) && empty($_POST['phone_number']) && empty($_POST['period'])) {
        $response_data       = array(
            'api_status'     => '400',
            'api_version'    => $api_version,
            'errors'         => array(
                'error_id'   => '1',
                'error_text' => 'Please enter your user id and phone number'
            )
        );
    } else if (!validate_mobile($_POST['phone_number'])) {
        $response_data       = array(
            'api_status'     => '400',
            'api_version'    => $api_version,
            'errors'         => array(
                'error_id'   => '1',
                'error_text' => 'Please enter correct phone number'
            )
        );
    } else {
        $user_id        = PT_Secure($_POST['user_id']);
        $phone_number        = PT_Secure($_POST['phone_number']);
        $period = ((!empty($_POST['period'])) ? PT_Secure($_POST['period']) : "month");
        $Amount = (($period == "year") ? "2900" : "300");
        $refID = guidv4();

        $db->where("(id = ? or phone_number = ?)", array($user_id, $username));
        $user = $db->getOne(T_USERS);

        if (!empty($user)) {
            if ($user->active == 0) {
                $response_data       = array(
                    'api_status'     => '400',
                    'api_version'    => $api_version,
                    'success_type'   => 'confirm_account',
                    'errors'         => array(
                        'error_id'   => '2',
                        'error_text' => 'this account is not found',
                        'email'      => $user_id
                    )
                );
            } else {

                $statusCode = RequestPay($phone_number, $Amount, $user->id, $refID, $period);

                if ($statusCode == 202) {
                    $number = substr($phone_number, 2);
                    $response_data    = array(
                        'api_status'         => '200',
                        'api_version'        => $api_version,
                        'message' => "Request accepted for processing, please authorize the transaction on $number",
                        'ref_id' => $refID,
                    );
                } else {
                    $response_data    = array(
                        'api_status'         => '400',
                        'api_version'        => $api_version,
                        'errors'             => array(
                            'error_id'       => '3',
                            'error_text'     => 'Invalid payment request'
                        )
                    );
                }
            }
        } else {
            $response_data           = array(
                'api_status'         => '400',
                'api_version'        => $api_version,
                'errors'             => array(
                    'error_id'       => '3',
                    'error_text'     => 'Invalid user id or Phone number'
                )
            );
        }
    }
}
