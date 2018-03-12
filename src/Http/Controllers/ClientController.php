<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Http\Controllers\ClientController as PassportClientController;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
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
     */
    public function __construct(ClientRepository $clients,
                                ValidationFactory $validation)
    {
        parent::__construct($clients, $validation);
    }

    public function store(Request $request)
    {
        $this->validation->make($request->all(), [
            'name' => 'required|max:255',
            'redirect' => 'required|url',
        ])->validate();

        return $this->clients->create(
            $request->user()->getKey(), $request->name, $request->redirect, false, false,
            $request->trusted, $request->sso
        )->makeVisible('secret');
    }

    public function update(Request $request, $clientId)
    {
        $client = $this->clients->findForUser($clientId, $request->user()->getKey());

        if (! $client) {
            return new Response('', 404);
        }

        $this->validation->make($request->all(), [
            'name' => 'required|max:255',
            'redirect' => 'required|url',
        ])->validate();

        return $this->clients->update2(
            $client, $request->name, $request->redirect, $request->trusted, $request->sso
        );
    }

}
