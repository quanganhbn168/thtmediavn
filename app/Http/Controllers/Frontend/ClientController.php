<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Client;
use Illuminate\View\View;

class ClientController extends FrontendController
{
    public function index(): View
    {
        $clients = Client::query()->where('is_active', true)->with(['media', 'projects.services', 'projects.slugs'])
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('frontend.clients.index', [
            'clientGroups' => $clients->groupBy(fn (Client $client) => $client->industry ?: 'Ngành khác'),
            'clientSchemaItems' => $clients->map(fn (Client $client): array => [
                'name' => $client->getTranslation('name', app()->getLocale()),
                'url' => route('clients.index').'#client-'.$client->id,
            ])->all(),
        ]);
    }
}
