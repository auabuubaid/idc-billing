<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InternetHistory;

// CORE NETWORK
use \RouterOS\Query;
use \RouterOS\Config;
use \RouterOS\Client;

class MonthlySuspension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suspend:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The subscribers who not paid internet fee till 19 of each month update their internet profile as "Expired-Alert" on Core Netwrok';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ihistory = new InternetHistory;
        $invoiceDate = previousYearMonth(currentYearMonth()).'-25';
        $notPaidArr = $ihistory->select('customer_id', 'address_id', 'plan_id')->where('entry_type', 'MP')->whereDate('monthly_invoice_date',$invoiceDate)->where('paid', 'N')->get()->toArray();
        
        $config = (new Config())
            ->set('timeout', 1)
            ->set('host', env('CORE_NETWORK_HOST'))
            ->set('user', env('CORE_NETWORK_USER'))
            ->set('pass', env('CORE_NETWORK_PASSWORD'));
            $client = new Client($config);

        if(is_array($notPaidArr) && count($notPaidArr)>0)
        {
            foreach ($notPaidArr as $nval) 
            {
                #Remove from PPP Active connection 
                $query = new Query('/ppp/active/print');
                $query->where('name', customerDetailsByID($nval['customer_id'])['internet_id']);
                $pppActive = $client->query($query)->read();
                $query = (new Query('/ppp/active/remove'))
                        ->equal('.id', @$pppActive[0]['.id']);
                $client->query($query)->read();
        
                #Update internet profile to Expired-Alert in PPP Secret connection
                $query = new Query('/ppp/secret/print');
                $query->where('name', customerDetailsByID($nval['customer_id'])['internet_id']);
                $subscriberSecret = $client->query($query)->read();                
                $query = (new Query('/ppp/secret/set'))
                        ->equal('.id', @$subscriberSecret[0]['.id'])
                        ->equal('profile', env('EXPIRED_PLAN'));
                $client->query($query)->read();
            }
        }
         
        $this->info('The subscribers who not paid internet fee till 19 of each month update their internet profile as "Expired-Alert" on Core Netwrok');
    }
}
