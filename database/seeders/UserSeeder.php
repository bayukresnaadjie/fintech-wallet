<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserKyc;
use App\Models\Wallet;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'       => 'Admin Fintech',
                'email'      => 'admin@fintech.test',
                'nomor_hp'   => '081200000000',
                'kyc_status' => 'verified',
                'saldo'      => 50000000,
                'profile'    => ['nik'=>'3201010101010001','kota'=>'Jakarta','provinsi'=>'DKI Jakarta','pekerjaan'=>'Administrator'],
            ],
            [
                'name'       => 'Budi Santoso',
                'email'      => 'budi@fintech.test',
                'nomor_hp'   => '081211111111',
                'kyc_status' => 'verified',
                'saldo'      => 2500000,
                'profile'    => ['nik'=>'3201010101010002','kota'=>'Bandung','provinsi'=>'Jawa Barat','pekerjaan'=>'Karyawan Swasta'],
            ],
            [
                'name'       => 'Siti Rahayu',
                'email'      => 'siti@fintech.test',
                'nomor_hp'   => '081222222222',
                'kyc_status' => 'verified',
                'saldo'      => 1750000,
                'profile'    => ['nik'=>'3201010101010003','kota'=>'Surabaya','provinsi'=>'Jawa Timur','pekerjaan'=>'Wirausaha'],
            ],
            [
                'name'       => 'Dika Pratama',
                'email'      => 'dika@fintech.test',
                'nomor_hp'   => '081233333333',
                'kyc_status' => 'verified',
                'saldo'      => 3200000,
                'profile'    => ['nik'=>'3201010101010004','kota'=>'Yogyakarta','provinsi'=>'DIY','pekerjaan'=>'Freelancer'],
            ],
            [
                'name'       => 'Rina Wulandari',
                'email'      => 'rina@fintech.test',
                'nomor_hp'   => '081244444444',
                'kyc_status' => 'pending',
                'saldo'      => 500000,
                'profile'    => ['kota'=>'Medan','provinsi'=>'Sumatera Utara','pekerjaan'=>'Mahasiswa'],
            ],
            [
                'name'       => 'Fajar Nugroho',
                'email'      => 'fajar@fintech.test',
                'nomor_hp'   => '081255555555',
                'kyc_status' => 'belum',
                'saldo'      => 150000,
                'profile'    => ['kota'=>'Semarang','provinsi'=>'Jawa Tengah'],
            ],
        ];

        foreach ($users as $data) {
            // Skip auto-create wallet dari boot()
            $user = new User();
            $user->name       = $data['name'];
            $user->email      = $data['email'];
            $user->nomor_hp   = $data['nomor_hp'];
            $user->password   = Hash::make('password');
            $user->pin        = Hash::make('123456');
            $user->pin_aktif  = true;
            $user->kyc_status = $data['kyc_status'];
            $user->is_aktif   = true;
            $user->email_verified_at = now();
            $user->save();

            // Update saldo wallet
            $user->wallet()->update(['saldo' => $data['saldo']]);

            // Update profil
            $user->profile()->update($data['profile']);

            // KYC untuk yang verified/pending
            if (in_array($data['kyc_status'], ['verified','pending'])) {
                UserKyc::create([
                    'user_id'          => $user->id,
                    'foto_ktp'         => 'kyc/ktp_dummy.jpg',
                    'foto_selfie'      => 'kyc/selfie_dummy.jpg',
                    'foto_selfie_ktp'  => 'kyc/selfie_ktp_dummy.jpg',
                    'status'           => $data['kyc_status'] === 'verified' ? 'verified' : 'pending',
                    'diverifikasi_oleh'=> $data['kyc_status'] === 'verified' ? 1 : null,
                    'diverifikasi_at'  => $data['kyc_status'] === 'verified' ? now() : null,
                ]);
            }
        }

        $this->command->info('✅ ' . count($users) . ' users created with wallets');
    }
}
