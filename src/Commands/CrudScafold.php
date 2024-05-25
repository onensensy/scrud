<?php

namespace Sensy\Crud\Commands;

##REQUIRED
use App\Models\Menu;
use App\Models\SubMenu;
use App\Models\SystemModule;
use Sensy\Crud\Traits\CrudTrait;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
##REQUIRED

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class CrudScafold extends Command
{
    use CrudTrait;

    public $component_name;
    public $dirOption;
    public $ServiceFolder;

    public $show_sub_menus = false;

    public $EXCLUSIONS = [
        'id',
        'created_at',
        'created_by',
        'updated_at',
        'deleted_at',
        'company_id',
        'request_id',
        'workflow_id',
        'workflow_definPition_id',
        'workflow_status',
        'assigned_to',
        'status',
        'app_status',
        'deleted_by',
        'delete_comment',
        'tocken',
        'token',
    ];

    public $COMMENTED_OUT = [];

    public $USER_ID_FILL = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensy:crud
                            {--dir=}
                            {--full}
                            {--class=}
                            {--rm}
                            {--m : Run migration before scafold}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scafold a full CRUD';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelPath = app_path('/Models/');
        $class = $this->option('class') ?? '';
        $migrate = $this->option('m');
        $remove = $this->option('rm');

        if ($migrate)
            $this->call('migrate');

        if ($remove)
        {

            if ($class === '')
            {
                $q =  $this->ask('!!!No Module was selected, would you like to clear everything!!!', false);
                if ($q) $this->ask('!!!I Just Want to make sure YOU WANT TO ====CLEAR EVERYTHING====!!!', false);
                if ($q) return $this->cleanup();
                else return;
            }
            else
            {
                $q =  $this->ask('Proceed to remove [' . $class . '] Module?', false);
                if ($q) return $this->cleanupSingle($class);
                else return;
            }
        }


        #Check Dependencies
        $this->info('Checking Dependencies...');
        if ($this->checkDependencies() === 0)
            return $this->error("Scafold Terminated Unmet Dependencies");
        // dd($class);
        ##Loop through all models
        if ($class === '')
        {
            $to_create = [];
            # Check Get all files from model
            if (File::exists($modelPath))
            {

                $choices = ['Generate New', 'Regenerate Existing', 'Mixed generation', 'Generate Specific ', 'Quit'];
                #Get Scafolded System Modules
                $system_modules = SystemModule::all()->pluck('name')->toArray();
                if ($system_modules)
                {
                    $action = $this->choice('Some modules are already scafolded. How do you wish to proceed?', $choices);

                    if ($action === 'Quit')
                        return $this->warn("Scafold Terminated");

                    if (in_array($action, ['Regenerate Existing', 'Mixed generation', 'Generate Specific ']) || !in_array($action, $choices))
                        return $this->error('Implementation Not supported');
                }


                $files = File::files($modelPath);

                foreach ($files as $file)
                {
                    $service_ = str_replace(".php", "", $file->getFilename());

                    if (!in_array($service_, $system_modules))
                    {
                        $to_create[] = $service_;
                    }
                }
            }
            if (count($to_create) === 0)
                return $this->warn("Scafold Terminated: No New Modules");
        }
        else
        {
            $to_create = [$class];
        }


        foreach ($to_create as $class)
        {
            $this->warn("");
            $this->warn("===========================================");
            $this->warn("======= Scafolding [" . $class . "] =======");
            $this->warn("===========================================");
            #Controller

            # Migrate that specific file

            $this->info('Generating Controller...');
            if ($this->generateController($class) === 0)
            {
                $this->warn("Scafold for [" . $class . "] Terminated");
                continue;
            }

            #Views
            $this->info('Generating Views...');
            if ($this->generateViews($class) === 0)
            {
                $this->warn("Scafold for [" . $class . "] Terminated");
                // return
                continue;
            }

            #Register resource route
            $this->info('Registering Route...');
            if ($this->registerRoutes($class) === 0)
            {
                $this->warn("Scafold for [" . $class . "] Terminated");
                // return
                continue;
            }

            #Generate side Menu for it
            $this->info('Registering Side Menus...');
            if ($this->generateSideMenus($class) === 0)
            {
                $this->warn("Scafold for [" . $class . "] Terminated");
                continue;
            }


            #Generate and assign Permissions
            $this->info('Generating Permissions...');
            if ($this->generatePermissions($class) === 0)
            {
                $this->warn("Scafold for [" . $class . "] Terminated");
                // return
                continue;
            }
        }
    }

    public function getDataType($datatype)
    {
        if (in_array($datatype, ['foreignId', 'foreign', 'unsignedBigInteger']))
            $type =  'select';
        elseif (in_array($datatype, ['string']))
            $type =  'text';
        elseif ($datatype === 'boolean')
            $type = 'checkbox';
        elseif ($datatype === 'longText')
            $type = 'textarea';
        elseif ($datatype === 'dateTime')
            $type = 'date';
        elseif ($datatype === 'time')
            $type = 'time';
        elseif (in_array($datatype, ['integer', 'decimal', 'float', 'double']))
            $type = 'number';
        else
            $type = $datatype;

        return $type;
    }

    public function formatName($attribute)
    {
        return str_replace(' id', '', str_replace('_', ' ', ucwords($attribute)));
    }

    // * NEW

    public function extractAttributesFromMigration($migrationFile)
    {
        # Get the content of the migration file
        $content = file_get_contents($migrationFile);

        # Match column definitions, validations, and relationships
        preg_match_all('/\$table->([a-z_]+)\([\'|"]([a-zA-Z_]+)[\'|"],?([^)]*)\)/i', $content, $matches);

        $data = [];
        foreach ($matches[2] as $key => $match)
        {
            $attribute = (string) $match;

            # Check if the attribute already exists in the array
            $existingAttribute = array_search($attribute, array_column($data, 'attribute'));
            if ($existingAttribute !== false)
            {
                # If the attribute already exists, update its relationship data
                $data[$existingAttribute]['relationship'] = $this->extractRelationship($content, $attribute);
            }
            else
            {
                # If the attribute doesn't exist, add it to the array
                $dataType = (string) $matches[1][$key];
                $validations = $this->extractValidations($content, $attribute);
                $relationship = $this->extractRelationship($content, $attribute);

                $data[] = [
                    'attribute' => $attribute,
                    'datatype' => $dataType,
                    'validations' => $validations,
                    'relationship' => $relationship
                ];
            }
        }

        // dd($data);
        return $data ?? [];
    }

    private function extractValidations($content, $attribute)
    {
        // Define the regex pattern
        $regex_pattern = '/.*\$table->[a-z_]+\([\'|"](' . $attribute . ')[\'|"],?(.*?)\).*/i';

        // Perform the regex match
        preg_match_all($regex_pattern, $content, $matches, PREG_SET_ORDER);
        // dd($matches);
        $validations = [];
        foreach ($matches as $match)
        {
            $method = $match[1];
            $parameters = isset($match[3]) ? trim($match[3]) : '';
            $nullable = strpos($match[0], '->nullable()') !== false;
            $unique = strpos($match[0], '->unique()') !== false;
            $otherValidations = isset($match[5]) ? $match[5] : '';

            // Extracting additional validations
            preg_match_all('/([a-zA-Z]+)(\(([^)]*)\))?/', $otherValidations, $additionalMatches, PREG_SET_ORDER);
            $additionalValidations = [];
            foreach ($additionalMatches as $additionalMatch)
            {
                $validation = $additionalMatch[1];
                $parameter = isset($additionalMatch[3]) ? $additionalMatch[3] : null;
                $additionalValidations[$validation] = $parameter;
            }

            // Construct validation array
            $validation = [
                'attribute' => $attribute,
                // 'method' => $method,
                // 'parameters' => $parameters,
                'nullable' => $nullable,
                'unique' => $unique,
                'additional_validations' => $additionalValidations,
            ];

            $validations[] = $validation;
        }

        return $validations[0];
    }


    private function extractRelationship($content, $attribute)
    {
        $relationship = [];

        // Define the simplified regex pattern
        $regex_pattern = '/\$table->(?:foreignId|foreign)\(["\'](' . $attribute . ')["\']\)' . // Match the attribute with single or double quotes
            '(?:.*?->(?:references)\([\'"]([^\'"]+)?[\'"]\))?' . // Match optional references with its argument
            '(?:.*?->(?:constrained|on)\([\'"]([^\'"]+)?[\'"]\))*' . // Match optional methods and related table in any order
            '/i';

        // Perform the regex match
        preg_match_all($regex_pattern, $content, $matches, PREG_SET_ORDER);
        // Iterate over matches
        foreach ($matches as $match)
        {
            # Get constrained table name
            if (!isset($match[3]) || $match[3] == '')
                $match[3] = $this->getTableNameFromForeignKey($attribute);

            if (!isset($match[2]) || $match[2] == '')
                $match[2] = 'id';

            $relationship = [
                'referenced' => $match[2],
                'constrained' => $match[3],
                'relationship' => $match[3],
            ];
        }
        // dd($relationships);

        return $relationship;
    }

    // * END NEW
    public function generateController(string $class)
    {
        $_controller = "{$class}Controller";
        $controllerFileName = app_path("Http/Controllers/{$_controller}.php");

        if (file_exists($controllerFileName))
        {
            $display = (str_replace(base_path(), '', $controllerFileName));
            $this->info("Controller for model '[$class]' already exists File:'[$controllerFileName]'");
            if (!$confirm = $this->confirm('Override?', true))
                return 0;
        }

        // Load the content of the stub file
        $stubPath = $this->getStubPath('controller', $class);
        $stub = file_get_contents($stubPath);

        // Check if the migration file exists
        $_migration_file = $this->migrationExists($class);
        if (!$_migration_file)
        {
            #Check if its special
            if (!in_array($class, $this->special))
            {
                # code...
                $this->error("");
                $this->error("Migration file not found for model '[$class]' and not in specials list");
                return 0;
            }
            else
                $this->warn("Runnig Special Controller...");
        }
        else
        {
            // Extract attributes from the migration file
            $attributes = $this->extractAttributesFromMigration($_migration_file);

            // Generate passable data for views
            $passable_data = $this->generatePassableData($attributes);
            $_data = $passable_data['_data'];
            $passable_ = $passable_data['passable_'];
            $_data_imports = $passable_data['_data_imports'];

            // Generate attribute strings and validation rules
            $validation = $this->generateValidationRules($attributes, []);

            // Replace placeholders in the stub content with actual values
            $stub = str_replace('{{ studlyModelName }}', $class, $stub);
            $stub = str_replace('{{ validationRules }}', $validation, $stub);
            $stub = str_replace('{{ modelName }}', $class, $stub);
            $stub = str_replace('{{ controllerName }}', $_controller, $stub);

            $stub = str_replace('{{ imports }}', $_data_imports, $stub);
            $stub = str_replace('{{ passable_ }}', $passable_, $stub);
            $stub = str_replace('{{ _dataRetrieve }}', $_data, $stub);

            $stub = str_replace('{{returnView}}', $this->viewname($class), $stub);
            $stub = str_replace('{variable}', $this->variableName($class), $stub);
            $stub = str_replace('{displayName}', $this->displayName($class), $stub);
        }


        File::put($controllerFileName, $stub);
        $this->info("Controller Scaffold successfully created at '[$controllerFileName]'");
    }



    public function generatePassableData($attributes)
    {

        $passable_ = '';
        $_data = '';
        $_data_imports = '';

        $exists = [];
        foreach ($attributes as $attribute)
        {

            if (!empty($attribute['relationship']))
            {
                if (in_array($attribute['relationship']['relationship'], $exists))
                    continue;

                $exists[] = $attribute['relationship']['relationship'];

                $modelName = Str::singular(Str::studly($attribute['relationship']['relationship']));

                $passable_ .= "," . "'{$attribute['relationship']['relationship']}'";

                if ($attribute['relationship']['relationship'] == 'users')
                {

                    $_data .= "$" . $attribute['relationship']['relationship'] . " = " . $modelName . "::whereDoesntHave('roles', function (\$query) {
                            \$query->where('name', '_Maintainer');
                        })->get();\n";
                }
                else
                    # Construct the code snippet for fetching all records
                    $_data .= "$" . $attribute['relationship']['relationship'] . " = " . $modelName . "::all();\n";

                # Generate the import statement for the model
                $_data_imports .= "use App\Models\\" . $modelName . ";\n ";
            }
        }

        return [
            'passable_' => $passable_,
            '_data' => $_data,
            '_data_imports' => $_data_imports
        ];
    }

    protected function generateAttributeStrings($attributes)
    {
        $attributes = $this->filterExclusion($attributes);
        $attributeStrings = [];

        foreach ($attributes as $attribute)
        {
            $attributeStrings[] = "'{$attribute['attribute']}'";
        }

        return implode(", ", $attributeStrings);
    }

    protected function generateValidationRules($attributes, $validations)
    {
        $attributes = $this->filterExclusion($attributes, ['is_visible']);


        $validationRules = [];

        foreach ($attributes as $attribute)
        {

            $val = $attribute['validations'];

            if ($val['nullable'])
                $condition = $val['nullable'] = 'nullable';
            else
                $condition = $val['nullable'] = 'required';

            if ($val['unique'])
                $condition .= '|unique:' . $this->getTableNameFromForeignKey($attribute['attribute']) . ',' . $attribute['attribute'];

            if (in_array($attribute['datatype'], ['integer', 'decimal']))
            {
                // $condition .= '|integer';
                $condition .= '|' . $attribute['datatype'];
            }

            $rule = "'" . $attribute['attribute'] . "' => '$condition'";
            $validationRules[] = $rule;
        }

        return "[" . implode(", ", $validationRules) . "]";
    }


    protected function viewName($class)
    {
        return str_replace('_', '-', $this->toSnake($class, true));
    }

    protected function filterExclusion($attributes, $exception = [], $additional = [])
    {
        $filtered = [];
        foreach ($attributes as $attribute)
        {
            $excluded = false;

            #Check if the attribute is in the global exclusions list
            if (in_array($attribute['attribute'], $this->EXCLUSIONS))
            {
                $excluded = true;
            }

            #Check if the attribute is in the exceptions list
            if (in_array($attribute['attribute'], $exception))
            {
                $excluded = false;
            }

            #Check if the attribute is in the additional exclusions list
            if (in_array($attribute['attribute'], $additional))
            {
                $excluded = true;
            }

            #Add the attribute to the filtered array if it's not excluded
            if (!$excluded)
            {
                $filtered[] = $attribute;
            }
        }

        return $filtered;
    }

    protected function generateViews($class)
    {


        $view_name = $this->viewName($class);

        $folder = resource_path('views/pages/backend/' . $view_name);

        # Create Module folder if it does not exist
        if (!is_dir($folder))
            mkdir($folder, 0755, true);


        if (!in_array($class, ['Role', 'Permissions']))
        {
            $migrationFileName = $this->migrationExists($class);
            if (!$migrationFileName)
            {
                $this->error("Migration file not found for model '[$class]'");
                return;
            }
            $attributes = $this->extractAttributesFromMigration($migrationFileName);
        }
        else
        {
            $this->warn("Running Special case: " . $class);
            $attributes = [];
        }


        #Index
        $this->createIndexView($view_name, $attributes, $class);
        #Create
        $this->createCreateEditView($view_name, $attributes, $class);
        #Show
        $this->createShowView($view_name, $attributes, $class);

        return true;
    }

    protected function createIndexView($view_name, $form_data, $class)
    {

        ##index
        $view = 'index';

        #Check if file exists
        $file = resource_path('views/pages/backend/' . $view_name . '/' . $view_name . '-' . $view . '.blade.php');

        if (file_exists($file))
        {
            $confirm = $this->confirm('[' . $view . '] View for [' . $class . '] Exists. Override?', true);
            if (!$confirm)
                return 0;
        }

        $stubPath = $this->getStubPath($view, $class, view: true);
        $stub = file_get_contents($stubPath);

        #Replacements
        $thead = '';
        $tbody = '';
        $form_data = $this->filterExclusion($form_data);

        foreach ($form_data as $attribute)
        {
            $value = $attribute['attribute'];
            $name = $this->formatName($value);

            ##Table
            #THead
            $thead .= "\n<th class='align-middle'>{$name}</th>";

            #TBody
            if (!empty($attribute['relationship']))
            {
                $rel = Str::singular($attribute['relationship']['relationship']);
                $tbody .= "\n<td class='align-middle'>{{\$data->{$rel}->name}}</td>";
            }
            else
                $tbody .= "\n<td class='align-middle'>{{\$data->$value}}</td>";
        }

        $stub = str_replace("{class}", $this->viewName($class), $stub);
        $stub = str_replace("{thead}", $thead, $stub);
        $stub = str_replace("{tbody}", $tbody, $stub);

        #Write the modified stub content to the new file
        $result = file_put_contents($file, $stub);
        if ($result !== false)
            $this->info("View [$view] created successfully.");
        else
            $this->info("Error [$view] Creation failed.");
    }

    protected function createShowView($view_name, $form_data, $class)
    {

        ##index
        $view = 'show';

        #Check if file exists
        $file = resource_path('views/pages/backend/' . $view_name . '/' . $view_name . '-' . $view . '.blade.php');

        if (file_exists($file))
        {
            $confirm = $this->confirm('[' . $view . '] View for [' . $class . '] Exists. Override?', true);
            if (!$confirm)
                return 0;
        }


        $stubPath = $this->getStubPath($view, $class, view: true);
        $stub = file_get_contents($stubPath);

        #Replacements
        ##----##
        $code = '';
        $form_data = $this->filterExclusion($form_data);

        foreach ($form_data as $attribute)
        {
            $value = $attribute['attribute'];
            $name = $this->formatName($attribute['attribute']);

            $code .= "\n<div class='col-md-3'>";
            $code .= "\n<div class='form-group'>";
            $code .= "\n<label class='fw-bold'>{$name}</label>";
            $code .= "\n<p>";

            if (!empty($attribute['relationship']))
            {
                $rel = Str::singular($attribute['relationship']['relationship']);
                $code .= "\n<span class=''>{{\$data->{$rel}->name}}</span>";
            }
            else
                $code .= "\n<span class=''>{{\$data->{$value}}}</span>";
            $code .= "\n</p>";
            $code .= "\n</div>";
            $code .= "\n</div>";
        }

        $stub = str_replace("{{showBind}}", $code, $stub);
        $stub = str_replace("{class}", $this->viewName($class), $stub);


        #Write the modified stub content to the new file
        $result = file_put_contents($file, $stub);

        if ($result !== false)
            $this->info("View [$view] created successfully.");
        else
            $this->info("Error [$view] creating View.");
    }
    protected function createCreateEditView($view_name, $form_data, $class)
    {
        ##index
        $view = 'create';

        #Check if file exists
        $file = resource_path('views/pages/backend/' . $view_name . '/' . $view_name . '-' . $view . '.blade.php');

        if (file_exists($file))
        {
            $confirm = $this->confirm('[' . $view . '] View for [' . $class . '] Exists. Override?', true);
            if (!$confirm)
                return 0;
        }

        $stubPath = $this->getStubPath($view, $class, view: true);
        $stub = file_get_contents($stubPath);


        #Replacements
        $code = '';
        $form_data = $this->filterExclusion($form_data);

        foreach ($form_data as $attribute)
        {
            $value = $attribute['attribute'];
            $name = $this->formatName($attribute['attribute']);


            $datatype = $this->getDataType($attribute['datatype']);

            #Default value
            if ($attribute['datatype'] == 'boolean')
                $val = 'true';
            else
                $val = 'null';

            # Relationship
            if (!empty($attribute['relationship']))
                $option = '$' . $attribute['relationship']['relationship'];
            else
                $option = '[]';

            $code .= "\n <x-scrud::dynamics.forms.input col='4' model='{$attribute['attribute']}' type='{$datatype}' label='{$name}'  :option='{$option}' value=\"{{ isset(\$data) ? \$data->{$value} : old('{$value}')  }}\"/>";
        }
        $stub = str_replace("{{formBind}}", $code, $stub);
        $stub = str_replace("{class}", $this->viewName($class), $stub);


        #Write the modified stub content to the new file
        $result = file_put_contents($file, $stub);

        if ($result !== false)
            $this->info("View [$view] created successfully.");
        else
            $this->info("Error [$view] creating View.");
    }
    protected function registerRoutes($class)
    {
        $stub = $this->getStubPath('route');

        $routePath = __DIR__ . "/../" . $this->routePath;
        if (!file_exists($routePath))
        {
            #copy from stub
            $this->warn("Route file not found. Initializing");
            $result = copy($stub, $routePath);
            if ($result)
                $this->info("Route file created successfully.");
            else
                return $this->error("Route file creation failed.");
        }
        # Read the contents of the web.php file
        $contents = file_get_contents($routePath);

        # Find the position of the marker
        $marker = '##--GENERATED ROUTES--##';
        $pos = strpos($contents, $marker);

        # Define the resource route
        $resourceRoute = "Route::resource('" . $this->viewName($class) . "', '" . $class . "Controller');";

        # Check if the marker is found
        if ($pos !== false)
        {
            # Check if the resource route already exists after the marker
            $routePos = strpos($contents, $resourceRoute, $pos);

            if ($routePos === false)
            {
                # Insert the resource route after the marker with a newline
                $newContents = substr_replace($contents, "\n" . $resourceRoute, $pos + strlen($marker), 0);
            }
            else
            {
                $this->warn("Resource route for '{$class}' already exists in web.php.");
                return 1;
            }
        }
        else
        {
            # Marker not found in the web.php file. Create marker and add routes at the end of the file.
            $this->info("Marker not found in the web.php file. Creating marker and adding routes at the end of the file...");
            $newContents = rtrim($contents); // Remove trailing whitespaces

            // Add marker and resource route at the end of the file
            $newContents .= "\n\n" . $marker . "\n" . $resourceRoute;
        }

        # Write the modified contents back to the web.php file
        file_put_contents($routePath, $newContents);

        $this->info("Resource route added successfully.");
        return 1;
    }


    public function generateSideMenus($class)
    {
        # Choose Icon Set
        $icon = "bx bx-home"; # Default icon set.
        try
        {
            $module = $this->registerModule($class);

            #Check dependencies;
            $this->checkDependencies();
            #Check database for the name
            $menu = new Menu;
            if (!is_null($menu->where('name', $this->displayName($class))->first()))
            {
                $this->warn('Side Menu already exists');
                return 1;
            }


            # Add menu to database
            $menu = $menu->create([
                'system_module_id' => $module->id,
                'name' => $this->displayName($class),
                'icon' => $icon,
                'route' => $this->viewName($class) . '.index',
                'description' => $this->viewName($class) . ' menu',
                // 'show_sub_menus' => $this->show_sub_menus,
            ]);

            $default_sub_menus = ['index', 'create'];
            foreach ($default_sub_menus as $sub_menu)
                #Create Sub Menus
                SubMenu::create([
                    'menu_id' => $menu->id,
                    'name' => $this->viewName($sub_menu),
                    'route' => $this->viewName($class) . '.' . $sub_menu,
                    'icon' => $icon,
                    'description' => $this->viewName($class) . ' menu',
                ]);

            $this->info("");
            $this->info("Side menus generated successfully.");
        }
        catch (\Exception $e)
        {
            $this->warn($e->getMessage());
            $this->error('Side Menu Generation encountered an issue');
            Log::error($e->getMessage());
        }
    }

    public function generatePermissions($class)
    {
        #Create Permissions for the Item
        $default_permissions = ['index', 'show', 'create', 'update', 'destroy'];

        $permissions = [];
        foreach ($default_permissions as $permission)
        {
            $permissions[] = Str::plural(strtolower($class)) . '.' . $permission;
        }

        $this->info("");
        $this->info("Creating Permissions...");
        foreach ($permissions as $p)
        {
            try
            {
                Permission::create(['name' => $p]);
            }
            catch (\Exception $e)
            {
                $this->warn($e->getMessage());
            }
        }
        $this->info("Permissions Created");

        #Assign the permissions
        $this->info("");
        $this->info('Assigning [' . $class . '] Permissions...');
        $this->assignPermissionsToDefaultRoles($permissions);
        $this->info('[' . $class . '] permissions assigned');
    }

    public function getDefaultRole()
    {
        #Create default  for the Item
        $role = new Role;

        $this->info("");

        # Create a new role with the name 'Admin_Default' if not existing
        $role = Role::firstOrCreate(['name' => '_Maintainer']);

        return $role;
    }

    /**
     * Assign Permissions to default Roles
     */
    public function assignPermissionsToDefaultRoles($permissions)
    {

        $role = $this->getDefaultRole();

        $role->givePermissionTo($permissions);

        return 1;
    }

    public function displayName($text)
    {
        # Add a space before capital letters (except the first letter)
        $text = preg_replace('/(?<!^)([A-Z])/', ' $1', $text);
        # Capitalize the first letter
        $text = ucfirst($text);

        return $text;
    }

    public function variableName($text)
    {
        # Add a space before capital letters (except the first letter)
        $text = preg_replace('/(?<!^)([A-Z])/', ' $1', $text);
        # Capitalize the first letter
        $text = strtolower($text);

        $text = str_replace(" ", '_', $text);

        return $text;
    }

    function getTableNameFromForeignKey($foreignKey)
    {
        // Remove common foreign key suffixes like "_id"
        $tableName = str_replace('_id', '', $foreignKey);

        // Pluralize the table name
        $pluralTableName = strtolower(Str::plural(Str::snake($tableName)));

        return $pluralTableName;
    }
    /**
     * Check for system dependencies
     */
    public function checkDependencies()
    {
        // Check for System Modules
        if (!\Schema::hasTable('system_modules'))
        {
            $this->warn("\nSystem Modules dependencies do not exist.");
            if (!$this->confirm("Generate System Module dependencies?", true))
                return 0;

            $this->call('sensy:setup', ['class' => 'SystemModule', "--m" => true]);
            $this->call('migrate');

            $this->generateController('SystemModule');
            $this->generateViews('SystemModule');
            $this->generateSideMenus('SystemModule');
            $this->registerRoutes('SystemModule');
        }

        // Menu dependency
        if (!\Schema::hasTable('menus'))
        {
            $this->warn("\nMenus dependencies do not exist.");
            $this->warn("----------------------------------------------");
            if (!$this->confirm("Generate Menu dependencies?", true))
                return 0;

            $this->call('sensy:setup', ['class' => 'Menu', "--m" => true]);
            $this->call('migrate');
        }

        // Submenu dependency
        if (!\Schema::hasTable('sub_menus'))
        {
            $this->warn("\nSubmenus dependencies do not exist.");
            $this->warn("----------------------------------------------");

            if (!$this->confirm("Generate Submenu dependencies?", true))
                return 0;


            $this->call('sensy:setup', ['class' => 'SubMenu', "--m" => true]);
            $this->call('migrate');
        }

        // Jetstream dependency
        if (!$this->isPackageInstalled('laravel/jetstream'))
        {
            $this->warn("\nJetstream Auth not installed!");
            $this->warn("----------------------------------------------");

            if (!$this->confirm("Perform Jetstream Auth dependency install?"))
                return 0;


            $this->installJetstream();
        }

        // Spatie Permission dependency
        if (!$this->isPackageInstalled('spatie/laravel-permission'))
        {
            $this->warn("\nSpatie not installed!");
            $this->warn("----------------------------------------------");

            if (!$this->confirm("Perform Spatie dependency install?"))
                return 0;


            $this->installSpatie();
        }

        // Check and generate missing view dependencies
        $this->generateViewDependencies();

        $this->info('All dependencies are present.');
        $this->info('');
    }

    private function installJetstream()
    {
        $command = 'composer require laravel/jetstream';
        $this->executeCommand($command);

        // Other installation steps
    }

    private function installSpatie()
    {
        $command = 'composer require spatie/laravel-permission';
        $this->executeCommand($command);

        // Other installation steps
    }

    private function generateViewDependencies()
    {
        $dependencies = [
            'Role', 'SystemModule', 'Menu', 'SubMenu', 'Permission'
        ];

        foreach ($dependencies as $class)
        {
            $directoryExists = File::exists(resource_path("views/pages/backend/{$this->viewName($class)}"));
            if (!$directoryExists)
            {
                $this->warn("{$class} View Dependency not found. Generating...");
                $this->generateController($class);
                $this->generateViews($class);
                $this->generateSideMenus($class);
                $this->registerRoutes($class);
            }
        }
    }


    public function generateRolesAndPermissionsView()
    {
    }

    public function isPackageInstalled($packageName)
    {
        $installedPackages = json_decode(File::get(base_path('vendor/composer/installed.json')), true);

        foreach ($installedPackages as $content)
        {

            if (!is_array($content))
                continue;

            if ($package = 'packages')
            {
                foreach ($content as $p)
                {
                    if (!is_array($p))
                        continue;
                    Log::info('Found: ' . $p['name']);
                    if ($p['name'] === $packageName)
                    {
                        return true;
                    }
                }
            }
        }

        return 0;
    }

    public function executeCommand($command)
    {
        # Open a process for the command
        $descriptors = [
            0 => ['pipe', 'r'], # stdin
            1 => ['pipe', 'w'], # stdout
            2 => ['pipe', 'w'], # stderr
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (is_resource($process))
        {
            # Read the output from the process line by line
            while ($line = fgets($pipes[1]))
            {
                echo $line; # Output the line
                flush(); # Flush the output buffer to display in real-time
            }

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            # Close the process
            $returnValue = proc_close($process);

            # Check if the return value indicates success
            return $returnValue === 0;
        }

        return 0;
    }

    /**
     * Register Module in the Database
     */
    protected function registerModule($class)
    {
        $system_module = new SystemModule;

        try
        {
            return $system_module->create(['name' => $class, 'is_active' => true]);
        }
        catch (\Exception $e)
        {
            $this->warn('MODULE NOT REGISTERED: ' . $e->getMessage());
            return 0;
        }
    }
}
