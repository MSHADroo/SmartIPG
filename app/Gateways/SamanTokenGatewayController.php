<?php

namespace App\Gateways;

use App\Controllers\Controller;
use App\DataAccess\GatewayDataAccess;
use GuzzleHttp\Exception\RequestException;
use SoapClient;
use Symfony\Component\Translation\Exception\LogicException;

/**
 * @property GatewayDataAccess GatewayDataAccess
 */
class SamanTokenGatewayController extends Controller
{
    private $terminal_id;
    private const GATEWAY_PAYMENT_URL = 'https://sep.shaparak.ir/MobilePG/MobilePayment';
    private const GATEWAY_VERIFY_URL = 'https://verify.sep.ir/Payments/ReferencePayment.asmx?WSDL';

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
        -1 => 'خطای در پردازش اطلاعات ارسالی. (مشکل در یکی از ورودیها و ناموفق بودن￼￼ فراخوانی متد برگشت تراکنش)',
        -3 => 'ورودیها حاوی کارکترهای غیرمجاز میباشند',
        -4 => 'Merchant Authentication Failed( کلمه عبور یا کد فروشنده اشتباه است)',
        -6 => 'تراکنش قبلا برگشت داده شده است',
        -7 => 'رسید دیجیتالی تهی است',
        -8 => 'طول ورودیها بیشتر از حد مجاز است',
        -9 => 'وجود کارکترهای غیرمجاز در مبلغ برگشتی',
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
    }

    public function init($input)
    {
        $data = [
            'Action'      => 'token',
            'TerminalId'  => $this->terminal_id,
            'RedirectUrl' => 'https://gateways.local/callback/saman-token',
            'Amount'      => $input->price,
            'ResNum'      => $input->id,
            'CellNumber'  => $input->mobile,
            'curl_url'    => self::GATEWAY_PAYMENT_URL
        ];

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', self::GATEWAY_PAYMENT_URL, ['timeout' => 10, 'form_params' => $data]);
            $statusCode = $response->getStatusCode(); // 200
            $result = $response->getBody()->getContents();
            $status = 'success';
        } catch (RequestException $exception) {
            $this->transactionLog([
                'token_id'     => $input->order_id,
                'request'      => $data,
                'request_type' => 'init_pay',
                'response'     => $exception->getMessage(),
                'status'       => 'fail',
            ]);
            return ['status' => false, 'message' => $exception->getMessage()];
        }

        $resultObject = json_decode($result, false);

        if ($resultObject->status == 1) {
            $token = $resultObject->token;

            $this->transactionLog([
                'token_id'     => $input->order_id,
                'request'      => $data,
                'request_type' => 'init_pay',
                'response'     => $result,
                'status'       => 'success',
            ]);

            return [
                'status' => true,
                'type'   => 'formSubmit',
                'data'   => [
                    'payment_url' => self::GATEWAY_PAYMENT_URL,
                    'token'       => $token,
                ]
            ];
        }

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

        $state = $input['State'];//وضعیت تراکنش (حروف انگلیسی)
        $status = $input['Status'];//وضعیت تراکنتش(مقدار عددی)
        $ref_num = $input['RefNum'];//رسید دیجیتالی خرید
        $res_num = $input['ResNum'];//شماره خرید order_id
        $terminal_id = $input['TerminalId'];//شماره ترمینال
        $trace_no = $input['TraceNo'];//شماره رهگیری

//        $mid = $input['MID'];
//        $rrn = $input['RRN'];
//        $amount = $input['Amount'];
//        $wage = $input['Wage'];
//        $secure_pan = $input['SecurePan'];


        try {

            if ($state === 'OK') {
                $tokenObject = $this->TokenDataAccess->selectById(['id' => $res_num]);
                if (!$tokenObject) {
                    throw new LogicException('order dose not exist');
                }
                if ($tokenObject->status === 'paid') {
                    throw new LogicException('previously logged');
                }

                try {
                    $client = new SoapClient(self::GATEWAY_VERIFY_URL);
                } catch (LogicException $exception) {
                    throw new LogicException($exception->getMessage());
                }

                //TODO retry on timeout
                $result = $client->VerifyTransaction($ref_num, $terminal_id);

                if ($result == $tokenObject->price) {//TODO check $result must be equal to payment price
                    $this->transactionLog([
                        'token_id'     => $tokenObject->id,
                        'request'      => $input,
                        'request_type' => 'confirm',
                        'response'     => $result,
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
                    'response'     => $result,
                    'status'       => 'fail',
                ]);
                throw new LogicException($this->verifyError[$result]);
            }
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
}