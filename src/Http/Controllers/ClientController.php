<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Laravel\Passport\Http\Controllers\ClientController as PassportClientController;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Qwildz\PassportExtended\ClientRepository;

class ClientController extends PassportClientController
{

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

}
