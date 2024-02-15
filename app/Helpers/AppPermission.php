<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class AppPermission
{
    private $adminRoleRoutes = [
        [
            'label' => 'Master Data',
            'key' => 'master',
            'roles' => [
                [
                    'label' => 'Kategori Produk',
                    'key' => 'product_category',
                    'requireIndex' => true,
                    'index' => 'main.master.product-category.index',
                    'routes' => [
                        'main.master.product-category.index' => 'Halaman',
                        'main.master.product-category.create' => 'Create',
                        'main.master.product-category.edit' => 'Edit',
                    ]
                ],
                [
                    'label' => 'Produk',
                    'key' => 'product',
                    'requireIndex' => true,
                    'index' => 'main.master.product.index',
                    'routes' => [
                        'main.master.product.index' => 'Halaman',
                        'main.master.product.create' => 'Create',
                        'main.master.product.edit' => 'Edit',
                    ],
                    'subRoutes' => [
                        // [
                        //     'label' => 'Produk',
                        //     'key' => 'product_list',
                        //     'requireIndex' => true,
                        //     'index' => 'main.master.product.index',
                        //     'routes' => [
                        //         'main.master.product.create' => 'Create',
                        //         'main.master.product.edit' => 'Edit',
                        //     ],
                        // ],
                        [
                            'label' => 'Diskon',
                            'key' => 'discount',
                            'requireIndex' => true,
                            'index' => 'main.master.product.discount.index',
                            'routes' => [
                                'main.master.product.discount.index' => 'Halaman',
                                'main.master.product.discount.create' => 'Tambah',
                                'main.master.product.discount.edit' => 'Edit',
                                'main.master.product.discount.remove' => 'Hapus',
                            ],
                        ],
                        [
                            'label' => 'Reward',
                            'key' => 'reward',
                            'requireIndex' => true,
                            'index' => 'main.master.product.reward.index',
                            'routes' => [
                                'main.master.product.reward.index' => 'Halaman',
                                'main.master.product.reward.create' => 'Tambah',
                                'main.master.product.reward.edit' => 'Edit',
                            ],
                        ],
                        [
                            'label' => 'Bonus',
                            'key' => 'bonus',
                            'requireIndex' => true,
                            'index' => 'main.master.product.bonus.index',
                            'routes' => [
                                'main.master.product.bonus.index' => 'Halaman',
                                'main.master.product.bonus.edit' => 'Edit',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        // [
        //     'label' => 'Kantor Cabang',
        //     'key' => 'branch',
        //     'roles' => [
        //         [
        //             'label' => 'Kantor Cabang',
        //             'key' => 'list',
        //             'requireIndex' => true,
        //             'index' => 'main.branch.list.index',
        //             'routes' => [
        //                 'main.branch.list.index' => 'Halaman',
        //                 'main.branch.list.create' => 'Create',
        //                 'main.branch.list.edit' => 'Edit',
        //             ]
        //         ],
        //         [
        //             'label' => 'Produk',
        //             'key' => 'product',
        //             'requireIndex' => true,
        //             'index' => 'main.branch.product.index',
        //             'routes' => [
        //                 'main.branch.product.index' => 'Halaman',
        //                 'main.branch.product.stock' => 'Update Persediaan Barang',
        //             ]
        //         ]
        //     ],
        // ],
        // [
        //     'label' => 'Anggota',
        //     'key' => 'member',
        //     'roles' => [
        //         [
        //             'label' => 'Anggota',
        //             'key' => 'list',
        //             'requireIndex' => true,
        //             'index' => 'main.member.index',
        //             'routes' => [
        //                 'main.member.index' => 'Halaman',
        //                 'main.member.create' => 'Create',
        //                 'main.member.edit' => 'Edit',
        //             ]
        //         ],
        //         [
        //             'label' => 'Struktur',
        //             'key' => 'structure',
        //             'requireIndex' => false,
        //             'index' => null,
        //             'routes' => [
        //                 'main.members.structure.basic' => 'Dasar',
        //                 'main.members.structure.table' => 'Table',
        //                 'main.members.structure.tree' => 'Diagram Pohon',
        //             ]
        //         ],
        //     ],
        // ],
        [
            'label' => 'Member',
            'key' => 'mitra',
            'roles' => [
                [
                    'label' => 'Daftar',
                    'key' => 'list',
                    'requireIndex' => true,
                    'index' => 'main.mitra.index',
                    'routes' => [
                        'main.mitra.index' => 'Halaman',
                        'main.mitra.edit' => 'Edit',
                    ]
                ],
                [
                    'label' => 'Member Baru',
                    'key' => 'register',
                    'requireIndex' => true,
                    'index' => 'main.mitra.register.index',
                    'routes' => [
                        'main.mitra.register.index' => 'Halaman',
                        'main.mitra.register.action' => 'Approve / Reject',
                    ]
                ],
            ],
        ],
        // [
        //     'label' => 'Pembayaran',
        //     'key' => 'payments',
        //     'roles' => [
        //         [
        //             'label' => 'Setoran Cabang',
        //             'key' => 'list',
        //             'requireIndex' => true,
        //             'index' => 'main.payments.index',
        //             'routes' => [
        //                 'main.payments.index' => 'Halaman',
        //                 'main.payments.action' => 'Approve / Reject',
        //             ]
        //         ],
        //     ],
        // ],
        // [
        //     'label' => 'Penjualan',
        //     'key' => 'sale',
        //     'roles' => [
        //         [
        //             'label' => 'Penjualan',
        //             'key' => 'list',
        //             'requireIndex' => true,
        //             'index' => 'main.sales.index',
        //             'routes' => [
        //                 'main.sales.index' => 'Halaman',
        //                 'main.sales.edit' => 'Edit',
        //                 'main.sales.delete' => 'Hapus',
        //                 'main.sales.report.globalProduct.index' => 'Laporan Global Produk',
        //             ]
        //         ],
        //     ],
        // ],
        [
            'label' => 'Transfer',
            'key' => 'transfer',
            'roles' => [
                // [
                //     'label' => 'Penjualan',
                //     'key' => 'sale',
                //     'requireIndex' => true,
                //     'index' => 'main.transfers.sales.index',
                //     'routes' => [
                //         'main.transfers.sales.index' => 'Halaman',
                //         'main.transfers.sales.action' => 'Approve / Reject',
                //     ]
                // ],
                [
                    'label' => 'Member',
                    'key' => 'mitra',
                    'requireIndex' => true,
                    'index' => 'main.transfers.mitra.index',
                    'routes' => [
                        'main.transfers.mitra.index' => 'Halaman',
                        'main.transfers.mitra.action' => 'Approve / Reject',
                    ]
                ],
            ],
        ],
        [
            'label' => 'Bonus',
            'key' => 'bonus',
            'roles' => [
                [
                    'label' => 'Halaman Bonus',
                    'key' => 'list',
                    'requireIndex' => false,
                    'index' => null,
                    'routes' => [
                        // 'main.bonus.royalty.index' => 'Royalty',
                        // 'main.bonus.override.index' => 'Override',
                        // 'main.bonus.team.index' => 'Team',
                        // 'main.bonus.sale.index' => 'Penjualan',
                        // 'main.bonus.summary.index' => 'Summary',
                        // 'main.bonus.point.self.index' => 'Poin Pribadi',
                        // 'main.bonus.point.upline.index' => 'Poin Upline',
                        'main.memberBonus.sponsor.index' => 'Sponsor',
                        // 'main.memberBonus.sponsor-ro.index' => 'Sponsor RO',
                        'main.memberBonus.cashback.index' => 'Cashback',
                        'main.memberBonus.point-ro.index' => 'Point RO',
                        // 'main.memberBonus.generasi.index' => 'Generasi',
                        'main.memberBonus.prestasi.index' => 'Prestasi',
                        'main.memberBonus.summary.index' => 'Summary',
                    ],
                ],
            ],
        ],
        [
            'label' => 'Withdraw',
            'key' => 'withdraw',
            'roles' => [
                [
                    'label' => 'Halaman Withdraw',
                    'key' => 'list',
                    'requireIndex' => false,
                    'index' => null,
                    'routes' => [
                        'main.withdraw.sponsor.index' => 'Bonus Sponsor',
                        // 'main.withdraw.sponsor-ro.index' => 'Bonus Sponsor RO',
                        'main.withdraw.cashback.index' => 'Bonus Cashback',
                        // 'main.withdraw.generasi.index' => 'Bonus Titik Generasi',
                        'main.withdraw.prestasi.index' => 'Bonus Prestasi',
                        'main.withdraw.point-ro.index' => 'Bonus Point RO',
                        'main.withdraw.histories.index' => 'Riwayat',
                        'main.withdraw.transfer.index' => 'Transfer Withdraw Bonus',
                    ]
                ],
            ],
        ],
        // [
        //     'label' => 'Laporan',
        //     'key' => 'reports',
        //     'roles' => [
        //         [
        //             'label' => 'Global',
        //             'key' => 'global',
        //             'requireIndex' => true,
        //             'index' => 'main.reports.global.index',
        //             'routes' => [
        //                 'main.reports.global.index' => 'Menu',
        //             ],
        //             'subRoutes' => [
        //                 [
        //                     'label' => 'Produk',
        //                     'key' => 'product',
        //                     'requireIndex' => true,
        //                     'index' => 'main.reports.global.product.index',
        //                     'routes' => [
        //                         'main.reports.global.product.index' => 'Halaman',
        //                     ],
        //                 ],
        //                 [
        //                     'label' => 'Manager',
        //                     'key' => 'manager',
        //                     'requireIndex' => true,
        //                     'index' => 'main.reports.global.manager.index',
        //                     'routes' => [
        //                         'main.reports.global.manager.index' => 'Halaman',
        //                     ],
        //                 ],
        //                 [
        //                     'label' => 'Detail Manager',
        //                     'key' => 'detail-manager',
        //                     'requireIndex' => true,
        //                     'index' => 'main.reports.global.detailManager.index',
        //                     'routes' => [
        //                         'main.reports.global.detailManager.index' => 'Halaman',
        //                     ],
        //                 ],
        //             ],
        //         ],
        //         [
        //             'label' => 'Bonus',
        //             'key' => 'bonus',
        //             'requireIndex' => true,
        //             'index' => 'main.reports.bonus.index',
        //             'routes' => [
        //                 'main.reports.bonus.index' => 'Menu',
        //             ],
        //             'subRoutes' => [
        //                 [
        //                     'label' => 'Distributor',
        //                     'key' => 'distributor',
        //                     'requireIndex' => true,
        //                     'index' => 'main.reports.bonus.distributor.index',
        //                     'routes' => [
        //                         'main.reports.bonus.distributor.index' => 'Halaman',
        //                     ],
        //                 ],
        //             ],
        //         ],
        //     ],
        // ],
        [
            'label' => 'Pengaturan',
            'key' => 'setting',
            'roles' => [
                [
                    'label' => 'Bank Perusahaan',
                    'key' => 'bank',
                    'requireIndex' => true,
                    'index' => 'main.settings.bank.index',
                    'routes' => [
                        'main.settings.bank.index' => 'Halaman',
                        'main.settings.bank.create' => 'Create',
                        'main.settings.bank.edit' => 'Edit',
                    ]
                ],
                [
                    'label' => 'Kuota Belanja',
                    'key' => 'quota',
                    'requireIndex' => true,
                    'index' => 'main.settings.quota.index',
                    'routes' => [
                        'main.settings.quota.index' => 'Halaman',
                        'main.settings.quota.edit' => 'Edit',
                    ]
                ],
                // [
                //     'label' => 'Reward',
                //     'key' => 'reward',
                //     'requireIndex' => true,
                //     'index' => 'main.settings.reward.index',
                //     'routes' => [
                //         'main.settings.reward.index' => 'Halaman',
                //         'main.settings.reward.create' => 'Create',
                //         'main.settings.reward.edit' => 'Edit',
                //     ]
                // ],
                // [
                //     'label' => 'Bonus',
                //     'key' => 'bonus',
                //     'requireIndex' => true,
                //     'index' => 'main.settings.bonus.index',
                //     'routes' => [
                //         'main.settings.bonus.index' => 'Menu',
                //     ],
                //     'subRoutes' => [
                //         [
                //             'label' => 'Royalty',
                //             'key' => 'royalty',
                //             'requireIndex' => true,
                //             'index' => 'main.settings.bonus.royalty.index',
                //             'routes' => [
                //                 'main.settings.bonus.royalty.index' => 'Halaman',
                //                 'main.settings.bonus.royalty.edit' => 'Edit',
                //             ],
                //         ],
                //         [
                //             'label' => 'Override',
                //             'key' => 'override',
                //             'requireIndex' => true,
                //             'index' => 'main.settings.bonus.override.index',
                //             'routes' => [
                //                 'main.settings.bonus.override.index' => 'Halaman',
                //                 'main.settings.bonus.override.edit' => 'Edit',
                //             ],
                //         ],
                //         [
                //             'label' => 'Team',
                //             'key' => 'team',
                //             'requireIndex' => true,
                //             'index' => 'main.settings.bonus.team.index',
                //             'routes' => [
                //                 'main.settings.bonus.team.index' => 'Halaman',
                //                 'main.settings.bonus.team.edit' => 'Edit',
                //             ],
                //         ],
                //         [
                //             'label' => 'Penjualan',
                //             'key' => 'sale',
                //             'requireIndex' => true,
                //             'index' => 'main.settings.bonus.sell.index',
                //             'routes' => [
                //                 'main.settings.bonus.sell.index' => 'Halaman',
                //                 'main.settings.bonus.sell.edit' => 'Edit',
                //             ],
                //         ],
                //         [
                //             'label' => 'Belanja Mitra',
                //             'key' => 'mitra-shoping',
                //             'requireIndex' => true,
                //             'index' => 'main.settings.bonus.mitraPremiumShopping.index',
                //             'routes' => [
                //                 'main.settings.bonus.mitraPremiumShopping.index' => 'Halaman',
                //                 'main.settings.bonus.mitraPremiumShopping.edit' => 'Edit',
                //             ],
                //         ],
                //     ],
                // ],
                // [
                //     'label' => 'Mitra Benefit',
                //     'key' => 'mitra',
                //     'requireIndex' => true,
                //     'index' => 'main.settings.mitra.index',
                //     'routes' => [
                //         'main.settings.mitra.index' => 'Menu',
                //     ],
                //     'subRoutes' => [
                //         [
                //             'label' => 'Diskon',
                //             'key' => 'discount',
                //             'requireIndex' => true,
                //             'index' => 'main.settings.mitra.purchase.discount.index',
                //             'routes' => [
                //                 'main.settings.mitra.purchase.discount.index' => 'Halaman',
                //                 'main.settings.mitra.purchase.discount.edit' => 'Edit',
                //             ],
                //         ],
                //         [
                //             'label' => 'Cashback',
                //             'key' => 'cashback',
                //             'requireIndex' => true,
                //             'index' => 'main.settings.mitra.purchase.cashback.index',
                //             'routes' => [
                //                 'main.settings.mitra.purchase.cashback.index' => 'Halaman',
                //                 'main.settings.mitra.purchase.cashback.edit' => 'Edit',
                //             ],
                //         ],
                //     ],
                // ],
            ],
        ],
    ];

    private $groupAccesses = [
        // member
        'member' => [
            // member (manager / user dalam struktur internal)
            'distributor' => [
                // team
                'member.team.index',
                // mitra team
                'member.directMitra.index',
                // product
                'member.product.index',
                'member.product.stock.index',
                // payment
                'member.payment.index',
                'member.payment.create',
                'member.payment.edit',
                'member.payment.delete',
                'member.payment.transfer',
                // sale
                'member.sale.index',
                'member.sale.create',
                'member.sale.edit',
                'member.sale.delete',
                // transfer
                'member.transfer.index',
                'member.transfer.create',
            ],
            'agen' => [
                // team
                'member.team.index',
                // mitra team
                'member.directMitra.index',
                // produk
                'member.product.index',
                // sale
                'member.sale.index',
                'member.sale.create',
                'member.sale.edit',
                'member.sale.delete',
                // transfer
                'member.transfer.index',
                'member.transfer.create',
            ],
            'member' => [],
            // mitra
            'mitra' => [
                'dropshipper' => [
                    // products
                    'mitra.myProducts.index',
                    // pembelian
                    'mitra.purchase.index',
                    'mitra.purchase.create',
                    // 'mitra.purchase.edit',
                    // 'mitra.purchase.delete',
                    // member
                    'mitra.myMember.index',
                    'mitra.point.my-shopping.index',
                    'mitra.point.activate-member.index',
                    'mitra.point.reward.index',
                    'mitra.bonus.sponsor.index',
                    'mitra.bank.index',
                    'mitra.bonus.sponsor.index',
                    'mitra.bonus.sponsor-ro.index',
                    'mitra.bonus.cashback.index',
                    'mitra.bonus.point-ro.index',
                    'mitra.bonus.generasi.index',
                    'mitra.bonus.prestasi.index',
                ],
                'agent' => [
                    // products
                    'mitra.myProducts.index',
                    // pembelian
                    'mitra.purchase.index',
                    'mitra.purchase.create',
                    // 'mitra.purchase.edit',
                    // 'mitra.purchase.delete',
                    'mitra.myMember.index',
                    'mitra.point.my-shopping.index',
                    'mitra.point.activate-member.index',
                    'mitra.point.reward.index',
                    'mitra.bonus.sponsor.index',
                    'mitra.bank.index',
                    'mitra.bonus.sponsor.index',
                    'mitra.bonus.sponsor-ro.index',
                    'mitra.bonus.cashback.index',
                    'mitra.bonus.point-ro.index',
                    'mitra.bonus.generasi.index',
                    'mitra.bonus.prestasi.index',
                ],
                'reseller' => [
                    // products
                    'mitra.myProducts.index',
                    // pembelian
                    'mitra.purchase.index',
                    'mitra.purchase.create',
                    // 'mitra.purchase.edit',
                    // 'mitra.purchase.delete',
                    'mitra.myMember.index',
                    'mitra.point.my-shopping.index',
                    'mitra.point.activate-member.index',
                    'mitra.point.reward.index',
                    'mitra.bank.index',
                    'mitra.bonus.sponsor.index',
                    'mitra.bonus.sponsor-ro.index',
                    'mitra.bonus.cashback.index',
                    'mitra.bonus.point-ro.index',
                    'mitra.bonus.generasi.index',
                    'mitra.bonus.prestasi.index',
                ],
            ],
            // member
            'customer' => [],
        ],
    ];
    private $gropedRoutes = [];

    private Collection $adminRolesCollection;
    private Collection $adminRolesTableCollection;

    public function __construct()
    {
        $this->loadAdminRolesFromDB();
        $this->buildAdminRoles();

        $routes = Route::getRoutes();
        $adminRules = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            $group = explode('.', $name)[0];

            $this->gropedRoutes[$group][] = $name;
            if ($group == 'main') {
                $adminRules[] = $name;
            }
        }

        $this->groupAccesses['main'] = $adminRules;
    }

    private function loadAdminRolesFromDB(): void
    {
        $this->adminRolesTableCollection = DB::table('roles')
            ->where('user_group', '=', USER_GROUP_MAIN)
            ->get();
    }

    private function buildAdminRoles(): void
    {
        $this->adminRolesCollection = collect();
        $id = 1;

        foreach ($this->adminRoleRoutes as $mainRoles) {
            $mainKey = $mainRoles['key'];
            foreach ($mainRoles['roles'] as $menuRole) {
                $menuKey = $mainKey . '_' . $menuRole['key'];
                foreach ($menuRole['routes'] as $route => $label) {
                    if (Route::has($route)) {
                        $requireIndex = ($menuRole['requireIndex'] === true);
                        $data = (object) [
                            'id' => $id++,
                            'key' => $menuKey,
                            'main_key' => $mainKey,
                            'parent_key' => null,
                            'main_label' => $mainRoles['label'],
                            'menu_label' => $menuRole['label'],
                            'input_label' => $label,
                            'level' => 1,
                            'required_index' => $requireIndex,
                            'index' => $requireIndex ? $menuRole['index'] : null,
                            'parent' => null,
                            'route' => $route,
                        ];

                        $this->adminRolesCollection->push($data);
                    }
                }

                if (array_key_exists('subRoutes', $menuRole) && !empty($menuRole['subRoutes'])) {
                    foreach ($menuRole['subRoutes'] as $subMenuRole) {
                        $subMenuKey = $menuKey . '_' . $subMenuRole['key'];
                        foreach ($subMenuRole['routes'] as $route => $label) {
                            if (Route::has($route)) {
                                $requireIndex = ($subMenuRole['requireIndex'] === true);
                                $data = (object) [
                                    'id' => $id++,
                                    'key' => $subMenuKey,
                                    'main_key' => $mainKey,
                                    'parent_key' => $menuKey,
                                    'main_label' => null,
                                    'menu_label' => $subMenuRole['label'],
                                    'input_label' => $label,
                                    'level' => 2,
                                    'required_index' => $requireIndex,
                                    'index' => $requireIndex ? $subMenuRole['index'] : null,
                                    'parent' => $menuRole['index'],
                                    'route' => $route,
                                ];

                                $this->adminRolesCollection->push($data);
                            }
                        }
                    }
                }
            }
        }
    }

    private function getAdminRolesDB(int $adminType, int $adminDivision): Collection
    {
        return $this->adminRolesTableCollection
            ->where('user_type', '=', $adminType)
            ->where('position_id', '=', $adminDivision);
    }

    private function adminHasRole(Collection $roles, string $route): bool
    {
        return !empty($roles->where('route', '=', $route)->first());
    }

    public function groupedAdminRoles(int $adminType = null, int $adminDivision = null): Collection
    {
        if (is_null($adminType)) $adminType = 0;
        if (is_null($adminDivision)) $adminDivision = 0;

        $adminDbRoles = $this->getAdminRolesDB($adminType, $adminDivision);

        $result = collect();
        $menuRoles = $this->adminRolesCollection->where('level', '=', 1);

        foreach ($menuRoles as $menu) {
            $mainKey = $menu->main_key;
            $mainExists = true;

            if (empty($main = $result->where('main_key', '=', $mainKey)->first())) {
                $mainExists = false;
                $main = (object) [
                    'main_key' => $menu->main_key,
                    'label' => $menu->main_label,
                    'menus' => collect()
                ];
            }

            $menuExists = true;

            if (empty($mainMenu = $main->menus->where('key', '=', $menu->key)->first())) {
                $menuExists = false;
                $mainMenu = (object) [
                    'key' => $menu->key,
                    'label' => $menu->menu_label,
                    'index' => $menu->index,
                    'index_id' => str_replace('.', '___', $menu->index),
                    'required_index' => $menu->required_index,
                    'roles' => collect()
                ];
            }

            $menuRole = (object) [
                'input_id' => str_replace('.', '___', $menu->route),
                'key' => $menu->key,
                'label' => $menu->input_label,
                'route' => $menu->route,
                'is_index' => ($menu->route == $menu->index),
                'has_role' => $this->adminHasRole($adminDbRoles, $menu->route),
                'sub_roles' => collect()
            ];

            $subMenus = $this->adminRolesCollection->where('level', '=', 2)
                ->where('parent_key', '=', $menu->key);

            foreach ($subMenus as $subMenu) {
                $subExists = true;
                if (empty($sub = $menuRole->sub_roles->where('key', '=', $subMenu->key)->first())) {
                    $subExists = false;
                    $sub = (object) [
                        'key' => $subMenu->key,
                        'label' => $subMenu->menu_label,
                        'index' => $subMenu->index,
                        'index_id' => str_replace('.', '___', $subMenu->index),
                        'required_index' => $subMenu->required_index,
                        'roles' => collect()
                    ];
                }

                $hasSubRole = ($menuRole->has_role && $this->adminHasRole($adminDbRoles, $subMenu->route));

                $subMenuRole = (object) [
                    'input_id' => str_replace('.', '___', $subMenu->route),
                    'key' => $subMenu->key,
                    'label' => $subMenu->input_label,
                    'route' => $subMenu->route,
                    'has_role' => $hasSubRole,
                    'is_index' => ($subMenu->route == $subMenu->index),
                ];

                $sub->roles->push($subMenuRole);
                if (!$subExists) $menuRole->sub_roles->push($sub);
            }

            $mainMenu->roles->push($menuRole);

            if (!$menuExists) $main->menus->push($mainMenu);
            if (!$mainExists) $result->push($main);
        }

        return $result;
    }

    public function updateAdminRoles(int $adminType, int $adminDivision, array $routes): void
    {
        DB::table('roles')->where('user_group', '=', USER_GROUP_MAIN)
            ->where('user_type', '=', $adminType)
            ->where('position_id', '=', $adminDivision)
            ->delete();

        if (!empty($routes)) {
            $values = [];
            $user = Auth::user();

            foreach ($routes as $route) {
                $values[] = [
                    'user_group' => USER_GROUP_MAIN,
                    'user_type' => $adminType,
                    'position_id' => $adminDivision,
                    'route' => $route,
                    'created_by' => $user->id,
                ];
            }

            DB::table('roles')->insert($values);
        }
    }

    public function hasGroupPermission($groupPrefix, User $user = null): bool
    {
        if (!Auth::check()) return false;
        if (empty($user)) $user = Auth::user();
        if (empty($groupPrefix)) return false;

        $permissionGroupName = $user->permission_group_name;

        return (strtolower($groupPrefix) == strtolower($permissionGroupName));
    }

    public function hasPermission($routeName, User $user = null): bool
    {
        if (!Auth::check() || empty($routeName)) return false;

        if (empty($user)) $user = Auth::user();

        if ($user->is_main_user) return $this->hasAdminPermission($routeName, $user);

        $permissionName = $user->permission_name;
        $permissionGroupName = $user->permission_group_name;

        $routes = is_array($routeName) ? $routeName : explode(',', $routeName);
        $has = false;
        $groupAccesses = Arr::get($this->groupAccesses, $permissionName, []);
        $groupRoutes = Arr::get($this->gropedRoutes, $permissionGroupName, []);

        foreach ($routes as $route) {
            $route = trim($route, ' ');

            if (Route::has($route) && in_array($route, $groupRoutes) && in_array($route, $groupAccesses)) {
                $has = true;
                break;
            }
        }

        return $has;
    }

    private function hasAdminPermission($routeName, User $admin): bool
    {
        if (empty($routeName)) return false;

        if (!is_array($routeName)) {
            $routeName = [$routeName];
        }

        $routes = $this->adminRolesCollection->whereIn('route', $routeName);

        if ($routes->isNotEmpty()) {
            if ($admin->user_type != USER_TYPE_SUPER) {
                $table = $this->adminRolesTableCollection->where('user_group', '=', USER_GROUP_MAIN)
                    ->where('user_type', '=', $admin->user_type)
                    ->where('position_id', '=', ($admin->division_id > 0) ? $admin->division_id : ADMIN_DIVISION_PUBLIC)
                    ->whereIn('route', $routeName);

                return $table->isNotEmpty();
            }
        }

        if ($admin->user_type != USER_TYPE_SUPER) return false;

        $has = false;

        foreach ($routeName as $route) {
            if (Route::has($route) && in_array($route, $this->groupAccesses['main'])) {
                $has = true;
                break;
            }
        }

        return $has;
    }
}
