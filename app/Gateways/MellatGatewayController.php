<?php

namespace App\Gateways;

use App\Controllers\Controller;
use App\DataAccess\GatewayDataAccess;
use Exception;
use Symfony\Component\Translation\Exception\LogicException;

/**
 * Mellat Gateway based on doc v1.2
 * @property GatewayDataAccess GatewayDataAccess
 */
class MellatGatewayController extends Controller
{
    private const REDIRECT_URL = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat';
    private const GATEWAY_URL = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';
    private const GATEWAY_NAMESPACE = 'http://interfaces.core.sw.bps.com/';
    private const GATEWAY_TERMINAL_ID = 1;
    private const GATEWAY_USERNAME = '';
    private const GATEWAY_PASSWORD = '';

    private $terminalId;
    private $userName;
    private $userPassword;

    private $error = [
        0   => 'تراكنش با موفقيت انجام شد',
        11  => 'تراكنش با موفقيت انجام شد',
        12  => 'تراكنش با موفقيت انجام شد',
        13  => 'تراكنش با موفقيت انجام شد',
        14  => 'تراكنش با موفقيت انجام شد',
        15  => 'تراكنش با موفقيت انجام شد',
        16  => 'تراكنش با موفقيت انجام شد',
        17  => 'تراكنش با موفقيت انجام شد',
        18  => 'تراكنش با موفقيت انجام شد',
        19  => 'تراكنش با موفقيت انجام شد',
        111 => 'تراكنش با موفقيت انجام شد',
        112 => 'تراكنش با موفقيت انجام شد',
        113 => 'تراكنش با موفقيت انجام شد',
        114 => 'تراكنش با موفقيت انجام شد',
        21  => 'تراكنش با موفقيت انجام شد',
        23  => 'تراكنش با موفقيت انجام شد',
        24  => 'تراكنش با موفقيت انجام شد',
        25  => 'تراكنش با موفقيت انجام شد',
        31  => 'تراكنش با موفقيت انجام شد',
        32  => 'تراكنش با موفقيت انجام شد',
        33  => 'تراكنش با موفقيت انجام شد',
        34  => 'تراكنش با موفقيت انجام شد',
        35  => 'تراكنش با موفقيت انجام شد',
        41  => 'تراكنش با موفقيت انجام شد',
        42  => 'تراكنش با موفقيت انجام شد',
        43  => 'تراكنش با موفقيت انجام شد',
        44  => 'تراكنش با موفقيت انجام شد',
        45  => 'تراكنش با موفقيت انجام شد',
        46  => 'تراكنش با موفقيت انجام شد',
        47  => 'تراكنش با موفقيت انجام شد',
        48  => 'تراكنش با موفقيت انجام شد',
        49  => 'تراكنش با موفقيت انجام شد',
        412 => 'تراكنش با موفقيت انجام شد',
        413 => 'تراكنش با موفقيت انجام شد',
        414 => 'تراكنش با موفقيت انجام شد',
        415 => 'تراكنش با موفقيت انجام شد',
        416 => 'تراكنش با موفقيت انجام شد',
        417 => 'تراكنش با موفقيت انجام شد',
        418 => 'تراكنش با موفقيت انجام شد',
        419 => 'تراكنش با موفقيت انجام شد',
        421 => 'تراكنش با موفقيت انجام شد',
        51  => 'تراكنش با موفقيت انجام شد',
        54  => 'تراكنش با موفقيت انجام شد',
        55  => 'تراكنش با موفقيت انجام شد',
        61  => 'تراكنش با موفقيت انجام شد',
    ];

    public function __construct($container, $conf)
    {
        parent::__construct($container);
        $this->terminalId = $conf->terminalId;
        $this->userName = $conf->userName;
        $this->userPassword = $conf->userPassword;
    }

    public function init($input): array
    {
        $err = null;

        try {
            $client = new \SoapClient(self::GATEWAY_URL);
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => 'soap failed'
            ];
        }

        $parameters = [
            'terminalId'   => $this->terminalId,
            'userName'     => $this->userName,
            'userPassword' => $this->userPassword,
            'orderId'      => $input->order_id,
            'amount'       => $input->price,
            'localDate'    => date('Ymd'),
            'localTime'    => date('His'),
            'callBackUrl'  => $input->callback,
//            'payerId'        => 0,
//            'additionalData' => '',
        ];

        try {
            $result = $client->bpPayRequest($parameters, self::GATEWAY_NAMESPACE);
        } catch (Exception $e) {
            throw new LogicException(__('accounting.exceptions.gateway.bank.soap_connection'), 403);
        }

        // Display the result
        $res = explode(',', $result->return);
        $ResCode = $res[0];

        if ($ResCode === '0') {
            return [
                'status' => true,
                'data'   => [
                    'status'     => 'success',
                    'url'        => self::REDIRECT_URL,
                    'post_name'  => 'RefId',
                    'post_value' => $res[1],
                    'template'   => 'web.payment.gateway.bank_redirect'
                ]
            ];
        }

        return [
            'status'  => false,
            'message' => $this->error
        ];
    }


    public function callback(array $input): array
    {
        $refId = $input['RefId'];
        $ResCode = $input['ResCode'];
        $CardHolderPan = $input['CardHolderPan'];
        $SaleOrderId = $input['SaleOrderId'];
        $SaleReferenceId = $input['SaleReferenceId'];

        try {

            if ($ResCode === '0') {
                try {
                    $client = new \SoapClient(self::GATEWAY_URL);
                } catch (LogicException $e) {
                    return [
                        'status'  => false,
                        'message' => 'soap failed'
                    ];
                }

                $parameters = [
                    'terminalId'      => $this->terminalId,
                    'userName'        => $this->userName,
                    'userPassword'    => $this->userPassword,
                    'orderId'         => (float)$SaleOrderId,
                    'saleOrderId'     => (float)$SaleOrderId,
                    'saleReferenceId' => (float)$SaleReferenceId
                ];

                $result = $client->bpVerifyRequest($parameters, self::GATEWAY_NAMESPACE);

                $res = @explode(',', $result->return);

                if (is_array($res)) {
                    $ResCode = $res[0];

                    if ($ResCode == "0") {
                        $result_settle = $client->bpSettleRequest($parameters, self::GATEWAY_NAMESPACE);
                        $result_Strsettle = $result_settle->return;
                        $res_settle = @explode(',', $result_Strsettle);
                        $ResCodesettle = $res_settle[0];

                        if ($ResCodesettle == "0") {
                            $RefNum = $input['RefNum'] ? $input['RefNum'] : $input['RefId'];

                            $lastOrder = $this->DA_Payment->sp_profile_payment_one_by_ref_number_r($RefNum, 0);

                            if ($lastOrder) {
                                throw new LogicException(__("accounting.exceptions.payment.general.pay_is_duplicate"), 403);
                            }

                            return [
                                "type" => "view",
                                "data" => [
                                    "status"             => "dopay",
                                    "show_after_payment" => true,
                                    "package_data"       => $package_data,
                                    "template"           => "web.payment.package.success"
                                ]
                            ];
                        }
                    }
                }
            } else {
                throw new LogicException(__('accounting.exceptions.gateway.bank.canceled_by_user'));
            }

            throw new LogicException(__('accounting.exceptions.gateway.bank.not_verified'));

        } catch (LogicException $e) {
            return [
                'status' => false,
                'data' => [
                    'message'  => $e->getMessage(),
                    'template' => 'web.payment.fail',
                ]
            ];
        }

    }
}