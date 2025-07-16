<?php

namespace App\Http\Controllers;

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
    public function index(RedirectRequest $request)
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
