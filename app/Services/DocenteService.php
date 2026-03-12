<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DocenteService
{
    protected $baseUrl = 'https://sad.umsedomex.gob.mx/api';

    public function getDocentes($page = 1)
    {
        return Http::withToken(env('SAD_API_TOKEN'))
            ->get("{$this->baseUrl}/docentes?page={$page}")
            ->json();
    }

    public function getDocente($id)
    {
        return Http::withToken(env('SAD_API_TOKEN'))
            ->get("{$this->baseUrl}/docentes/{$id}")
            ->json();
    }

    public function createDocente($data)
    {
        return Http::withToken(env('SAD_API_TOKEN'))
            ->post("{$this->baseUrl}/docentes", $data)
            ->json();
    }

    public function updateDocente($id, $data)
    {
        return Http::withToken(env('SAD_API_TOKEN'))
            ->put("{$this->baseUrl}/docentes/{$id}", $data)
            ->json();
    }

    public function deleteDocente($id)
    {
        return Http::withToken(env('SAD_API_TOKEN'))
            ->delete("{$this->baseUrl}/docentes/{$id}")
            ->json();
    }
}
