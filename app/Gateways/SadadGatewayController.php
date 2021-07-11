<?php

namespace App\Gateways;

use App\Controllers\Controller;
use App\DataAccess\GatewayDataAccess;
use App\DataAccess\TokenDataAccess;
use Symfony\Component\Translation\Exception\LogicException;

/**
 * @property GatewayDataAccess GatewayDataAccess
 * @property TokenDataAccess   TokenDataAccess
 */
class SadadGatewayController extends Controller
{
    private $merchant_id;
    private $terminal_id;
    private $ipg_key;
    private const GATEWAY_VERIFY_URL = 'https://sadad.shaparak.ir/vpg/api/v0/AdviceEx/Verify';
    private const GATEWAY_PURCHASE_URL = 'https://sadad.shaparak.ir/VPG/Purchase';
    private const GATEWAY_PAYMENT_URL = 'https://sadad.shaparak.ir/vpg/api/v0/Request/PaymentRequest';

    private $error = [
        1  => [
            'code' => 'CanceledByUser',
            'desc' => 'کاربر انصراف داده است',
        ],
        2  => [
            'code' => 'OK',
            'desc' => 'پرداخت با موفقیت انجام شد',
        ],
        3  => [
            'code' => 'Failed',
            'desc' => 'پرداخت انجام نشد',
        ],
        4  => [
            'code' => 'SessionIsNull',
            'desc' => 'کاربر در بازه زمانی تعیین شده پاسخی ارسال نکرده است',
        ],
        5  => [
            'code' => 'InvalidParameters',
            'desc' => 'پارامترهای ارسالی نامعتبر است',
        ],
        8  => [
            'code' => 'MerchantIpAddressIsInvalid',
            'desc' => 'آدرس سرور پذیرنده نامعتبر است (در پرداخت های بر پایه توکن)',
        ],
        10 => [
            'code' => 'TokenNotFound',
            'desc' => 'توکن ارسال شده یافت نشد',
        ],
        11 => [
            'code' => 'TokenRequired',
            'desc' => 'با این شماره ترمینال فقط تراکنش های توکنی قابل پرداخت هستند',
        ],
        12 => [
            'code' => 'TerminalNotFound',
            'desc' => 'شماره ترمینال ارسال شده یافت نشد',
        ],
    ];

    private $verifyError = [
        -1  => 'خطای در پردازش اطلاعات ارسالی. (مشکل در یکی از ورودیها و ناموفق بودن￼￼ فراخوانی متد برگشت تراکنش)',
        -3  => 'ورودیها حاوی کارکترهای غیرمجاز میباشند',
        -4  => 'Merchant Authentication Failed( کلمه عبور یا کد فروشنده اشتباه است)',
        -6  => 'تراکنش قبلا برگشت داده شده است',
        -7  => 'رسید دیجیتالی تهی است',
        -8  => 'طول ورودیها بیشتر از حد مجاز است',
        -9  => 'وجود کارکترهای غیرمجاز در مبلغ برگشتی',
        -10 => 'رسید دیجیتالی به صورت Base64 نیست (حاوی کارکترهای غیرمجاز است)',
        -11 => 'طول ورودیها کمتر از حد مجاز است',
        -12 => 'مبلغ برگشتی منفی است',
        -13 => 'مبلغ برگشتی برای برگشت جزئی بیش از مبلغ برگشت نخوردهی رسید دیجیتالی است',
        -14 => 'چنین تراکنشی تعریف نشده است',
        -15 => 'مبلغ برگشتی به صورت اعشاری داده شده است',
        -16 => 'خطای داخلی سیستم',
        -17 => 'برگشت زدن جزیی تراکنش مجاز نمی باشد',
        -18 => 'IP Address فروشنده نا معتبر است',
    ];

    public function __construct($container, $conf)
    {
        parent::__construct($container);
        $this->terminal_id = $conf->terminal_id;
        $this->merchant_id = $conf->merchant_id;
        $this->ipg_key = $conf->ipg_key;
    }

