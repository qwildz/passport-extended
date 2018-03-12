<?php

namespace Qwildz\PassportExtended\Console;

use Illuminate\Console\Command;
use Qwildz\PassportExtended\Passport;

class GenerateClientKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:clientkey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate key (client_key) for clients which the key is null';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Passport::generateClientKey();
        $this->info('Client\'s keys completely generated');
    }
}
