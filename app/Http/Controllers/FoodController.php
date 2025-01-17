<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\food;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FoodController extends Controller
{

    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',  // Pastikan kategori ada
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif,svg,webp|max:10240',  // Maksimal 10MB
                'stock' => 'required|integer|min:0'
            ]);

            // Menyimpan gambar jika ada
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('foods', 'public'); // Simpan di storage

                // Simpan URL lengkap ke image_url
                $imageUrl = asset('storage/' . $imagePath);
            } else {
                $imageUrl = null;
            }

            // Menyimpan makanan baru ke database
            $food = Food::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'price' => $request->price,
                'image_url' => $imageUrl, // URL lengkap gambar
                'stock' => $request->stock,
            ]);

            return response()->json($food, 201);  // Return data makanan yang baru ditambahkan
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }



    public function update(Request $request, $name)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'description' => 'nullable|string',
                'price' => 'nullable|numeric',
                'stock' => 'nullable|integer|min:0'
            ]);

            // Cari data makanan berdasarkan nama
            $food = Food::where('name', $name)->firstOrFail();

            // Update data makanan
            $food->update([
                'description' => $request->description ?? $food->description,
                'price' => $request->price ?? $food->price,
                'stock' => $request->stock ?? $food->stock,
            ]);

            return response()->json([
                'message' => 'Food updated successfully',
                'food' => $food,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Food not found or update failed',
                'error' => $e->getMessage(),
            ], 404);
        }
    }



    public function index()
    {
        $foods = Food::all();

        // Mengubah URL relatif menjadi URL penuh
        $foods->each(function ($food) {
            $food->image_url = asset('storage/' . $food->image_url);
        });

        return response()->json($foods);
    }


    public function index1()
    {
        $menus = Food::all()->map(function ($item) {
            return [
                'id' => (int) $item->id,
                'name' => $item->name,
                'category_id' => (int) $item->category_id,
                'description' => $item->description,
                'price' => (float) $item->price,
                'image_url' => $item->image_url,
                'stock' => (int) $item->stock,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return response()->json($menus);
    }


    public function getByCategory($categoryId)
    {
        $menus = Food::where('category_id', $categoryId)->get()->map(function ($item) {
            return [
                'id' => (int) $item->id,
                'name' => $item->name,
                'category_id' => (int) $item->category_id,
                'description' => $item->description,
                'price' => (float) $item->price,
                'image_url' => $item->image_url,
                'stock' => (int) $item->stock,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return response()->json($menus);
    }



    public function search(Request $request)
    {
        // Mengambil input pencarian dari parameter 'query'
        $query = $request->input('q');

        // Menangani jika query kosong
        if (empty($query)) {
            return response()->json(['message' => 'Query is required'], 400);
        }

        // Melakukan pencarian pada kolom 'name' dan 'description'
        $foods = Food::where('name', 'like', '%' . $query . '%')
            ->get();

        // Jika tidak ada hasil
        if ($foods->isEmpty()) {
            return response()->json(['message' => 'No food found matching your query'], 404);
        }

        // Mengembalikan hasil pencarian dalam bentuk JSON
        return response()->json($foods);
    }
}
