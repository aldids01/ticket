<?php

namespace App\Services;
use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Request;
use PEAR2\Net\RouterOS\ResponseCollection;
use PEAR2\Net\RouterOS\ResponseException;

class MikroTikService
{
    protected $client;
    public function __construct()
    {
        $this->client = new Client(env('MIKROTIK_HOST'), env('MIKROTIK_USER'), env('MIKROTIK_PASSWORD'));
    }
    public function getActiveUsers(): array|ResponseCollection
    {
        try {
            return $this->client->sendSync(new Request('/ppp/active/print'));
        } catch (ResponseException $e) {
            return [];
        }
    }

    public function disconnectUser($user): bool
    {
        try {
            $this->client->sendSync(new Request('/ppp/active/remove', ['.id' => $user]));
            return true;
        } catch (ResponseException $e) {
            return false;
        }
    }
}
