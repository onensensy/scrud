<?php

namespace Sensy\Scrud\Traits;

use App\Models\SystemModule;
use DirectoryIterator;

use Illuminate\Support\Str;

trait CrudTrait
{

    /** @param array $special Model name */
    protected $special = ['User', 'Role', 'SystemModule', 'Menu', 'SubMenu', 'Permission', 'Session', 'PasswordResetToken'];
    /** @param array $exclude_models  Model name */
    protected $exclude_models = ['Session', 'PasswordResetToken'];

    protected $routePath = 'routes/web.php';

    /**
     * Generate model
     * @param string $class Model name
     */
    private function migrationExists($class)
    {
        $table_name = Str::plural(Str::snake($class));

        $path = database_path("migrations/*_create_{$table_name}_table.php");
        $files = glob($path);

        return count($files) > 0 ? $files[0] : null;
    }

    /**
     * Get the stub file path
     * @param string $type
     * @param string|null $name
     * @return string
     */
    private function getStubPath($type = null, $class = null, $view = false)
    {
        if ($type == 'route')
        {
            $stub = __DIR__ . "/../stubs/routes/web.stub";
            return $stub;
        }
        if (!$view)
        {
            $class = $this->toSnake($class);

            $stub_folder = strtolower(Str::plural($type));

            if (is_null($class))
                $class = $type;
            else
                $class = Str::singular($class) . '_' . $type;

            $stub =   __DIR__ . "/../stubs/{$stub_folder}/{$class}.stub";

            #check if the file exists in the speciaal folder
            if (!file_exists($stub))
                $stub = __DIR__ . "/../stubs/{$stub_folder}/{$type}.stub";
        }
        else
        {
            #If a folder exists with the class name, use it
            $stub_folder = $this->viewName($class);
            $stub = __DIR__ . "/../stubs/views/{$stub_folder}/{$type}.stub";

            if (!file_exists($stub))
                $stub = __DIR__ . "/../stubs/views/{$type}.stub";
        }

        return $stub;
    }


    /**
     * Convert string to snake case
     * @param string $input String to convert
     * @param bool $plural Pluralize the string
     * @return string
     */
    public function toSnake($input, $plural = false)
    {
        return $plural ? Str::plural(Str::snake($input)) : Str::snake($input);
    }

    /**
     * This will clean the whole system plus the database
     */
    public function cleanup()
    {
        // TODO:: Add a confirmation

        return $this->info("Going Clean");
    }
    /**
     * This will clean one Class the whole system plus the database
     */
    public function cleanupSingle($class)
    {
        # DATABASE
        $module = SystemModule::whereName($class)->first();
        if (is_null($module))
            return $this->error('No Module Found with that name');

        // dd($module);
        $this->warn('Working on database');
        $this->warn('======================');

        ## Remove from System Module + Menus + Sub Menus
        $module->delete();

        ## Unasign Permissions
        ###PENDING###


        # FILES
        $this->warn('Working on Files');
        $this->warn('======================');
        # Remove controller
        $_cp = app_path('Http/Controllers/' . $class . 'Controller.php');
        $c = file_exists($_cp);
        if ($c) unlink($_cp);
        else
            $this->warn('Controller Does not Exits');

        # Remove views //##CHECK CONFIGS FOR LOCATION
        $_vp = resource_path('views/pages/backend/' . $this->viewName($class));
        $v = is_dir($_vp);
        if ($v) $this->deleteContent($_vp);
        else
            $this->warn('View Path not found');

        # Remove Migration
        # Remove Model

        # Remove Route
        ## Get route file
        $webPath = __DIR__ . "/../" . $this->routePath;

        $web = file_get_contents($webPath);
        $web = str_replace("Route::resource('{$this->viewName($class)}', '{$class}Controller');", '', $web);
        file_put_contents($webPath, $web);

        return $this->info("Going Clean");
    }
    public function deleteContent($path)
    {
        try
        {
            $iterator = new DirectoryIterator($path);
            foreach ($iterator as $fileinfo)
            {
                if ($fileinfo->isDot()) continue;
                if ($fileinfo->isDir())
                {
                    if ($this->deleteContent($fileinfo->getPathname()))
                        @rmdir($fileinfo->getPathname());
                }
                if ($fileinfo->isFile())
                {
                    @unlink($fileinfo->getPathname());
                }
            }
            @rmdir($path);
        }
        catch (\Exception $e)
        {
            // write log
            return false;
        }
        return true;
    }
}
