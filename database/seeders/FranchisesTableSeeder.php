<?php
namespace Database\Seeders;

use App\Models\Franchise;
use Illuminate\Database\Seeder;

class FranchisesTableSeeder extends Seeder
{
    public function run()
    {
        // Insert dummy franchises
        Franchise::create(['name' => 'LMS Franchise 1', 'description' => 'Main franchise', 'contact_email' => 'franchise1@example.com', 'contact_phone' => '1234567890']);
        Franchise::create(['name' => 'LMS Franchise 2', 'description' => 'Branch franchise', 'contact_email' => 'franchise2@example.com', 'contact_phone' => '0987654321']);
    }
}

?>