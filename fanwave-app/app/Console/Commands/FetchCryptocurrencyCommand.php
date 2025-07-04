<?php

namespace App\Console\Commands;

use App\Jobs\FetchCryptocurrencyData;
use Illuminate\Console\Command;

class FetchCryptocurrencyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:fetch {--sync : Run synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch cryptocurrency data from CoinGecko API and store in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching cryptocurrency data from CoinGecko API...');
        
        if ($this->option('sync')) {
            // Run synchronously for immediate execution
            $this->info('Running synchronously...');
            $job = new FetchCryptocurrencyData();
            $job->handle();
            $this->info('✅ Cryptocurrency data fetch completed!');
        } else {
            // Queue the job for background processing
            $this->info('Queueing job for background processing...');
            FetchCryptocurrencyData::dispatch();
            $this->info('✅ Cryptocurrency data fetch job has been queued!');
            $this->comment('💡 Make sure your queue worker is running: php artisan queue:work');
        }
        
        return Command::SUCCESS;
    }
}
