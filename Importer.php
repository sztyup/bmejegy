<?php

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Importer
{
    protected function fetchExternal()
    {
        $client = new Client([
            RequestOptions::COOKIES => true,
            RequestOptions::ALLOW_REDIRECTS => true
        ]);

        // Get the login page to grab nonce
        $response = $client->get('https://www.bmejegy.hu/fiokom/');

        // Login
        $client->post('https://www.bmejegy.hu/fiokom/', [
            'form_params' => [
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'login' => 'Bejelentkezés',
                '_wp_http_referer' => '/fiokom/',
                'woocommerce-login-nonce' => $this->extractNonceFromResponse($response)
            ]
        ]);

        $client->get('https://www.bmejegy.hu/rendeles-riportok/');

        $result = $client->post(
            'https://www.bmejegy.hu/wp-admin/admin-ajax.php?action=onliner_ajax&onliner_ajax_action=jqgrid_order_result',
            [
                'form_params' => [
                    '_search' => false,
                    'nd' => 1469882453853,
                    'rows' => 100000,
                    'page' => 1,
                    'sidx' => '',
                    'sord' => 'asc'
                ]
            ]
        );

        return json_decode($result->getBody(), true);
    }

    protected function extractNonceFromResponse(ResponseInterface $response)
    {
        $content = $response->getBody()->getContents();

        preg_match_all('/name\=\"woocommerce\-login\-nonce\" value\=\"([a-zA-Z0-9]*)\"/', $content, $matches);

        return $matches[1][0] ?? null;
    }
}