<?php

namespace Qwildz\PassportExtended\Http\Controllers;

use Illuminate\Http\Request;
use Qwildz\PassportExtended\Client;
use Qwildz\PassportExtended\Passport;
use Vinkla\Hashids\Facades\Hashids;

trait SSOTrait
{
    protected function isSSONeedLogin(Request $request)
    {
        $client = $this->getClient($request);
        $parameter = $this->getClientParameter($request);
        if(isset($parameter['client_id']) && isset($parameter['state']) && $client) {
            return $request->session()->get('login_' . $parameter['client_id'] . '_' . $parameter['state']) != true && !$client->sso;
        }

        return false;
    }

    protected function setSSOHasLogin(Request $request)
    {
        $parameter = $this->getClientParameter($request);
        if(isset($parameter['client_id']) && isset($parameter['state'])) {
            $request->session()->put('login_' . $parameter['client_id'] . '_' . $parameter['state'], true);
        }
    }

    protected function getClient(Request $request)
    {
        $parameter = $this->getClientParameter($request);
        if(isset($parameter['client_id'])) {
            if(Passport::$usesHashids) {
                $clientId = Hashids::connection(config('passport-extended.client.key_hashid_connection', 'main'))->decode($parameter['client_id'])[0];
            } else {
                $clientId = $parameter['client_id'];
            }

            return Client::find($clientId);
        }

        return null;
    }

    protected function getClientParameter(Request $request)
    {
        if($request->get('client_id')) return $request->toArray();
        $query = parse_url($request->session()->get('url.intended'), PHP_URL_QUERY);
        parse_str($query, $output);
        return $output;
    }
}