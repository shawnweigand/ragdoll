<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TrelloService
{
    protected string $apiKey;
    protected string $apiToken;
    protected string $baseUrl;
    protected string $boardId;

    public function __construct()
    {
        $this->apiKey = config('services.trello.key');
        $this->apiToken = config('services.trello.token');
        $this->boardId = config('services.trello.board_id');
        $this->baseUrl = 'https://api.trello.com/1';
    }

    public function getCards()
    {
        $url = $this->baseUrl . '/boards/' . $this->boardId . '/cards';

        $response = Http::get($url, [
            'key' => $this->apiKey,
            'token' => $this->apiToken,
        ]);

        return json_decode($response, true);
    }

    public function getCard(string $cardId)
    {
        $url = $this->baseUrl . '/cards/' . $cardId;

        $response = Http::get($url, [
            'key' => $this->apiKey,
            'token' => $this->apiToken,
        ]);

        return json_decode($response, true);
    }

    public function search(string $query)
    {
        $url = $this->baseUrl . '/search';

        $response = Http::get($url, [
            'key' => $this->apiKey,
            'token' => $this->apiToken,
            'query' => $query,
            'modelTypes' => 'cards',
            'card_fields' => 'shortLink,name,desc,labels,idList,start,due,shortUrl',
        ]);

        return json_decode($response, true);
    }
}