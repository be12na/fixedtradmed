<?php

namespace Database\Seeders;

use App\Models\MitraBonusLevel;
use App\Models\MitraReward;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\UserPackage;
use App\Repositories\RegionRepository;
use Carbon\Carbon;
// use App\Models\Zone;
// use App\Models\ZoneDelivery;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->seedDaerah();
        $this->createDefaultUsers();
        $this->seedProducts();
        $this->seedReward();
        $this->seedBonusLevel();
        // $this->seedDeliveryArea();
    }

    private function createDefaultUsers()
    {
        $email = str_replace(' ', '-', strtolower(config('app.name'))) . '@email.com';

        if (!User::byUsername('superadmin')->exists()) {
            User::create([
                'username' => 'superadmin',
                'name' => 'Super Administrator',
                'email' => 'super-' . $email,
                'password' => Hash::make('a'),
                'user_group' => USER_GROUP_MAIN,
                'user_type' => USER_TYPE_SUPER,
                'user_status' => USER_STATUS_ACTIVE,
                'is_login' => true,
            ]);
        }

        if (!User::byUsername('masteradmin')->exists()) {
            User::create([
                'username' => 'masteradmin',
                'name' => 'Master Administrator',
                'email' => 'master-' . $email,
                'password' => Hash::make('a'),
                'user_group' => USER_GROUP_MAIN,
                'user_type' => USER_TYPE_MASTER,
                'user_status' => USER_STATUS_ACTIVE,
                'is_login' => true,
            ]);
        }

        if (!User::byUsername('admin')->exists()) {
            User::create([
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin-' . $email,
                'password' => Hash::make('a'),
                'user_group' => USER_GROUP_MAIN,
                'user_type' => USER_TYPE_ADMIN,
                'user_status' => USER_STATUS_ACTIVE,
                'is_login' => true,
            ]);
        }

        if (!User::byUsername('tstdev')->exists()) {
            User::create([
                'username' => 'tstdev',
                'name' => 'TST',
                'email' => 'tst-' . $email,
                'password' => Hash::make('a'),
                'user_group' => USER_GROUP_MAIN,
                'user_type' => USER_TYPE_SUPER,
                'user_status' => USER_STATUS_ACTIVE,
                'is_login' => true,
            ]);
        }

        // $dateTime = date('Y-m-d H:i:s');

        // $top01 = User::byUsername('top01')->first();

        // if (empty($top01)) {
        //     $top01 = User::create([
        //         'username' => 'top01',
        //         'name' => 'Top 01',
        //         'email' => 'top01-' . $email,
        //         'password' => Hash::make('a'),
        //         'user_group' => USER_GROUP_MEMBER,
        //         'user_type' => USER_TYPE_MITRA,
        //         'user_status' => USER_STATUS_ACTIVE,
        //         'status_at' => $dateTime,
        //         'is_login' => true,
        //         'activated' => true,
        //         'activated_at' => $dateTime,
        //         'phone' => '081234567890',
        //         'mitra_type_reg' => MITRA_TYPE_DROPSHIPPER,
        //         'mitra_type' => MITRA_TYPE_DROPSHIPPER,
        //         'level_id' => MITRA_TYPE_DROPSHIPPER,
        //         'position_ext' => USER_EXT_MTR,
        //     ]);
        // }

        // if (empty($top01->userPackage)) {
        //     $code = UserPackage::makeCode(MITRA_TYPE_AGENT);

        //     $top01->userPackage()->create([
        //         'code' => $code,
        //         'package_id' => MITRA_TYPE_AGENT,
        //         'price' => 0,
        //         'digit' => 0,
        //         'total_price' => 0,
        //         'status' => MITRA_PKG_CONFIRMED,
        //     ]);
        // }
    }

    private function seedProducts()
    {
        $code = strtoupper(config('app.name'));

        if (empty($category = ProductCategory::query()->byCode($code)->first())) {
            $category = ProductCategory::create([
                'code' => $code,
                'name' => "{$code} Herbal",
                'merek' => $code,
                'is_active' => true,
            ]);
        }

        $categoryId = $category->id;
        $now = Carbon::now();

        $products = [
            [
                'code' => "{$code} ONE",
                'product_category_id' => $categoryId,
                'name' => 'Paket 12 botol @ 60ml',
                'satuan' => PRODUCT_UNIT_PCS,
                'isi' => 12,
                'satuan_isi' => 1,
                'harga_a' => 1350000,
                'is_active' => true,
                'active_at' => $now,
                'is_publish' => true,
                'self_point' => 1,
                'upline_point' => 1,
                'bonus_sponsor' => 135000,
                'bonus_sponsor_ro' => 35000,
                'bonus_cashback_ro' => 100000,
                'package_range' => 1,
            ],
            [
                'code' => "{$code} TWO",
                'product_category_id' => $categoryId,
                'name' => 'Paket 5 box, 60 botol @ 12ml',
                'satuan' => PRODUCT_UNIT_PCS,
                'isi' => 5,
                'satuan_isi' => 1,
                'harga_a' => 1500000,
                'is_active' => true,
                'active_at' => $now,
                'is_publish' => true,
                'self_point' => 1,
                'upline_point' => 1,
                'bonus_sponsor' => 150000,
                'bonus_sponsor_ro' => 50000,
                'bonus_cashback_ro' => 100000,
                'package_range' => 2,
            ],
            [
                'code' => "{$code} THREE",
                'product_category_id' => $categoryId,
                'name' => 'Paket 3 box @ 12ml + 6 botol @ 60ml',
                'satuan' => PRODUCT_UNIT_PCS,
                'isi' => 3,
                'satuan_isi' => 1,
                'harga_a' => 1575000,
                'is_active' => true,
                'active_at' => $now,
                'is_publish' => true,
                'self_point' => 1,
                'upline_point' => 1,
                'bonus_sponsor' => 157500,
                'bonus_sponsor_ro' => 57500,
                'bonus_cashback_ro' => 100000,
                'package_range' => 3,
            ],
        ];

        foreach ($products as $product) {
            if (empty(Product::query()->byCode($product['code'])->first())) {
                Product::create($product);
            }
        }
    }

    private function seedDaerah()
    {
        RegionRepository::dbSeed();
    }

    private function seedReward()
    {
        $rewardValues = [
            ['point' => 50, 'reward' => 'Emas Antam 1 gram'],
            ['point' => 500, 'reward' => 'Iphone senilai 10 Juta'],
            ['point' => 2500, 'reward' => 'Motor Yamaha NMAX + Trip LN 3 Negara'],
            ['point' => 4500, 'reward' => 'Paket Umroh 2 Orang'],
            ['point' => 9000, 'reward' => 'Honda Brio + BPKB 175 Juta'],
            ['point' => 15000, 'reward' => 'Xpander + BPKB Senilai 300 Juta'],
            ['point' => 35000, 'reward' => 'Pajero Sport Senilai 600 Juta'],
            ['point' => 65000, 'reward' => 'Toyota Alphard atau Rumah Mewah Senilai 1,2M'],
        ];

        foreach ($rewardValues as $values) {
            if (!MitraReward::byPoint($values['point'])->exists()) {
                MitraReward::create($values);
            }
        }
    }

    private function seedBonusLevel()
    {
        // $typeGenerasi = BONUS_MITRA_LEVEL_GENERASI;
        $typePrestasi = BONUS_MITRA_LEVEL_PRESTASI;
        // $generasiPrefixCode = Arr::get(BONUS_MITRA_LEVELS, "{$typeGenerasi}.code");
        // $generasiPrefixName = Arr::get(BONUS_MITRA_LEVELS, "{$typeGenerasi}.name");
        $prestasiPrefixCode = Arr::get(BONUS_MITRA_LEVELS, "{$typePrestasi}.code");
        $prestasiPrefixName = Arr::get(BONUS_MITRA_LEVELS, "{$typePrestasi}.name");

        $levels = [
            // prestasi
            [
                'type' => $typePrestasi,
                'level' => 1,
                'code' => "{$prestasiPrefixCode}1",
                'name' => "{$prestasiPrefixName} 1",
                'bonus' => 5,
            ],
            [
                'type' => $typePrestasi,
                'level' => 2,
                'code' => "{$prestasiPrefixCode}2",
                'name' => "{$prestasiPrefixName} 2",
                'bonus' => 3,
            ],
            [
                'type' => $typePrestasi,
                'level' => 3,
                'code' => "{$prestasiPrefixCode}3",
                'name' => "{$prestasiPrefixName} 3",
                'bonus' => 2,
            ],
        ];

        MitraBonusLevel::truncate();

        foreach ($levels as $level) {
            MitraBonusLevel::create($level);
        }
    }

    // private function seedDeliveryArea()
    // {
    //     $zones = [
    //         // barat
    //         [
    //             'zone' => Zone::where('name', '=', 'Barat')->first() ?: Zone::create(['name' => 'Barat']),
    //             'names' => ['Sumatera', 'Kalimantan', 'Jawa Barat', 'Jawa Tengah'],
    //         ],
    //         // tengah
    //         [
    //             'zone' => Zone::where('name', '=', 'Tengah')->first() ?: Zone::create(['name' => 'Tengah']),
    //             'names' => ['Jawa Timur', 'Sulawesi', 'Bali', 'Lombok', 'NTT'],
    //         ],
    //         // timur
    //         [
    //             'zone' => Zone::where('name', '=', 'Timur')->first() ?: Zone::create(['name' => 'Timur']),
    //             'names' => ['Maluku', 'Irian Jaya'],
    //         ],
    //     ];

    //     foreach ($zones as $zone) {
    //         foreach ($zone['names'] as $area) {
    //             if (empty(ZoneDelivery::where('name', '=', $area)->first())) {
    //                 ZoneDelivery::create([
    //                     'zone_id' => $zone['zone']->id,
    //                     'name' => $area
    //                 ]);
    //             }
    //         }
    //     }
    // }
}
