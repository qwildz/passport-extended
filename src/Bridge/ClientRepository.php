<?php

namespace Qwildz\PassportExtended\Bridge;

use Laravel\Passport\Bridge\ClientRepository as PassportClientRepository;
use Qwildz\PassportExtended\ClientRepository as ClientModelRepository;

class ClientRepository extends PassportClientRepository
{
    /**
     * Create a new repository instance.
     *
     * @param \Laravel\Passport\ClientRepository|ClientModelRepository $clients
     */
    public function __construct(ClientModelRepository $clients)
    {
        parent::__construct($clients);
    }
}
