<?php

namespace App\Controllers;

use App\DataAccess\ClientAccountGatewayDataAccess;
use App\DataAccess\GatewayDataAccess;
use App\DataAccess\TokenDataAccess;
use Illuminate\Support\Str;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @property \App\DataAccess\ClientModel client
 * @property \App\DataAccess\GatewayDataAccess GatewayDataAccess
 * @property \App\DataAccess\TokenDataAccess TokenDataAccess
 * @property \App\DataAccess\ClientAccountGatewayDataAccess ClientAccountGatewayDataAccess
 */
class GatewayController extends Controller
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->GatewayDataAccess = new GatewayDataAccess($container);
        $this->TokenDataAccess = new TokenDataAccess($container);
        $this->ClientAccountGatewayDataAccess = new ClientAccountGatewayDataAccess($container);
    }

    public function getGateways(Request $request, Response $response, $args)
    {
        // TODO
        // 1 - auth in middleware
        // 2 - query the enable gateways of client
//        $gateways = $GatewayDataAccess->getGateways(['id' => $this->client->id]);
        $gateways = $this->GatewayDataAccess->getGateways(['id' => 1]);


        return $response->withJson(['status' => true, 'data' => $gateways]);
    }

    public function init(Request $request, Response $response, $args)
    {
        $order_id = $request->getParam('order_id');
        $price = $request->getParam('price');
        $callback = $request->getParam('callback');
        $mobile = $request->getParam('mobile');
        $account_id = $request->getParam('account_id');
        //TODO check account id is for this user
        // validate mobile and other params

        $token = Str::random(10);
        $res = $this->TokenDataAccess->create([
            'token' => $token,
            'price' => $price,
            'callback' => $callback,
            'order_id' => $order_id,
            'mobile' => $mobile,
            'client_account_gateway_id' => $account_id,
        ]);

        if ($res) {
            return $response->withJson(['status' => true, 'token' => 'tok_' . $token]);
        }

        return $response->withJson(['status' => false, 'message' => 'fail']);
    }

    public function gotobank(Request $request, Response $response, $args)
    {
        $token = $args['token'];

        if (strpos($token, 'tok_') !== 0) {
            return $response->withJson(['status' => false, 'message' => 'invalid token']);
        }

        $token = str_replace('tok_', '', $token);

        $res = $this->TokenDataAccess->selectByToken([
            'token' => $token,
        ]);

        //todo replace with db time , get db time from previous query
        $valid_until = date('Y-m-d H:i:s', strtotime('-5 minute', strtotime(date('Y-m-d H:i:s'))));

//        if ($res->created_at < $valid_until) {
//            return $response->withJson(['status' => false, 'message' => 'token expire']);
//        }

        $account_gateway = $this->ClientAccountGatewayDataAccess->get??Info(['id' => $res->client_account_gateway_id]);
        if (!$account_gateway) {
            return $response->withJson(['status' => false, 'message' => 'account not found']);
        }


        $gateway = $this->GatewayDataAccess->gatewayInfo(['id' => $account_gateway->gateway_id]);

        if (!$gateway || $gateway->status === 'disable') {
            return $response->withJson(['status' => false, 'message' => 'gateway not available or not found']);
        }


        $class = 'App\Gateways\\' . $gateway->class_name;
        $conf = (object) array_merge(json_decode($account_gateway->config, true), ['callback' => 'https://ipg.local/callback/' . str_replace('GatewayController', '', $gateway->class_name)]);
        $gatewayObject = new  $class($this->container, $conf);
        $gatewayResult = $gatewayObject->init($res);

        if ($gatewayResult['status'] === true) {
            $this->TokenDataAccess->updateStatus([
                'id' => $res->id,
                'status' => 'pending',
            ]);


            if ($gatewayResult['type'] === 'formSubmit') {
                return $this->view->render($response, 'saman', $gatewayResult['data']);
            }
        }


        return $response->withJson(['status' => false, 'message' => $gatewayResult['message']]);
    }


    public function callback(Request $request, Response $response, $args)
    {
        $gatewayName = $args['gateway'];

        $gateway = $this->GatewayDataAccess->allGatewaysInfo(['class_name' => $gatewayName . 'GatewayController']);
        if (!$gateway) {
            return $response->withJson(['status' => false, 'message' => 'gateway not available or not found']);
        }

        $order_id = $request->getParam($gateway->order_id_property);

        $res = $this->TokenDataAccess->selectById([
            'id' => $order_id,
        ]);

        if (!$res) {
            return $response->withJson(['status' => false, 'message' => 'order not found']);
        }

        if ($res->status === 'paid') {
            return $response->withJson(['status' => false, 'message' => 'order paid']);
        }

        $account_gateway = $this->ClientAccountGatewayDataAccess->get??Info(['id' => $res->client_account_gateway_id]);
        if (!$account_gateway) {
            return $response->withJson(['status' => false, 'message' => 'account not found']);
        }

        $gateway = $this->GatewayDataAccess->gatewayInfo(['id' => $account_gateway->gateway_id]);
        if (!$gateway) {
            return $response->withJson(['status' => false, 'message' => 'gateway not available or not found']);
        }

        $class = 'App\Gateways\\' . $gateway->class_name;
        $conf = (object) array_merge(json_decode($account_gateway->config, true), ['callback' => '']);
        $gatewayObject = new $class($this->container, $conf);
        $gatewayResult = $gatewayObject->callback($request->getParams());

        if ($gatewayResult['status'] === true) {
            $this->TokenDataAccess->updateStatus([
                'id' => $order_id,
                'status' => 'paid',
            ]);
            $tokenObject = $this->TokenDataAccess->selectById(['id' => $order_id]);
            $data = array_merge($gatewayResult, ['callback' => $tokenObject->callback, 'token' => $tokenObject->token]);
            return $this->view->render($response, 'callback', $data);
        }

        $this->TokenDataAccess->updateStatus([
            'id' => $order_id,
            'status' => 'failed',
        ]);

        return $response->withJson(['status' => false, 'message' => 'fail']);
    }


}