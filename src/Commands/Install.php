<?php

namespace Sensy\Scrud\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    public $layout_path;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's-crud:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Crud';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->layout_path = app_path('/Scrud/View/AdminLayout.php');

        $this->call('vendor:publish', ['--tag' => 'scrud', '--force' => true]);

//        # Rename the namespace
//        foreach (glob(__DIR__ . '/../View/Components/*.php') as $file) {
//            //get file
//            # open file
//            $file_content = file_get_contents($file);
//            //replace namespace
//            $file_content = str_replace('Sensy\Scrud\View\Components', 'App\Scrud\View', $file_content);
//            //save file
//            file_put_contents($layout_path . '/' . basename($file), $file_content);
//        }

    }
}
