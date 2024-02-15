<?php

namespace App\Http\Controllers\Main\Products;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductReward;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class RewardController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $product = $request->product;

        return view('main.products.reward.index', [
            'product' => $product,
            'windowTitle' => 'Daftar Reward Produk',
            'breadcrumbs' => ['Master', 'Produk', 'Reward']
        ]);
    }

    public function datatable(Request $request)
    {
        $product = $request->product;
        $canEdit = hasPermission('main.master.product.reward.edit');
        $rewards = $product->mitraReward()
            ->orderBy('reward_type')
            ->orderBy('total_qty')
            ->get();
        $productId = $product->id;

        $query = DB::table('product_rewards')
            ->selectRaw("
            product_rewards.id, product_rewards.reward_type, 
            product_rewards.total_qty, product_rewards.reward_value
            ")
            ->where('product_rewards.product_id', '=', $productId)
            ->whereNull('product_rewards.deleted_at')
            ->orderByRaw("product_rewards.reward_type, product_rewards.total_qty");

        return datatables()->query($query)
            ->addColumn('reward_name', function ($row) {
                return Arr::get(MITRA_REWARD_CATEGORIES, $row->reward_type, '-');
            })
            ->editColumn('total_qty', function ($row) use ($rewards) {
                $result = formatNumber($row->total_qty, 0) . ' s/d ';
                $reward = $rewards->where('reward_type', '=', $row->reward_type)
                    ->where('total_qty', '>', $row->total_qty)
                    ->first();

                if (!empty($reward)) {
                    $result .= formatNumber($reward->total_qty - 1, 0);
                } else {
                    $result .= ' UP';
                }

                return $result;
            })
            ->addColumn('view', function ($row) use ($canEdit, $productId) {
                $buttons = [];

                if ($canEdit) {
                    $routeEdit = route('main.master.product.reward.edit', ['product' => $productId, 'mitraReward' => $row->id]);
                    $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-success me-1\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeEdit}\" title=\"Edit\"><i class=\"fa-solid fa-pencil-alt mx-1\"></i></button>";
                }

                return new HtmlString(implode('', $buttons));
            })
            ->escapeColumns()
            ->toJson();
    }

    private function validateInput(array $values, ProductReward $productReward = null)
    {
        $result = ['status' => true, 'message' => ''];

        $inCategories = implode(',', array_keys(MITRA_REWARD_CATEGORIES));

        $uniqueQty = new Unique('product_rewards', 'total_qty');
        $uniqueQty = $uniqueQty->whereNull('deleted_at');

        if (!empty($values['reward_type'])) {
            $uniqueQty = $uniqueQty->where('reward_type', $values['reward_type']);
        }

        if (!empty($values['product_id'])) {
            $uniqueQty = $uniqueQty->where('product_id', $values['product_id']);
        }

        if (!empty($productReward)) {
            $uniqueQty = $uniqueQty->ignore($productReward->id, 'id');
        }

        $validator = Validator::make($values, [
            'reward_type' => ['required', "in:{$inCategories}"],
            'total_qty' => ['required', 'integer', 'gt:0', $uniqueQty],
            'reward_value' => ['required', 'string', 'max:250'],
        ], [], [
            'reward_type' => 'Kategori',
            'total_qty' => 'QTY',
            'reward_value' => 'Reward',
        ]);

        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $this->validationMessages($validator);
        }

        return $result;
    }

    public function create(Request $request)
    {
        $product = $request->product;
        return view('main.products.reward.form', [
            'product' => $product,
            'data' => null,
            'postUrl' => route('main.master.product.reward.store', ['product' => $product->id]),
            'modalHeader' => 'Tambah Reward Produk',
        ]);
    }

    public function store(Request $request)
    {
        $product = $request->product;
        $values = $request->except(['_token']);

        $valid = $this->validateInput($values);

        $responCode = 200;
        $responText = route('main.master.product.reward.index', ['product' => $product->id]);

        if ($valid['status'] === true) {
            $values['set_by'] = $request->user()->id;

            DB::beginTransaction();
            try {
                ProductReward::create($values);

                session([
                    'message' => 'Reward Produk berhasil ditambahkan.',
                    'messageClass' => 'success',
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $responCode = 500;
                $responText = $this->validationMessages('Telah terjadi kesalahan pada server. Silahkan coba lagi.');
            }
        } else {
            $responCode = 400;
            $responText = $valid['message'];
        }

        return response($responText, $responCode);
    }

    public function edit(Request $request)
    {
        $product = $request->product;
        $reward = $request->mitraReward;
        return view('main.products.reward.form', [
            'product' => $product,
            'data' => $reward,
            'postUrl' => route('main.master.product.reward.update', ['product' => $product->id, 'mitraReward' => $reward->id]),
            'modalHeader' => 'Edit Reward Produk',
        ]);
    }

    public function update(Request $request)
    {
        $product = $request->product;
        $reward = $request->mitraReward;
        $values = $request->except(['_token']);

        $valid = $this->validateInput($values, $reward);

        $responCode = 200;
        $responText = route('main.master.product.reward.index', ['product' => $product->id]);

        if ($valid['status'] === true) {
            $values['set_by'] = $request->user()->id;
            $values['previous_id'] = $reward->id;

            DB::beginTransaction();
            try {
                $reward->delete();
                ProductReward::create($values);

                session([
                    'message' => 'Reward Produk berhasil diperbarui.',
                    'messageClass' => 'success',
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $responCode = 500;
                $responText = $this->validationMessages('Telah terjadi kesalahan pada server. Silahkan coba lagi.');
            }
        } else {
            $responCode = 400;
            $responText = $valid['message'];
        }

        return response($responText, $responCode);
    }
}
