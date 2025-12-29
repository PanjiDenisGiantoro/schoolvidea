<?php

namespace App\Http\Controllers;

use App\Models\MerchantProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MerchantProductController extends Controller
{
    /**
     * Halaman index
     */
    public function index()
    {
        return view('pages.ekantin.dashboard_merchant.product.index');
    }

    // =======================
    // DATATABLE
    // =======================
    public function datatable(Request $request)
    {
        $columns = [
            'id',
            'product_name',
            'product_category',
            'product_unit',
            'number_of_product',
            'selling_price',
        ];
        $merchant = Auth::guard('merchant')->user();

        $query = MerchantProduct::where('merchant_id', $merchant->id);

        // 🔍 SEARCH
        if (! empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_category', 'like', "%{$search}%")
                    ->orWhere('product_unit', 'like', "%{$search}%");
            });
        }

        $recordsTotal = MerchantProduct::where('merchant_id', $merchant->id)->count();

        $recordsFiltered = $query->count();

        // ↕ ORDER
        if ($request->order) {
            $columnIndex = $request->order[0]['column'];
            $direction = $request->order[0]['dir'];
            $query->orderBy($columns[$columnIndex] ?? 'id', $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        // 📄 PAGINATION
        $products = $query
            ->offset($request->start)
            ->limit($request->length)
            ->get();

        $data = [];
        $no = $request->start + 1;

        foreach ($products as $product) {
            $data[] = [
                'no' => $no++,
                'product_name' => $product->product_name,
                'product_category' => $product->product_category,
                'number_of_product' => $product->number_of_product . ' ' . $product->product_unit,
                'selling_price' => 'Rp ' . number_format($product->selling_price, 0, ',', '.'),
                'status' => $product->status === 'active'
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Non Aktif</span>',
                'action' => '
                <div class="d-flex align-items-center gap-2">
                    <a href="' . route('merchant.product.show', $product->id) . '"
                       class="btn btn-sm btn-success rounded-pill">
                        <i class="ri-eye-line"></i>
                    </a>
                    <a href="' . route('merchant.product.edit', $product->id) . '"
                       class="btn btn-sm btn-warning rounded-pill">
                        <i class="ri-pencil-line"></i>
                    </a>
                    <button
                        type="button"
                        class="btn btn-sm btn-danger rounded-pill btn-delete"
                        data-url="' . route('merchant.product.destroy', $product->id) . '">
                        <i class="ri-delete-bin-7-line"></i>
                    </button>
                </div>
            ',
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Form create
     */
    public function create()
    {
        return view('pages.ekantin.dashboard_merchant.product.create');
    }

    /**
     * Simpan produk
     */
    public function store(Request $request)
    {

        $request->validate([
            'product_name' => 'string|max:150',
            'product_category' => 'string|max:100',
            'product_unit' => 'string|max:50',
            'number_of_product' => 'integer|min:0',
            'purchase_price' => 'numeric|min:0',
            'selling_price' => 'numeric|min:0',
            'image' => 'nullable|string',
            'status' => 'string',
        ]);
        $merchant = Auth::guard('merchant')->user();

        MerchantProduct::create([
            'merchant_id' => $merchant->id,
            'product_name' => $request->product_name,
            'product_category' => $request->product_category,
            'product_unit' => $request->product_unit,
            'number_of_product' => $request->number_of_product,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'image' => $request->image,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('merchant.product.index')
            ->with('success', 'Produk berhasil ditambahkan');
    }

    /**
     * Form edit
     */
    public function edit($id)
    {
        $merchant = Auth::guard('merchant')->user();
        $product = MerchantProduct::where('merchant_id', $merchant->id)
            ->findOrFail($id);

        return view('pages.ekantin.dashboard_merchant.product.create', compact('product'));
    }

    /**
     * Update produk
     */
    public function update(Request $request, $id)
    {
        $merchant = Auth::guard('merchant')->user();
        $product = MerchantProduct::where('merchant_id', $merchant->id)
            ->findOrFail($id);

        $request->validate([
            'product_name' => 'string|max:150',
            'product_category' => 'string|max:100',
            'product_unit' => 'string|max:50',
            'number_of_product' => 'integer|min:0',
            'purchase_price' => 'numeric|min:0',
            'selling_price' => 'numeric|min:0',
            'image' => 'string',
            'status' => 'string',
        ]);
        $oldImage = $product->image;

        $product->update([
            'product_name' => $request->product_name,
            'product_category' => $request->product_category,
            'product_unit' => $request->product_unit,
            'number_of_product' => $request->number_of_product,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'image' => $request->image,
            'status' => $request->status,
        ]);

        if ($request->image && $oldImage && $request->image !== $oldImage) {
            Storage::disk('public')->delete(
                str_replace('storage/', '', $oldImage)
            );
        }

        return redirect()
            ->route('merchant.product.index')
            ->with('success', 'Produk berhasil diperbarui');
    }

    /**
     * Hapus produk
     */
    public function destroy($id)
    {
        $merchant = Auth::guard('merchant')->user();
        $product = MerchantProduct::where('merchant_id', $merchant->id)
            ->findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete(
                str_replace('storage/', '', $product->image)
            );
        }

        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus',
        ]);
    }

    public function show($id)
    {
        $merchant = Auth::guard('merchant')->user();

        $product = MerchantProduct::where('merchant_id', $merchant->id)
            ->findOrFail($id);
        $show = true;

        return view('pages.ekantin.dashboard_merchant.product.create', compact('product', 'show'));
    }

    /**
     * Upload image (Dropzone)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:1024',
        ]);

        $file = $request->file('file');
        $filename = Str::random(15) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/product', $filename, 'public');

        return response()->json([
            'success' => true,
            'filepath' => 'storage/' . $path,
        ]);
    }
}
