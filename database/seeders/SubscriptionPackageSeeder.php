<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPackage;

class SubscriptionPackageSeeder extends Seeder
{
    public function run()
    {
        $packages = [
            [
                'package_name' => 'Silver',
                'package_code' => 'SILVER',
                'ads_quota' => 10,
                'price' => 99000,
                'description' => 'Perfect for individual agents',
                'features' => [
                    '10 Property Listings',
                    'Basic Search Ranking',
                    'Standard Support',
                    '5 Photos per Property'
                ],
                'is_active' => true,
                'display_order' => 1,
                'is_unlimited' => false,
                'is_customizable' => false,
                'can_delete' => false
            ],
            [
                'package_name' => 'Gold',
                'package_code' => 'GOLD',
                'ads_quota' => 50,
                'price' => 299000,
                'description' => 'Great for professional agents',
                'features' => [
                    '50 Property Listings',
                    'Priority Search Ranking',
                    'Priority Support',
                    '15 Photos per Property',
                    'Featured Badge'
                ],
                'is_active' => true,
                'display_order' => 2,
                'is_unlimited' => false,
                'is_customizable' => false,
                'can_delete' => false
            ],
            [
                'package_name' => 'Exclusive',
                'package_code' => 'EXCLUSIVE',
                'ads_quota' => null, // unlimited
                'price' => null, // custom pricing
                'description' => 'For agencies and enterprises',
                'features' => [
                    'Unlimited Property Listings',
                    'Top Search Priority',
                    'Dedicated Support',
                    'Unlimited Photos',
                    'Premium Badge',
                    'Analytics Dashboard',
                    'API Access'
                ],
                'is_active' => true,
                'display_order' => 3,
                'is_unlimited' => true,
                'is_customizable' => true,
                'can_delete' => false
            ]
        ];

        foreach ($packages as $package) {
            SubscriptionPackage::create($package);
        }
    }
}
