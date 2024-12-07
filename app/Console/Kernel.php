<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\MonthlySuspension::class,
        Commands\GenerateCableTVMonthlyInvoices::class,
        Commands\GenerateInternetMonthlyInvoices::class,
        Commands\UploadMonthlyDMCFilesToFTPServer::class,
        Commands\UploadMonthlyIDCFilesToFTPServer::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /** Run a monthly suspend subscribers on 19th at 07:00 AM of each month **/
        //$schedule->command('suspend:monthly')->monthlyOn(19, '07:00')->timezone(env('TIME_ZONE'));
        /** Run a generate internet monthly invoices on 25th at 04:30 PM of each month **/
        $schedule->command('generateInternetInvoices:monthly')->monthlyOn(25, '16:30')->timezone(env('TIME_ZONE'));
        /** Run a upload internet monthly files to FTP server on 25th at 04:45 PM of each month **/
        $schedule->command('uploadMonthlyDMCFilesToFTPServer:monthly')->monthlyOn(25, '16:45')->timezone(env('TIME_ZONE'));
        /** Run a upload internet monthly files to FTP server for IDC on 25th at 04:50 PM of each month **/
        $schedule->command('uploadMonthlyIDCFilesToFTPServer:monthly')->monthlyOn(25, '16:50')->timezone(env('TIME_ZONE'));
        /** Run a generate cable tv monthly invoices on 01st at 08:00 AM of each month **/
        $schedule->command('generateCableTVInvoices:monthly')->monthlyOn(1, '08:00')->timezone(env('TIME_ZONE'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
