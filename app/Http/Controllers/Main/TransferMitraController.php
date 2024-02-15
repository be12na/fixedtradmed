<?php

namespace App\Http\Controllers\Main;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\MitraBonusLevel;
use App\Models\Product;
use App\Models\UserBonus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class TransferMitraController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $dateRange = session('filter.dates', []);
        $today = Carbon::today();

        if (empty($dateRange)) {
            $dateRange = [
                'start' => (clone $today)->startOfWeek(),
                'end' => $today
            ];
        }

        if ($dateRange['start']->format('Y-m-d') > date('Y-m-d')) $dateRange['start'] = $today;
        if ($dateRange['end']->format('Y-m-d') > date('Y-m-d')) $dateRange['end'] = $today;

        $currentBankCode = session('filter.bankCode');
        $currentStatusId = session('filter.statusId', PROCESS_STATUS_PENDING);

        return view('main.transfers.mitra.index', [
            'dateRange' => $dateRange,
            'currentBankCode' => $currentBankCode,
            'currentStatusId' => $currentStatusId,
            'windowTitle' => 'Daftar Transfer Member',
            'breadcrumbs' => ['Transfer', 'Member', 'Daftar']
        ]);
    }

    public function datatable(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));

        $bankCode = $request->get('bank_code');
        $statusId = $request->get('status_id', -1);

        $baseQuery = DB::table('mitra_purchases')
            ->join('users', 'users.id', '=', 'mitra_purchases.mitra_id')
            ->leftJoin(DB::raw('users as referral'), 'referral.id', '=', 'users.referral_id')
            ->selectRaw("
                mitra_purchases.id,
                mitra_purchases.code as kode,
                mitra_purchases.purchase_date,
                concat(mitra_purchases.purchase_date, '-', mitra_purchases.id) as tanggal,
                mitra_purchases.bank_name,
                mitra_purchases.purchase_status,
                mitra_purchases.total_purchase,
                mitra_purchases.discount_amount,
                mitra_purchases.total_transfer,
                mitra_purchases.is_v2,
                users.name as mitra_name,
                referral.name as referral_name
            ")
            ->whereBetween('mitra_purchases.purchase_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('mitra_purchases.is_transfer', '=', true);

        if (!empty($bankCode)) {
            $baseQuery = $baseQuery->where('mitra_purchases.bank_code', '=', $bankCode);
        }

        if (in_array($statusId, [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])) {
            $baseQuery = $baseQuery->where('mitra_purchases.purchase_status', '=', $statusId);
        }

        session([
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
            'filter.bankCode' => $bankCode,
            'filter.statusId' => $statusId,
        ]);

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as transferan"))
            ->mergeBindings($baseQuery);

        $result = datatables()->query($query)
            ->editColumn('tanggal', function ($row) {
                return formatFullDate($row->purchase_date);
            })
            ->editColumn('mitra_name', function ($row) {
                $result = "<div>{$row->mitra_name}</div>";
                $format = '<div class="text-decoration-underline text-primary">%s</div>';
                $format = sprintf($format, 'Referral : ' . ($row->referral_name ?? env('APP_COMPANY')));

                $result .= $format;

                return $result;
            })
            ->editColumn('purchase_status', function ($row) {
                $cls = 'bg-warning';
                if ($row->purchase_status == PROCESS_STATUS_APPROVED) {
                    $cls = 'bg-success text-light';
                } elseif ($row->purchase_status == PROCESS_STATUS_REJECTED) {
                    $cls = 'bg-danger text-light';
                }

                $text = Arr::get(PROCESS_STATUS_LIST, $row->purchase_status);

                $html = "<div class=\"text-center\"><span class=\"py-1 px-2 {$cls}\">{$text}<span></div>";

                return new HtmlString($html);
            })
            ->addColumn('view', function ($row) {
                $routeView = route('main.transfers.mitra.detail', ['mitraPurchase' => $row->id]);
                $buttonView = "<button type=\"button\" class=\"btn btn-sm btn-outline-success\" onclick=\"window.location.href='{$routeView}';\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString($buttonView);
            })->escapeColumns(['view']);

        return $result->toJson();
    }

    public function detail(Request $request)
    {
        return view('main.transfers.mitra.detail', [
            'transfer' => $request->mitraPurchase,
            'windowTitle' => 'Detail Transfer Member',
            'breadcrumbs' => ['Transfer', 'Member', 'Detail']
        ]);
    }

    public function actionTransfer(Request $request)
    {
        $mode = intval($request->get('action_mode', PROCESS_STATUS_PENDING));

        if (!in_array($mode, [PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])) {
            return response($this->validationMessages('Proses tidak dikenali.'), 404);
        }

        $transfer = $request->mitraPurchase;
        $mitra = $transfer->mitra;

        $values = [
            'purchase_status' => $mode,
        ];

        $responCode = 200;
        $responText = route('main.transfers.mitra.index');
        $msg = 'dikonfirmasi';
        $valid = true;

        if ($mode == PROCESS_STATUS_REJECTED) {
            $msg = 'ditolak';
            $values['status_note'] = $request->get('status_note');
            $validator = Validator::make($values, [
                'status_note' => 'required|string|max:250'
            ], [], [
                'status_note' => 'Keterangan'
            ]);

            if ($validator->fails()) {
                $valid = false;
                $responCode = 400;
                $responText = $this->validationMessages($validator);
            }
        }

        if ($valid === true) {
            if (empty($transfer->referral_id)) {
                $values['referral_id'] = $mitra->referral_id;
            }

            $bonusPoints = [];
            $dateTime = Carbon::now();
            $isRO = false;
            $sponsorList = [];
            $isRO = $mitra->is_reseller;
            $maxLevelPrestasi = MitraBonusLevel::query()->byType(BONUS_MITRA_LEVEL_PRESTASI)->max('level') ?? 0;
            $highestProduct = $transfer->products->max('product.package_range');
            $mitraPackageProduct = Product::query()->byPackageRange($highestProduct)->first();

            if ($mode == PROCESS_STATUS_APPROVED) {
                $pointDate = $dateTime->format('Y-m-d');

                // poin sponsor member aktivasi / RO
                if (!empty($referral = $mitra->referral)) {
                    if ($isRO) {
                        foreach ($transfer->products as $purchaseProduct) {
                            $point = $purchaseProduct->product->upline_point * $purchaseProduct->product_qty;

                            if ($point > 0) {
                                $bonusPoints[] = [
                                    'point_date' => $pointDate,
                                    'user_id' => $referral->id,
                                    'from_user_id' => $mitra->id,
                                    'point_type' => POINT_TYPE_REPEAT_ORDER,
                                    'user_package_id' => $purchaseProduct->product_id,
                                    'purchase_id' => $transfer->id,
                                    'point_unit' => $point,
                                    'point' => $point,
                                ];
                            }
                        }
                    } else {
                        $point = $mitraPackageProduct->upline_point;

                        if ($point > 0) {
                            $bonusPoints[] = [
                                'point_date' => $pointDate,
                                'user_id' => $referral->id,
                                'from_user_id' => $mitra->id,
                                'point_type' => POINT_TYPE_ACTIVATE_MEMBER,
                                'user_package_id' => $mitraPackageProduct->id,
                                'purchase_id' => $transfer->id,
                                'point_unit' => $point,
                                'point' => $point,
                            ];
                        }
                    }
                }

                $sponsor = $mitra;
                $level = 0;

                while (!empty($sponsor = $sponsor->referral)) {
                    $level += 1;
                    $sponsorList[] = [
                        'user' => $sponsor,
                        'level' => $level,
                    ];
                }
            }

            DB::beginTransaction();
            try {
                $values['status_at'] = $dateTime->format('Y-m-d H:i:s');
                $values['status_by'] = $request->user()->id;

                if ($mode == PROCESS_STATUS_APPROVED) {
                    if ($isRO) {
                        // bonus cashback RO
                        UserBonus::createBonusCashbackRO($transfer);
                        // bonus point ro for self
                        UserBonus::createBonusPointRO($transfer, true);
                    } else {
                        $mitra->update([
                            'mitra_type' => MITRA_TYPE_RESELLER,
                            'level_id' => MITRA_TYPE_RESELLER,
                        ]);
                        // bonus sponsor
                        UserBonus::createBonusSponsor($transfer);

                        // bonus point ro for sponsor (jika dgn aktivasi ini, si sponsor mencapai target point)
                        UserBonus::createBonusPointRO($transfer, false);
                    }

                    // bonus level prestasi
                    UserBonus::createBonusLevel($transfer, BONUS_MITRA_LEVEL_PRESTASI);

                    $transfer->update($values);

                    $omzetValues = $this->neo->omzetFromApprovedTransfer($transfer);
                    if (!empty($omzetValues)) {
                        DB::table('omzet_members')->insert($omzetValues);
                    }

                    if (!empty($bonusPoints)) {
                        DB::table('mitra_points')->insert($bonusPoints);
                    }
                } else {
                    $transfer->update($values);
                }

                DB::commit();

                session([
                    'message' => "Data transfer mitra berhasil {$msg}.",
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                $responCode = 500;
                $message = !isLive() ? $e->getMessage() : 'Telah terjadi kesalahan pada server. Silahkan coba lagi.';
                $responText = $this->validationMessages($message);
            }
        }

        return response($responText, $responCode);
    }
}
