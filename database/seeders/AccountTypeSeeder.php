// In database/seeders/AccountTypeSeeder.php
use Illuminate\Database\Seeder;
use App\Models\Accounting\AccountType; // Import the model

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
        if (AccountType::count() == 0) { // Only run if table is empty
            $defaults = [ /* ... your defaults array ... */ ];
            foreach ($defaults as $type) {
                AccountType::create($type);
            }
        }
    }
}

// In database/seeders/DatabaseSeeder.php
public function run()
{
    $this->call([
        AccountTypeSeeder::class,
        // ... other seeders ...
    ]);
}