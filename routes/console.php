<?php

use App\Services\TrelloService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('trello', function () {
    $trello = new TrelloService();
    // $response = $trello->getCards();
    // $response = $trello->getCard('piXpwxOr');
    $response = $trello->search('prism');
    dd(collect($response['cards']));
});