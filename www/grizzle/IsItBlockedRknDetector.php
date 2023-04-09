<?php

namespace grizzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


class IsItBlockedRknDetector implements RknDetectorInterface
{
    protected Client $client;
    protected string $url = 'http://192.168.1.24/';
    // Вписать url
    public RknResponse $response;

    public function __construct()
    {
        $this->response = new RknResponse();

        $this->client = new Client([
            'timeout' => 10.0,
        ]);
        //Создаёт класс RknResponce и указывает время ожидания guzzle
    }

    public function checkHost(string $host): RknResponse
    {
        try {
            $body = $this->client->request('POST', $this->url, [
                'body' => json_encode([
                    'host' => $host,
                ]),
                'headers' => [
                    'Content-Type' => 'application/json',
                ]]);
            $data = (json_decode($body->getBody(), true));
            $this->response->setIsBlocked($data[0]['responce']);
        } catch (GuzzleException| \Exception $e) {
            $this->response->setException($e);
        }
        return $this->response;
    }
    //Добавляет данные (Ошибка или True, False) в класс RknResponse.
}