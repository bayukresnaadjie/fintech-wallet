<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BankAccount;
use App\Models\User;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $budi  = User::where('email','budi@fintech.test')->first();
        $siti  = User::where('email','siti@fintech.test')->first();
        $dika  = User::where('email','dika@fintech.test')->first();
        $admin = User::where('email','admin@fintech.test')->first();

        $rekening = [
            ['user_id'=>$budi->id,  'nama_bank'=>'BCA',     'kode_bank'=>'014', 'nomor_rekening'=>'1234567890', 'nama_pemilik'=>'Budi Santoso',    'is_verified'=>true,  'is_default'=>true],
            ['user_id'=>$budi->id,  'nama_bank'=>'Mandiri',  'kode_bank'=>'008', 'nomor_rekening'=>'0987654321', 'nama_pemilik'=>'Budi Santoso',    'is_verified'=>true,  'is_default'=>false],
            ['user_id'=>$siti->id,  'nama_bank'=>'BNI',      'kode_bank'=>'009', 'nomor_rekening'=>'1122334455', 'nama_pemilik'=>'Siti Rahayu',     'is_verified'=>true,  'is_default'=>true],
            ['user_id'=>$dika->id,  'nama_bank'=>'BRI',      'kode_bank'=>'002', 'nomor_rekening'=>'5544332211', 'nama_pemilik'=>'Dika Pratama',    'is_verified'=>false, 'is_default'=>true],
            ['user_id'=>$admin->id, 'nama_bank'=>'BSI',      'kode_bank'=>'451', 'nomor_rekening'=>'7788990011', 'nama_pemilik'=>'Admin Fintech',   'is_verified'=>true,  'is_default'=>true],
        ];

        foreach ($rekening as $r) {
            BankAccount::create($r);
        }

        $this->command->info('✅ Bank accounts seeded');
    }
}
