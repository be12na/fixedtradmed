<?php

if (!defined('DATE_V2')) define('DATE_V2', '2022-07-29'); // penjualan / pembelian dgn harga per-zona cabang

// Stock OpName, penjualan, pembelian
if (!defined('DAY_STOCKOPNAME_OPEN')) define('DAY_STOCKOPNAME_OPEN', 1); // senin
if (!defined('DAY_STOCKOPNAME_START')) define('DAY_STOCKOPNAME_START', 1); // senin
if (!defined('DAY_STOCKOPNAME_END')) define('DAY_STOCKOPNAME_END', 6); // sabtu

if (!defined('LOCK_PREV_STOCK_AFTER_INPUT')) define('LOCK_PREV_STOCK_AFTER_INPUT', true); // recommended true
if (!defined('OPEN_SALE_IF_STOCK_LOCKED')) define('OPEN_SALE_IF_STOCK_LOCKED', true);
if (!defined('OPEN_PURCHASE_IF_STOCK_LOCKED')) define('OPEN_PURCHASE_IF_STOCK_LOCKED', true);

if (!defined('DAY_SALE_STARTWEEK')) define('DAY_SALE_STARTWEEK', 1); // senin
if (!defined('DAY_SALE_ENDWEEK')) define('DAY_SALE_ENDWEEK', 6); // sabtu
if (!defined('DAYS_OPEN_SALE')) define('DAYS_OPEN_SALE', [1, 2, 3, 4, 5, 6]);
if (!defined('DAYS_SALE_RANGE')) define('DAYS_SALE_RANGE', [
    0 => [-2, 0],
    1 => [-4, 0],
    2 => [-3, 0],
    3 => [-3, 0],
    4 => [-3, 0],
    5 => [-3, 0],
    6 => [-3, 0],
    // end Stock OpName, penjualan, pembelian
]);

// Bank
if (!defined('OWNER_BANK_MAIN')) define('OWNER_BANK_MAIN', 1); // perusahaan
if (!defined('OWNER_BANK_MEMBER')) define('OWNER_BANK_MEMBER', 10); // member
if (!defined('BANK_000')) define('BANK_000', 'CASH');
if (!defined('BANK_BSI')) define('BANK_BSI', 'BSI');
if (!defined('BANK_BRI')) define('BANK_BRI', 'BRI');
if (!defined('BANK_BCA')) define('BANK_BCA', 'BCA');
if (!defined('BANK_BAS')) define('BANK_BAS', 'BAS');
if (!defined('BANK_MANDIRI')) define('BANK_MANDIRI', 'MANDIRI');
if (!defined('BANK_BNI')) define('BANK_BNI', 'BNI');
if (!defined('BANK_LIST')) define('BANK_LIST', [
    BANK_BSI => 'Bank Syariah Indonesia',
    BANK_MANDIRI => 'Bank Mandiri',
    BANK_BRI => 'Bank Rakyat Indonesia',
    BANK_BCA => 'Bank Central Asia',
    BANK_BAS => 'Bank Aceh Syariah',
    BANK_BNI => 'Bank Negara Indonesia',
]);
if (!defined('BANK_TRANSFER_LIST')) define('BANK_TRANSFER_LIST', [
    BANK_000 => 'Tunai',
    BANK_BSI => 'Bank Syariah Indonesia',
    BANK_MANDIRI => 'Bank Mandiri',
    BANK_BRI => 'Bank Rakyat Indonesia',
    BANK_BCA => 'Bank Central Asia',
    BANK_BAS => 'Bank Aceh Syariah',
    BANK_BNI => 'Bank Negara Indonesia',
]);
// end bank

// process status
if (!defined('PROCESS_STATUS_PENDING')) define('PROCESS_STATUS_PENDING', 0);
if (!defined('PROCESS_STATUS_APPROVED')) define('PROCESS_STATUS_APPROVED', 1);
if (!defined('PROCESS_STATUS_CANCEL_BY_ADMIN')) define('PROCESS_STATUS_CANCEL_BY_ADMIN', 2);
if (!defined('PROCESS_STATUS_CANCEL_BY_USER')) define('PROCESS_STATUS_CANCEL_BY_USER', 3);
if (!defined('PROCESS_STATUS_REJECTED')) define('PROCESS_STATUS_REJECTED', 5);
if (!defined('PROCESS_STATUS_LIST')) define('PROCESS_STATUS_LIST', [
    PROCESS_STATUS_PENDING => 'Pending',
    PROCESS_STATUS_APPROVED => 'Approved',
    PROCESS_STATUS_REJECTED => 'Reject',
]);