    public function init($input)
    {
        $local_date_time = date('m/d/Y g:i:s a');

        $sign_data = $this->encrypt_pkcs7(
            "$this->terminal_id;$input->order_id;$input->price",
            "$this->ipg_key"
        );

        $data = array(
            'TerminalId'    => $this->terminal_id,
            'MerchantId'    => $this->merchant_id,
            'Amount'        => $input->amount,
            'SignData'      => $sign_data,
            'ReturnUrl'     => 'http://gateways.local/callback/sadad',
            'LocalDateTime' => $local_date_time,
            'OrderId'       => $input->order_id
        );

        if ($input->mobile) {
            $data['UserId'] = $input->mobile;
        }

        $str_data = json_encode($data);

        $result = $this->connect(self::GATEWAY_PAYMENT_URL, $str_data);
        $arr = json_decode($result, false);

        if ($arr->ResCode == 0) {
            $token = $arr->Token;
            $url = self::GATEWAY_PURCHASE_URL . "?Token=$token";

            $this->transactionLog([
                'token_id'     => $input->order_id,
                'request'      => $data,
                'request_type' => 'init_pay',
                'response'     => $result,
                'status'       => 'success',
            ]);

            return [
                'status' => true,
                'type'   => 'redirect',
                'data'   => [
                    'payment_url' => $url
                ]
            ];
        }

        //TODO check
        $errorCode = $resultObject->errorCode;
        $errorDesc = $resultObject->errorDesc;

        $this->transactionLog([
            'token_id'     => $input->order_id,
            'request'      => $data,
            'request_type' => 'init_pay',
            'response'     => $result,
            'status'       => 'fail',
        ]);

        //is possible to use error variable with errorCode
        return ['status' => false, 'message' => $errorDesc];
    }

    public function callback(array $input): array
    {
//        $guid = StringHelper::getGUID();

        $bankInfo['Token'] = $input['token'];
        $bankInfo['ResCode'] = $input['ResCode'];
        $bankInfo['OrderId'] = $input['OrderId'];
        $bankInfo['voucher_code'] = $input['voucher_code'];
        $bankInfo['devicetype'] = $input['devicetype'];
        $bankInfo['pay_userid'] = $input['pay_userid'];
        $bankInfo['packageid'] = $input['packageid'];
        $bankInfo['pay_type'] = 'bank_melli';
        $bankInfo['ref_type'] = $input['ref_type'];

        try {
            $verify_data = $this->verify($input);

            if ($bankInfo['ResCode'] == 0 && $verify_data->ResCode != -1 && $verify_data->Amount > 0) {
                $verify_status = 'ok';
            }

            // Retry
            if ($verify_status !== 'ok') {
                sleep(5);
                $verify_data = $this->verify($input);
            }

            $customer_id = $verify_data->CardNo;
            $bankInfo['VerifyResCode'] = $verify_data->ResCode;
            $bankInfo['payResult'] = $verify_status;
            $bankInfo['CustomerRefNum'] = $verify_data->OrderId;
            $bankInfo['OrderId'] = $verify_data->OrderId;
            $bankInfo['SystemTraceNo'] = $verify_data->SystemTraceNo;
            $bankInfo['Description'] = $verify_data->Description;
            $bankInfo['Amount'] = $verify_data->Amount;
            $bankInfo['RetrivalRefNo'] = $verify_data->RetrivalRefNo;
            $bankInfo['fullInfo'] = $verify_data;

            $RefNum = $verify_data->RetrivalRefNo ? $verify_data->RetrivalRefNo : 0;


            if ($verify_status === 'ok') {
                $tokenObject = $this->TokenDataAccess->selectById(['id' => $RefNum]);
                if (!$tokenObject) {
                    throw new LogicException('order dose not exist');
                }
                if ($tokenObject->status === 'paid') {
                    throw new LogicException('previously logged');
                }

                $this->transactionLog([
                    'token_id'     => $tokenObject->id,
                    'request'      => $input,
                    'request_type' => 'confirm',
                    'response'     => $bankInfo,
                    'status'       => 'success',
                ]);
                return [
                    'status' => true
                ];
            }
            $this->transactionLog([
                'token_id'     => $tokenObject->id,
                'request'      => $input,
                'request_type' => 'confirm',
                'response'     => $bankInfo,
                'status'       => 'fail',
            ]);
            throw new LogicException($this->error[$status]['desc']);
        } catch (LogicException $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function reverseTransaction()
    {

    }

    private function connect($url, $params)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 10000);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($params)));
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    private function verify($input = array())
    {
        $key = $this->ipg_key;
        $res_code = $input['ResCode'];
        $token = $input['token'];

        $arrres = [];
        if ($res_code == 0) {
            $verifyData = [
                'Token'    => $token,
                'SignData' => $this->encrypt_pkcs7($token, $key)
            ];
            $str_data = json_encode($verifyData);
            $result = $this->connect(self::GATEWAY_VERIFY_URL, $str_data);
            $arrres = json_decode($result);
        }

        return $arrres;
    }
}
