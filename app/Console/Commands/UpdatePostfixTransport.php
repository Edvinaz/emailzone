<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdatePostfixTransport extends Command
{
    protected $signature = 'postfix:update-transport {domain} {mailserver}';
    protected $description = 'Update Postfix transport file with new routing';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $domain = $this->argument('domain');
        $mailserver = $this->argument('mailserver');
        $filePath = '/etc/postfix/transport';
//        $filePath = '/home/edvinas/Documents/transport';

        if (!File::exists($filePath)) {
            $this->error("Transport file does not exist.");
            return 1;
        }

        // Append new routing rule
        $newRule = "$domain $mailserver\n";
        File::append($filePath, $newRule);

        // Reload Postfix configuration
        exec('postmap /etc/postfix/transport');
        exec('systemctl reload postfix');

        $this->info("Transport file updated and Postfix reloaded.");
        return 0;
    }
}