if (!defined('PAYMENT_STATUS_PENDING')) define('PAYMENT_STATUS_PENDING', 0);
if (!defined('PAYMENT_STATUS_APPROVED')) define('PAYMENT_STATUS_APPROVED', 1);
if (!defined('PAYMENT_STATUS_TRANSFERRED')) define('PAYMENT_STATUS_TRANSFERRED', 2);
if (!defined('PAYMENT_STATUS_REJECTED')) define('PAYMENT_STATUS_REJECTED', 5);
if (!defined('PAYMENT_STATUS_LIST')) define('PAYMENT_STATUS_LIST', [
    PAYMENT_STATUS_PENDING => 'Pending',
    PAYMENT_STATUS_TRANSFERRED => 'Transfer',
    PAYMENT_STATUS_APPROVED => 'Approved',
    PAYMENT_STATUS_REJECTED => 'Reject',
]);
// end process status

// user
if (!defined('TOP_MEMBER_ID')) define('TOP_MEMBER_ID', 5);
// user group
if (!defined('USER_GROUP_MAIN')) define('USER_GROUP_MAIN', 1);
if (!defined('USER_GROUP_MEMBER')) define('USER_GROUP_MEMBER', 10);
// end user group

// user type
// khusus group main
if (!defined('USER_TYPE_SUPER')) define('USER_TYPE_SUPER', 1);
if (!defined('USER_TYPE_MASTER')) define('USER_TYPE_MASTER', 2);
if (!defined('USER_TYPE_ADMIN')) define('USER_TYPE_ADMIN', 3);
if (!defined('USER_TYPE_FOUNDER')) define('USER_TYPE_FOUNDER', 4);
// khusus group member
if (!defined('USER_TYPE_MEMBER')) define('USER_TYPE_MEMBER', 10);
if (!defined('USER_TYPE_MITRA')) define('USER_TYPE_MITRA', 30);
if (!defined('USER_TYPE_CUSTOMER')) define('USER_TYPE_CUSTOMER', 50);
// end user type

// user group type (digunakan untuk rule)
if (!defined('USER_GROUP_TYPES')) define('USER_GROUP_TYPES', [
    USER_GROUP_MAIN => [
        USER_TYPE_SUPER => 'main.super',
        USER_TYPE_MASTER => 'main.master',
        USER_TYPE_ADMIN => 'main.admin',
        USER_TYPE_FOUNDER => 'main.founder',
    ],
    USER_GROUP_MEMBER => [
        USER_TYPE_MEMBER => 'member.member',
        USER_TYPE_MITRA => 'member.mitra',
        USER_TYPE_CUSTOMER => 'member.customer'
    ]
]);

// khusus level admin
if (!defined('ADMIN_DIVISION_PUBLIC')) define('ADMIN_DIVISION_PUBLIC', 1);
if (!defined('ADMIN_DIVISION_FINANCIAL')) define('ADMIN_DIVISION_FINANCIAL', 2);
if (!defined('ADMIN_DIVISION_INVENTORY')) define('ADMIN_DIVISION_INVENTORY', 3);
// if (!defined('ADMIN_DIVISION_ACCOUNTING')) define('ADMIN_DIVISION_ACCOUNTING', 4);
if (!defined('ADMIN_DIVISION_LIST')) define('ADMIN_DIVISION_LIST', [
    ADMIN_DIVISION_PUBLIC => 'Umum',
    ADMIN_DIVISION_FINANCIAL => 'Keuangan',
    ADMIN_DIVISION_INVENTORY => 'Produk dan Persediaan',
    // ADMIN_DIVISION_ACCOUNTING => 'Akuntansi',
]);
// end user group type

