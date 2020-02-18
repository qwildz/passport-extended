<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Http\Controllers\ClientController as PassportClientController;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Laravel\Passport\Http\Rules\RedirectRule;
use Qwildz\PassportExtended\ClientRepository;

class ClientController extends PassportClientController
{
    /**
     * The client repository instance.
     *
     * @var ClientRepository
     */
    protected $clients;

    /**
     * ClientController constructor.
     * @param ClientRepository $clients
     * @param ValidationFactory $validation
     * @param RedirectRule $redirectRule
     */
    public function __construct(
        ClientRepository $clients,
        ValidationFactory $validation,
        RedirectRule $redirectRule
    ) {
        parent::__construct($clients, $validation, $redirectRule);
    }

    public function store(Request $request)
    {
        $this->validation->make($request->all(), [
            'name' => 'required|max:255',
            'redirect' => ['required', $this->redirectRule],
            'confidential' => 'boolean',
        ])->validate();

        return $this->clients->create(
            $request->user()->getKey(), $request->name, $request->redirect, false, false,
            (bool) $request->input('confidential', true), $request->trusted, $request->sso, $request->slo
        )->makeVisible('secret');
    }

    public function update(Request $request, $clientId)
    {
        $client = $this->clients->findForUser($clientId, $request->user()->getKey(), false);

        if (! $client) {
            return new Response('', 404);
        }

        $this->validation->make($request->all(), [
            'name' => 'required|max:255',
            'redirect' => ['required', $this->redirectRule],
        ])->validate();

        return $this->clients->update2(
            $client, $request->name, $request->redirect, $request->trusted, $request->sso, $request->slo
        );
    }
    
    public function destroy(Request $request, $clientId)
    {
        $client = $this->clients->findForUser($clientId, $request->user()->getKey(), false);

        if (! $client) {
            return new Response('', 404);
        }

        $this->clients->delete($client);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
