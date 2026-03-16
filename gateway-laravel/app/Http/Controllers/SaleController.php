<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SaleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|string',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        $flaskBaseUrl = config('services.flask.base_url');
        $expressBaseUrl = config('services.express.base_url');

        $saleProducts = [];
        $total = 0;

        foreach ($request->products as $item) {
            $productResponse = Http::baseUrl($flaskBaseUrl)
                ->acceptJson()
                ->get("/products/{$item['product_id']}");

            if ($productResponse->failed()) {
                return response()->json([
                    'error' => 'Producto no encontrado en inventario',
                    'product_id' => $item['product_id'],
                ], 404);
            }

            $product = $productResponse->json();
            $stock = (int) ($product['stock'] ?? 0);
            $quantity = (int) $item['quantity'];

            if ($stock < $quantity) {
                return response()->json([
                    'error' => 'Stock insuficiente',
                    'product_id' => $item['product_id'],
                    'available_stock' => $stock,
                ], 400);
            }

            $price = (float) $product['price'];
            $subtotal = $price * $quantity;
            $total += $subtotal;

            $saleProducts[] = [
                'product_id' => $product['id'],
                'name' => $product['name'],
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
            ];
        }

        foreach ($saleProducts as $item) {
            $productResponse = Http::baseUrl($flaskBaseUrl)
                ->acceptJson()
                ->get("/products/{$item['product_id']}");

            $product = $productResponse->json();
            $newStock = ((int) $product['stock']) - ((int) $item['quantity']);

            Http::baseUrl($flaskBaseUrl)
                ->acceptJson()
                ->put("/products/{$item['product_id']}/stock", [
                    'stock' => $newStock,
                ]);
        }

        $salePayload = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'products' => $saleProducts,
            'total' => $total,
        ];

        $saleResponse = Http::baseUrl($expressBaseUrl)
            ->acceptJson()
            ->post('/sales', $salePayload);

        return response()->json([
            'message' => 'Venta registrada correctamente',
            'sale' => $saleResponse->json(),
        ], $saleResponse->status());
    }

    public function index()
    {
        $expressBaseUrl = config('services.express.base_url');

        $res = Http::baseUrl($expressBaseUrl)
            ->acceptJson()
            ->get('/sales');

        return response()->json($res->json(), $res->status());
    }

    public function byUser()
    {
        $expressBaseUrl = config('services.express.base_url');
        $user = Auth::user();

        $res = Http::baseUrl($expressBaseUrl)
            ->acceptJson()
            ->get("/sales/user/{$user->id}");

        return response()->json($res->json(), $res->status());
    }

    public function byDate($date)
    {
        $expressBaseUrl = config('services.express.base_url');

        $res = Http::baseUrl($expressBaseUrl)
            ->acceptJson()
            ->get("/sales/date/{$date}");

        return response()->json($res->json(), $res->status());
    }
}
