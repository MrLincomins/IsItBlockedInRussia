<?php

namespace guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


class IsItBlockedRknDetector implements RknDetectorInterface
{
    protected Client $client;
    protected string $url = '192.168.1.24';
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
            $items = (json_decode($body->getBody(), true));
            $responce = $items[0]['responce'];
            $data = ['in_black_list' => $responce];
            $this->response->setIsBlocked($data);
        } catch (GuzzleException| \Exception $e) {
            $this->response->setException($e);
        }
        return $this->response;
    }

    public function checkHosts(array $hosts): RknResponse
    {
        try {
            $body = $this->client->request('POST', $this->url, [
                'body' => json_encode([
                    'host' => $hosts,
                ]),
                'headers' => [
                    'Content-Type' => 'application/json',
                ]]);
            $result = [];
            $items = (json_decode($body->getBody(), true));
            foreach ($items as $i => $data){
                $response = $data[0]['responce'];
                $host = $hosts[$i];
                $result[$host] = $response;
            }
            $this->response->setIsBlocked($result);
        } catch (GuzzleException| \Exception $e) {
            $this->response->setException($e);
        }
        return $this->response;
    }
    //Добавляет данные (Ошибка или True, False) в класс RknResponse.
}