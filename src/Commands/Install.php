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

        $this->call('vendor:publish', ['--tag' => 'scrud']);

        # Rename the namespace
        if (file_exists($this->layout_path)) {
            $layout = file_get_contents($this->layout_path);
            $newlayout = str_replace('Sensy\\Scrud\\View\\Components', 'App\\Scrud\\View', $layout);
            file_put_contents($this->layout_path, $newlayout);
        }

    }
}
