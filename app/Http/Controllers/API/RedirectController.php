<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Models\Redirect;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class RedirectController extends Controller
{
    protected $rules = [
        'status' => 'sometimes|in:ativo,inativo',
        'url' => 'required|url|max:2048',
    ];

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'O status deve ser "ativo" ou "inativo"',
            'url.url' => 'A URL deve ser um endereço válido',
            'url.max' => 'A URL não pode exceder 2048 caracteres',
            'url.required' => 'A URL é obrigatória'
        ];
    }

    /**
     * Fetch all redirects
     *
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Redirect $redirect)
    {
        $data = $redirect->toArray();
        $data['code'] = $redirect->code;
        unset($data['id']);

        return response()->json($data);
    }

    /**
     * Create a new redirect
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->only(['url', 'status']), $this->rules, $this->messages());
            
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $urlValidation = $this->urlValidation($request->url);
            
            if (! $urlValidation['isValid']) {
                return response()->json([
                    'message' => $urlValidation['message']
                ], 422);
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
                'message' => 'A URL deve retornar status 200 ou 201.'
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Redirect $redirect, Request $request)
    {
        try{
            $validator = Validator::make($request->only(['url', 'status']), $this->rules, $this->messages());
            
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $urlValidation = $this->urlValidation($request->url);
            
            if (! $urlValidation['isValid']) {
                return response()->json([
                    'message' => $urlValidation['message']
                ], 422);
            }

            if($redirect->update($request->only(['url', 'status']))) {
                $data = $redirect->toArray();
                unset($data['id']);
                $data['code'] = $redirect->code;

                return response()->json($data, 201);
            }

            return response()->json(['message' => 'Erro ao criar o redirect'], 500);
        } catch (ConnectionException | RequestException $e) {
            return response()->json([
                'message' => 'A URL deve retornar status 200 ou 201.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro inesperado ao atualizar o redirect.',
                'erro' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a redirect
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Redirect $redirect)
    {
        if($redirect->delete()) {
            return response()->json(['message' => 'Redirect deletado com sucesso'], 200);
        } else {
            return response()->json(['message' => 'Erro ao deletar o redirect'], 500);
        }
    }

     /**
     * Verify the given URL
     *
     * @return array
     */
    public function urlValidation($url){
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);
        if ($appHost === $urlHost) {
            return ['isValid' => false, 'message' => 'A URL não pode apontar para a própria aplicação.'];
        }

        if (strpos($url, 'http://') === 0) {
            return ['isValid' => false, 'message' => 'A URL deve ser https.'];
        }

        $response = Http::get($url);

        if ($response->notFound()) {
            return ['isValid' => false, 'message' => 'A URL não foi encontrada (404).'];
        }

        if ($response->ok() || $response->created()){
            return ['isValid' => true, 'message' => 'A URL é válida.'];
        } else {
            return ['isValid' => false, 'message' => 'A URL deve retornar status 200 ou 201.']; 
            
        }
    }

    /**
     * Search for redirection statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Redirect $redirect)
    {
        $total = $redirect->logs()->count();
        $totalUnique = $redirect->logs()->distinct('ip')->count('ip');

        $topReferer = $redirect->logs()
            ->select('header_referer', DB::raw('count(*) as total'))
            ->whereNotNull('header_referer')
            ->groupBy('header_referer')
            ->orderByDesc('total')
            ->limit(1)
            ->get();

        $last10DaysLogs = $redirect->logs()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw('count(distinct ip) as unique_ips')
            )
            ->where('created_at', '>=', now()->subDays(10)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'total_access' => $total,
            'total_unique_ip' => $totalUnique,
            'top_referer' => $topReferer,
            'last_10_days' => $last10DaysLogs,
        ]);
    }
    
    /**
     * Fetch all logs of a redirect
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logs(Redirect $redirect, PaginateRequest $request)
    {
        $request->validated();

        $perPage = $request->query('per_page', 10);
        $redirectLogs = $redirect->logs()->paginate($perPage);

        $redirectLogs->getCollection()->transform(function ($log) {
            $data = $log->toArray();
            $data['redirect_code'] = $log->redirect->code;
            unset($data['redirect_id']);
            return $data;
        });
    
        return response()->json($redirectLogs);
    }
}
