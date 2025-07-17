<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\RedirectRequest;
use App\Models\Redirect;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class RedirectController extends Controller
{
    /**
     * Fetch all redirects
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(PaginateRequest $request)
    {
        $request->validated();

        $perPage = $request->query('per_page', 10);
        $redirects = Redirect::paginate($perPage);

        $redirects->getCollection()->transform(function ($redirect) {
            $data = $redirect->toArray();
            $data['code'] = $redirect->code;
            unset($data['id']);
            return $data;
        });
    
        return response()->json($redirects);
    }

    /**
     * Fetch one redirect
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(Redirect $redirect)
    {
        return redirect()->route('home');
    }

    /**
     * Create a new redirect
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RedirectRequest $request)
    {
        try{
            $request->validated();

            $response = Http::get($request->url);
            
            if ($response->notFound()) {
                return response()->json([
                    'message' => 'A URL não foi encontrada (404)'
                ], 422);
            }

            if ($response->forbidden()) {
                return response()->json([
                    'message' => 'A URL retornou acesso proibido (403)'
                ], 422);
            }

            if (!$response->ok()) {
                return response()->json([
                    'message' => 'A URL não está acessível. Status retornado: ' . $response->status()
                ], 422);
            }

            if (strpos($request->url, 'http://') === 0) {
                return response()->json(['message' => 'A URL deve ser https'], 422);
            }

            if($redirect = Redirect::create($request->only(['url', 'status']))) {
                $data = $redirect->toArray();
                unset($data['id']);
                $data['code'] = $redirect->code;

                return response()->json($data, 201);
            }

            return response()->json(['message' => 'Erro ao criar o redirect'], 500);
        } catch (ConnectionException | RequestException $e) {
            return response()->json([
                'message' => 'Não foi possível acessar a URL informada.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro inesperado ao criar o redirect.'
            ], 500);
        }
    }


    /**
     * Update a redirect
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Redirect $redirect)
    {
        return redirect()->route('home');
    }

    /**
     * Delete a redirect
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Redirect $redirect)
    {
        return redirect()->route('home');
    }
}
