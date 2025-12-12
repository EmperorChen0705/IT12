<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\StockIn;

class WipeStockIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:wipe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe all data from the stock_in table (Truncate)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('This will permanently delete ALL records in the "stock_in" table. It will NOT revert item quantities. Do you wish to continue?')) {

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('stock_in')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('Stock In data has been wiped successfully.');
        }
    }
}