// user status
if (!defined('USER_STATUS_INACTIVE')) define('USER_STATUS_INACTIVE', 0);
if (!defined('USER_STATUS_ACTIVE')) define('USER_STATUS_ACTIVE', 1);
if (!defined('USER_STATUS_NEED_ACTIVATE')) define('USER_STATUS_NEED_ACTIVATE', 2);
if (!defined('USER_STATUS_BANNED')) define('USER_STATUS_BANNED', 5);
if (!defined('USER_STATUS_MITRA_REJECTED')) define('USER_STATUS_MITRA_REJECTED', 400); // sama dengan error code validation
// end user status

// jabatan
// structure internal position
if (!defined('USER_INT_NONE')) define('USER_INT_NONE', 0);
if (!defined('USER_INT_GM')) define('USER_INT_GM', 1);
if (!defined('USER_INT_DGM')) define('USER_INT_DGM', 2);
if (!defined('USER_INT_EM')) define('USER_INT_EM', 3);
if (!defined('USER_INT_SM')) define('USER_INT_SM', 4);
if (!defined('USER_INT_MGR')) define('USER_INT_MGR', 5);
if (!defined('USER_INT_AM')) define('USER_INT_AM', 6);
if (!defined('USER_INT_ETL')) define('USER_INT_ETL', 7);
if (!defined('USER_INT_LD')) define('USER_INT_LD', 8);
if (!defined('USER_INT_TR')) define('USER_INT_TR', 9);
if (!defined('USER_INT_MCD')) define('USER_INT_MCD', 10);

if (!defined('USER_INT_POSITIONS')) define('USER_INT_POSITIONS', [
    USER_INT_NONE => ['Team Support', 'SUPPORT'],
    USER_INT_GM => ['General Manager', 'GM'],
    USER_INT_DGM => ['Deputy General Manager', 'DGM'],
    USER_INT_EM => ['Executive Manager', 'EM'],
    USER_INT_SM => ['Senior Manager', 'SM'],
    USER_INT_MGR => ['Manager', 'MGR'],
    USER_INT_AM => ['Assistant Manager', 'AM'],
    USER_INT_ETL => ['Executive Top Leader', 'EL'],
    USER_INT_LD => ['Leader', 'LD'],
    USER_INT_TR => ['Trainer', 'TR'],
    USER_INT_MCD => ['Merchandiser', 'MD'],
]);

if (!defined('USER_INT_STRUCTURES')) define('USER_INT_STRUCTURES', [
    USER_INT_GM => [USER_INT_DGM],
    USER_INT_DGM => [USER_INT_EM],
    USER_INT_EM => [USER_INT_SM],
    USER_INT_SM => [USER_INT_MGR],
    USER_INT_MGR => [USER_INT_AM],
    USER_INT_AM => [USER_INT_ETL],
    USER_INT_ETL => [
        USER_INT_LD,
        USER_INT_TR,
        USER_INT_MCD,
    ],
    USER_INT_LD => [],
    USER_INT_TR => [],
    USER_INT_MCD => [],
]);

if (!defined('USER_INT_UPLINES')) define('USER_INT_UPLINES', [
    USER_INT_LD => USER_INT_ETL,
    USER_INT_TR => USER_INT_ETL,
    USER_INT_MCD => USER_INT_ETL,
    USER_INT_ETL => USER_INT_AM,
    USER_INT_AM => USER_INT_MGR,
    USER_INT_MGR => USER_INT_SM,
    USER_INT_SM => USER_INT_EM,
    USER_INT_EM => USER_INT_DGM,
    USER_INT_DGM => USER_INT_GM,
    USER_INT_GM => null,
]);
// end structure internal position

// structure external position
if (!defined('USER_EXT_NONE')) define('USER_EXT_NONE', 0);
if (!defined('USER_EXT_GM')) define('USER_EXT_GM', 1);
if (!defined('USER_EXT_DGM')) define('USER_EXT_DGM', 2);
if (!defined('USER_EXT_EM')) define('USER_EXT_EM', 3);
if (!defined('USER_EXT_SM')) define('USER_EXT_SM', 4);
if (!defined('USER_EXT_DIST')) define('USER_EXT_DIST', 5);
if (!defined('USER_EXT_AG')) define('USER_EXT_AG', 6);
if (!defined('USER_EXT_MTR')) define('USER_EXT_MTR', 7);

