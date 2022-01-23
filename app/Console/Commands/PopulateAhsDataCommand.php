<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateAhsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:ahs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate Bunch of Sample AHS\'es Data';

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

        $dataList = scandir('database/sample-data');

        foreach ($dataList as $key => $dirName) {
            if ($key > 1) DB::unprepared(file_get_contents(base_path('database/sample-data/' . $dirName)));
        }

        $this->info('Populate Sample AHS data successfully !');

        return 0;
    }
}
