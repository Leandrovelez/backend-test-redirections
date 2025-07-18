<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_redirect_com_url_valida()
    {
        $response = $this->postJson('/api/redirects', [
            'url' => 'https://www.google.com',
            'status' => 'ativo'
        ]);
        $response->assertStatus(201)
                 ->assertJsonStructure(['code', 'url', 'status']);
    }

    public function test_cria_redirect_com_dns_invalido()
    {
        $response = $this->postJson('/api/redirects', [
            'url' => 'https://url-inexistente-123456-xpto.com',
            'status' => 'ativo'
        ]);
        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'A URL deve retornar status 200 ou 201.']);
    }

    public function test_cria_redirect_com_url_invalida()
    {
        $response = $this->postJson('/api/redirects', [
            'url' => 'not-a-url',
            'status' => 'ativo'
        ]);
        $response->assertStatus(422)
                 ->assertJsonFragment(['errors' => ['url' => ['A URL deve ser um endereço válido']]]);
    }

    public function test_cria_redirect_com_url_apontando_para_aplicacao()
    {
        $appUrl = config('app.url');
        $response = $this->postJson('/api/redirects', [
            'url' => $appUrl,
            'status' => 'ativo'
        ]);
        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'A URL não pode apontar para a própria aplicação.']);
    }

    public function test_cria_redirect_com_url_sem_https()
    {
        $response = $this->postJson('/api/redirects', [
            'url' => 'http://www.google.com',
            'status' => 'ativo'
        ]);
        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'A URL deve ser https.']);
    }

    public function test_cria_redirect_com_url_status_diferente_de_200_ou_201()
    {
        $response = $this->postJson('/api/redirects', [
            'url' => 'https://leandrovelez.com',
            'status' => 'ativo'
        ]);
        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'A URL deve retornar status 200 ou 201.']);
    }
}