if (!defined('USER_EXT_POSITIONS')) define('USER_EXT_POSITIONS', [
    USER_EXT_NONE => ['Team Support', 'SUPPORT'],
    USER_EXT_GM => ['General Manager', 'GM'],
    USER_EXT_DGM => ['Deputy General Manager', 'DGM'],
    USER_EXT_EM => ['Executive Manager', 'EM'],
    USER_EXT_SM => ['Senior Manager', 'SM'],
    USER_EXT_DIST => ['Distributor', 'DIST'],
    USER_EXT_AG => ['Agent', 'AG'],
    USER_EXT_MTR => ['Mitra Usaha', null],
]);

if (!defined('USER_EXT_STRUCTURES')) define('USER_EXT_STRUCTURES', [
    USER_EXT_GM => [USER_EXT_DGM],
    USER_EXT_DGM => [USER_EXT_EM],
    USER_EXT_EM => [USER_EXT_SM],
    USER_EXT_SM => [USER_EXT_DIST],
    USER_EXT_DIST => [USER_EXT_AG],
    USER_EXT_AG => [USER_EXT_MTR],
    USER_EXT_MTR => [],
]);

if (!defined('USER_EXT_UPLINES')) define('USER_EXT_UPLINES', [
    USER_EXT_MTR => USER_EXT_AG,
    USER_EXT_AG => USER_EXT_DIST,
    USER_EXT_DIST => USER_EXT_SM,
    USER_EXT_SM => USER_EXT_EM,
    USER_EXT_EM => USER_EXT_DGM,
    USER_EXT_DGM => USER_EXT_GM,
    USER_EXT_GM => null,
]);
// end structure external position
// end jabatan

// manger cabang
if (!defined('USER_BRANCH_MANAGER')) define('USER_BRANCH_MANAGER', USER_INT_MGR);
// end manger cabang

// user type manager cabang
if (!defined('USER_BRANCH_MANAGER_NONE')) define('USER_BRANCH_MANAGER_NONE', 0);
if (!defined('USER_BRANCH_MANAGER_QUARTERBACK')) define('USER_BRANCH_MANAGER_QUARTERBACK', 1);
if (!defined('USER_BRANCH_MANAGER_TENANT')) define('USER_BRANCH_MANAGER_TENANT', 2);
if (!defined('USER_BRANCH_MANAGER_TYPES')) define('USER_BRANCH_MANAGER_TYPES', [
    USER_BRANCH_MANAGER_NONE => null,
    USER_BRANCH_MANAGER_QUARTERBACK => 'Quarterback',
    USER_BRANCH_MANAGER_TENANT => 'Tenant',
]);
if (!defined('USER_BRANCH_MANAGER_CODES')) define('USER_BRANCH_MANAGER_CODES', [
    USER_BRANCH_MANAGER_NONE => null,
    USER_BRANCH_MANAGER_QUARTERBACK => 'QB',
    USER_BRANCH_MANAGER_TENANT => 'TN',
]);
// end user type manager cabang

// user level
if (!defined('USER_LEVEL_NONE')) define('USER_LEVEL_NONE', 0);
if (!defined('USER_LEVEL_CLASSIC')) define('USER_LEVEL_CLASSIC', 1);
if (!defined('USER_LEVEL_SILVER')) define('USER_LEVEL_SILVER', 2);
if (!defined('USER_LEVEL_GOLD')) define('USER_LEVEL_GOLD', 3);
if (!defined('USER_LEVEL_PLATINUM')) define('USER_LEVEL_PLATINUM', 4);

if (!defined('USER_LEVELS')) define('USER_LEVELS', [
    USER_LEVEL_NONE => null,
    USER_LEVEL_CLASSIC => 'Classic',
    USER_LEVEL_SILVER => 'Silver',
    USER_LEVEL_GOLD => 'Gold',
    USER_LEVEL_PLATINUM => 'Platinum',
]);
// end user level

