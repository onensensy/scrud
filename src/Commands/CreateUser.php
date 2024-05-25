<?php

namespace Sensy\Crud\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensy:create-user
                            {name : The name of the user}
                            {email : The email of the user}
                            {--password= : The password of the user}
                            {--role= : The role of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user with an optional role';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->option('password') ?? 'password'; #Default password if not provided
        $roleName = $this->option('role') ?? '_Maintainer';

        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password), #Encrypt the password
        ];

        try
        {

            #Create the user
            $user = User::create($userData);
        }
        catch (\Exception $e)
        {
            $this->error($e->getMessage());
        }
        try
        {
            #If a role is provided, find the role by name and associate it with the user
            if ($roleName)
            {
                $role = Role::where('name', $roleName)->first();

                if (!$role)
                {
                    $this->error("Role '{$roleName}' not found.");
                    return 1; #Return error status code
                }
                else
                    $user->assignRole($roleName);
            }
        }
        catch (\Exception)
        {
            $this->error("Something Went wrong with roles.");
        }

        $this->info('User created successfully.');
    }
}