// mitra type
if (!defined('MITRA_TYPE_AGENT')) define('MITRA_TYPE_AGENT', 1);
if (!defined('MITRA_TYPE_RESELLER')) define('MITRA_TYPE_RESELLER', 2);
if (!defined('MITRA_TYPE_DROPSHIPPER')) define('MITRA_TYPE_DROPSHIPPER', 3);
if (!defined('MITRA_TYPES')) define('MITRA_TYPES', [
    MITRA_TYPE_DROPSHIPPER => 'Paket Dropshipper - Rp 0',
    MITRA_TYPE_RESELLER => 'Paket 60ml - 12btl - Rp 1.350.000',
    MITRA_TYPE_AGENT => 'Paket 12ml - 5box @12btl - Rp 1.500.000',

]);
if (!defined('MITRA_NAMES')) define('MITRA_NAMES', [
    MITRA_TYPE_DROPSHIPPER => 'Dropshipper',
    MITRA_TYPE_RESELLER => 'Paket 60ml',
    MITRA_TYPE_AGENT => 'Paket 12ml',

]);
if (!defined('MITRA_PRICES')) define('MITRA_PRICES', [
    MITRA_TYPE_DROPSHIPPER => 0,
    MITRA_TYPE_RESELLER => 1350000,
    MITRA_TYPE_AGENT => 1500000,


]);
if (!defined('MITRA_POINTS')) define('MITRA_POINTS', [
    MITRA_TYPE_DROPSHIPPER => 1,
    MITRA_TYPE_RESELLER => 1,
    MITRA_TYPE_AGENT => 1,

]);
if (!defined('MITRA_SPONSORINGS')) define('MITRA_SPONSORINGS', [
    MITRA_TYPE_DROPSHIPPER => [
        'value' => 0,
        'should_upgrade' => true,
    ],
    MITRA_TYPE_RESELLER => [
        'value' => 135000,
        'should_upgrade' => false,
    ],
    MITRA_TYPE_AGENT => [
        'value' => 150000,
        'should_upgrade' => false,
    ],

]);
if (!defined('MITRA_REPEAT_ORDERS')) define('MITRA_REPEAT_ORDERS', [
    MITRA_TYPE_DROPSHIPPER => 0,
    MITRA_TYPE_RESELLER => 35000,
    MITRA_TYPE_AGENT => 50000,
]);

if (!defined('MITRA_RULES')) define('MITRA_RULES', [
    MITRA_TYPE_DROPSHIPPER => 'dropshipper',
    MITRA_TYPE_RESELLER => 'reseller',
    MITRA_TYPE_AGENT => 'agent',

]);
// end mitra type
// mitra level
if (!defined('MITRA_LEVEL_AGENT')) define('MITRA_LEVEL_AGENT', 1);
if (!defined('MITRA_LEVEL_RESELLER')) define('MITRA_LEVEL_RESELLER', 2);
if (!defined('MITRA_LEVEL_DROPSHIPPER')) define('MITRA_LEVEL_DROPSHIPPER', 3);
if (!defined('MITRA_LEVELS')) define('MITRA_LEVELS', [
    MITRA_LEVEL_AGENT => 'Agen',
    MITRA_LEVEL_RESELLER => 'Reseller',
    MITRA_LEVEL_DROPSHIPPER => 'Dropshipper',
]);
if (!defined('MITRA_LEVEL_MAPS')) define('MITRA_LEVEL_MAPS', [
    MITRA_TYPE_AGENT => MITRA_LEVEL_AGENT,
    MITRA_TYPE_RESELLER => MITRA_LEVEL_RESELLER,
    MITRA_TYPE_DROPSHIPPER => MITRA_LEVEL_DROPSHIPPER,
]);
// end mitra level
// mitra status package
if (!defined('MITRA_PKG_PENDING')) define('MITRA_PKG_PENDING', 0);
if (!defined('MITRA_PKG_TRANSFERRED')) define('MITRA_PKG_TRANSFERRED', 1);
if (!defined('MITRA_PKG_CONFIRMED')) define('MITRA_PKG_CONFIRMED', 2);
if (!defined('MITRA_PKG_REJECTED')) define('MITRA_PKG_REJECTED', 3);
if (!defined('MITRA_PKG_CANCELED')) define('MITRA_PKG_CANCELED', 5);
// end mitra status package
// end user

// transaction package
if (!defined('TRANS_PKG_ACTIVATE')) define('TRANS_PKG_ACTIVATE', 1);
if (!defined('TRANS_PKG_REPEAT_ORDER')) define('TRANS_PKG_REPEAT_ORDER', 2);
if (!defined('TRANS_PKG_UPGRADE')) define('TRANS_PKG_UPGRADE', 3);

if (!defined('TRANS_PKG_TYPES')) define('TRANS_PKG_TYPES', [
    TRANS_PKG_ACTIVATE => 'Aktivasi',
    TRANS_PKG_REPEAT_ORDER => 'Repeat Order',
    TRANS_PKG_UPGRADE => 'Upgrade',
]);
// end transaction package

// mitra discount
if (!defined('MITRA_DISCOUNT_CATEGORY_NONE')) define('MITRA_DISCOUNT_CATEGORY_NONE', 0);
if (!defined('MITRA_DISCOUNT_CATEGORY_REGULAR')) define('MITRA_DISCOUNT_CATEGORY_REGULAR', 1);
if (!defined('MITRA_DISCOUNT_CATEGORY_SILVER')) define('MITRA_DISCOUNT_CATEGORY_SILVER', 2);
if (!defined('MITRA_DISCOUNT_CATEGORY_GOLD')) define('MITRA_DISCOUNT_CATEGORY_GOLD', 3);
if (!defined('MITRA_DISCOUNT_CATEGORY_PRO')) define('MITRA_DISCOUNT_CATEGORY_PRO', 4);

if (!defined('MITRA_DISCOUNT_CATEGORIES')) define('MITRA_DISCOUNT_CATEGORIES', [
    MITRA_DISCOUNT_CATEGORY_NONE => null,
    MITRA_DISCOUNT_CATEGORY_REGULAR => 'Regular',
    MITRA_DISCOUNT_CATEGORY_SILVER => 'Silver',
    MITRA_DISCOUNT_CATEGORY_GOLD => 'Gold',
    MITRA_DISCOUNT_CATEGORY_PRO => 'Pro',
]);
// end mitra discount

// mitra discount
if (!defined('MITRA_REWARD_CATEGORY_EVERY_SHOP')) define('MITRA_REWARD_CATEGORY_EVERY_SHOP', 10);
if (!defined('MITRA_REWARD_CATEGORY_TOTAL_YEARLY')) define('MITRA_REWARD_CATEGORY_TOTAL_YEARLY', 50);

if (!defined('MITRA_REWARD_CATEGORIES')) define('MITRA_REWARD_CATEGORIES', [
    MITRA_REWARD_CATEGORY_EVERY_SHOP => 'Setiap Pembelian',
    MITRA_REWARD_CATEGORY_TOTAL_YEARLY => 'Akumulasi 1 Tahun',
]);
// end mitra discount

// product
// satuan produk
if (!defined('PRODUCT_UNIT_PCS')) define('PRODUCT_UNIT_PCS', 1);
if (!defined('PRODUCT_UNIT_BOX')) define('PRODUCT_UNIT_BOX', 2);
if (!defined('PRODUCT_UNITS')) define('PRODUCT_UNITS', [
    PRODUCT_UNIT_PCS => 'pcs',
    PRODUCT_UNIT_BOX => 'box',
]);
// end satuan produk

// product type order
if (!defined('PRODUCT_DROPSHIPPER')) define('PRODUCT_DROPSHIPPER', 0);
if (!defined('PRODUCT_RESELLER')) define('PRODUCT_RESELLER', 1);
if (!defined('PRODUCT_DISTRIBUTOR')) define('PRODUCT_DISTRIBUTOR', 2);

if (!defined('PRODUCT_PURCHASES')) define('PRODUCT_PURCHASES', [
    PRODUCT_DROPSHIPPER => 'Dropshipper',
    PRODUCT_RESELLER => 'Reseller',
    PRODUCT_DISTRIBUTOR => 'Distributor',
]);
// product type order:end
// end product

// branch
// zones
if (!defined('BRANCH_CENTRAL')) define('BRANCH_CENTRAL', 1);
if (!defined('ZONE_WEST')) define('ZONE_WEST', 1);
if (!defined('ZONE_EAST')) define('ZONE_EAST', 2);

if (!defined('BRANCH_ZONES')) define('BRANCH_ZONES', [
    ZONE_WEST => 'Barat',
    ZONE_EAST => 'Timur',
]);
// end zones

if (!defined('STOCK_FLAG_MANAGER')) define('STOCK_FLAG_MANAGER', 0);
if (!defined('STOCK_FLAG_ADMIN')) define('STOCK_FLAG_ADMIN', 1);
if (!defined('STOCK_FLAG_EDIT')) define('STOCK_FLAG_EDIT', 2);
if (!defined('STOCK_FLAG_PLUS')) define('STOCK_FLAG_PLUS', 3);
if (!defined('STOCK_FLAG_MINUS')) define('STOCK_FLAG_MINUS', 4);

if (!defined('STOCK_FLAG_INFOS')) define('STOCK_FLAG_INFOS', [
    STOCK_FLAG_MANAGER => 'Dibuat oleh manager',
    STOCK_FLAG_ADMIN => 'Dibuat oleh admin',
    STOCK_FLAG_EDIT => 'Diubah oleh admin',
    STOCK_FLAG_PLUS => 'Penambahan jumlah oleh admin',
    STOCK_FLAG_MINUS => 'Pengurangan jumlah oleh admin',
]);

// date
// month
if (!defined('DATE_ID')) define('DATE_ID', [
    'januari' => 'january',
    'februari' => 'february',
    'maret' => 'march',
    'april' => 'april',
    'mei' => 'may',
    'juni' => 'june',
    'juli' => 'july',
    'agustus' => 'august',
    'september' => 'september',
    'oktober' => 'october',
    'november' => 'november',
    'desember' => 'december',
]);
// end month
// end date

// sale
if (!defined('SALE_FOUNDATION_PERSEN')) define('SALE_FOUNDATION_PERSEN', 2.5);
// end sale

// referral link
// share
if (!defined('SHARE_REFLINK_COPY')) define('SHARE_REFLINK_COPY', true);
if (!defined('SHARE_REFLINK_WHATSAPP')) define('SHARE_REFLINK_WHATSAPP', true);
if (!defined('SHARE_REFLINK_FACEBOOK')) define('SHARE_REFLINK_FACEBOOK', true);
// end referral link

// bonus
if (!defined('BONUS_TYPE_ROYALTY')) define('BONUS_TYPE_ROYALTY', 10);
if (!defined('BONUS_TYPE_OVERRIDE')) define('BONUS_TYPE_OVERRIDE', 20);
if (!defined('BONUS_TYPE_TEAM')) define('BONUS_TYPE_TEAM', 30);
if (!defined('BONUS_TYPE_SALE')) define('BONUS_TYPE_SALE', 40);
if (!defined('BONUS_TYPE_MGR_DIRECT_MITRA')) define('BONUS_TYPE_MGR_DIRECT_MITRA', 50);
if (!defined('BONUS_TYPE_MGR_DISTRIBUTOR')) define('BONUS_TYPE_MGR_DISTRIBUTOR', 60);
if (!defined('BONUS_TYPE_MITRA_DIRECT_MITRA')) define('BONUS_TYPE_MITRA_DIRECT_MITRA', 70);

if (!defined('BONUS_TYPE_MITRA_SHOPPINGS')) define('BONUS_TYPE_MITRA_SHOPPINGS', [
    BONUS_TYPE_MGR_DIRECT_MITRA => [
        'pageKey' => 'bonusMgrDirectMitra',
        'routeKey' => 'mgr-direct-mp',
        'title' => 'Manager Direct Mitra',
    ],
    BONUS_TYPE_MGR_DISTRIBUTOR => [
        'pageKey' => 'bonusMgrDC',
        'routeKey' => 'mgr-dc',
        'title' => 'Manager Distributor',
    ],
    BONUS_TYPE_MITRA_DIRECT_MITRA => [
        'pageKey' => 'bonusMitraMitra',
        'routeKey' => 'mp-direct-mp',
        'title' => 'Mitra Direct Mitra',
    ],
]);
// end bonus

if (!defined('DATE_BONUS_MGR_DIRECT_MITRA')) define('DATE_BONUS_MGR_DIRECT_MITRA', '2022-08-21');

// point purchase
if (!defined('POINT_TYPE_SHOPPING_SELF')) define('POINT_TYPE_SHOPPING_SELF', 1);
if (!defined('POINT_TYPE_SHOPPING_MEMBER')) define('POINT_TYPE_SHOPPING_MEMBER', 5);
if (!defined('POINT_TYPE_ACTIVATE_MEMBER')) define('POINT_TYPE_ACTIVATE_MEMBER', 10);
if (!defined('POINT_TYPE_REPEAT_ORDER')) define('POINT_TYPE_REPEAT_ORDER', 20);
// end point purchase

// claim reward status
if (!defined('CLAIM_STATUS_PENDING')) define('CLAIM_STATUS_PENDING', 0);
if (!defined('CLAIM_STATUS_FINISH')) define('CLAIM_STATUS_FINISH', 1);
if (!defined('CLAIM_STATUS_CANCEL')) define('CLAIM_STATUS_CANCEL', 2);
if (!defined('CLAIM_STATUS_REJECT')) define('CLAIM_STATUS_REJECT', 3);
if (!defined('CLAIM_STATUS_LIST')) define('CLAIM_STATUS_LIST', [
    CLAIM_STATUS_PENDING => 'Dalam Proses',
    CLAIM_STATUS_FINISH => 'Selesai',
    CLAIM_STATUS_CANCEL => 'Batal',
    CLAIM_STATUS_REJECT => 'Ditolak',
]);
// end claim reward status

// bonus mitra
if (!defined('BONUS_MITRA_SPONSOR')) define('BONUS_MITRA_SPONSOR', 1);
if (!defined('BONUS_MITRA_RO')) define('BONUS_MITRA_RO', 2);
if (!defined('BONUS_MITRA_CASHBACK_RO')) define('BONUS_MITRA_CASHBACK_RO', 3);
if (!defined('BONUS_MITRA_GENERASI')) define('BONUS_MITRA_GENERASI', 4);
if (!defined('BONUS_MITRA_PRESTASI')) define('BONUS_MITRA_PRESTASI', 5);
if (!defined('BONUS_MITRA_CASHBACK_ACTIVATION')) define('BONUS_MITRA_CASHBACK_ACTIVATION', 6);
if (!defined('BONUS_MITRA_POINT_RO')) define('BONUS_MITRA_POINT_RO', 7);


if (!defined('BONUS_MITRA_NAMES')) define('BONUS_MITRA_NAMES', [
    BONUS_MITRA_SPONSOR => 'Sponsor',
    // BONUS_MITRA_RO => 'Sponsor RO',
    BONUS_MITRA_CASHBACK_RO => 'Cashback',
    // BONUS_MITRA_CASHBACK_ACTIVATION => 'Cashback Sponsoring',
    // BONUS_MITRA_GENERASI => 'Titik Generasi',
    BONUS_MITRA_PRESTASI => 'Prestasi',
    BONUS_MITRA_POINT_RO => 'Point RO',
]);

// bonus level (generasi dan prestasi)
if (!defined('BONUS_MITRA_LEVEL_GENERASI')) define('BONUS_MITRA_LEVEL_GENERASI', 1);
if (!defined('BONUS_MITRA_LEVEL_PRESTASI')) define('BONUS_MITRA_LEVEL_PRESTASI', 2);

if (!defined('BONUS_MITRA_LEVELS')) define('BONUS_MITRA_LEVELS', [
    BONUS_MITRA_LEVEL_GENERASI => ['code' => 'G', 'name' => 'Generasi'],
    BONUS_MITRA_LEVEL_PRESTASI => ['code' => 'P', 'name' => 'Prestasi'],
]);
// end bonus level
// end bonus mitra

// wd
if (!defined('WD_FEE')) define('WD_FEE', 10000);
// end wd

// vendor gateway
if (!defined('VENDOR_TYPE_SMS_REGULAR')) define('VENDOR_TYPE_SMS_REGULAR', 1);
if (!defined('VENDOR_TYPE_SMS_MASKING')) define('VENDOR_TYPE_SMS_MASKING', 2);
if (!defined('VENDOR_TYPE_WHATSAPP')) define('VENDOR_TYPE_WHATSAPP', 3);
// end vendor gateway
