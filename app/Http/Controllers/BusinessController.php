<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Facades\Validator;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($userId)
    {
        $business = Business::where('user_id', $userId)->get();
        return response()->json(
            [
                'status' => 'success',
                'message' => 'Data bisnis berhasil diambil',
                'data' => $business
            ],
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $userId)
    {
        $rules = [
            'nama_bisnis' => 'required|string',
            'kategori_bisnis' => 'required|string',
            'mata_uang' => 'required|string',
            'gambar_bisnis' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ],
                422
            );
        }

        $data = new Business();
        $data->nama_bisnis = $request->nama_bisnis;
        $data->kategori_bisnis = $request->kategori_bisnis;
        $data->mata_uang = $request->mata_uang;

        if ($request->hasFile('gambar_bisnis')) {
        $gambarPath = $request->file('gambar_bisnis')->store('gambar_bisnis', 'public');
        $data->gambar_bisnis = 'https://kelola.abdaziz.my.id/storage/' . $gambarPath;
        }

        $data->user_id = $userId;
        $data->save();

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Data bisnis berhasil ditambahkan',
                'data' => $data
            ],
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show($userId, $id)
    {
        $business = Business::where('user_id', $userId)->where('id', $id)->first();

        if (!$business) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Data bisnis tidak ditemukan'
                ],
                404
            );
        }

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Data bisnis berhasil diambil',
                'data' => $business
            ],
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $userId, $id)
    {
        $rules = [
            'nama_bisnis' => 'required|string',
            'kategori_bisnis' => 'required|string',
            'mata_uang' => 'required|string',
            'gambar_bisnis' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ],
                422
            );
        }

        $business = Business::where('user_id', $userId)->where('id', $id)->first();

        if (!$business) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Data bisnis tidak ditemukan'
                ],
                404
            );
        }

        $business->nama_bisnis = $request->nama_bisnis;
        $business->kategori_bisnis = $request->kategori_bisnis;
        $business->mata_uang = $request->mata_uang;

        if ($request->hasFile('gambar_bisnis')) {
            $gambarPath = $request->file('gambar_bisnis')->store('gambar_bisnis', 'public');
            $business->gambar_bisnis = $gambarPath;
        }

        $business->save();

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Data bisnis berhasil diperbarui',
                'data' => $business
            ],
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($userId, $id)
    {
        $business = Business::where('user_id', $userId)->where('id', $id)->first();

        if (!$business) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Data bisnis tidak ditemukan'
                ],
                404
            );
        }

        $business->delete();

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Data bisnis berhasil dihapus'
            ],
            200
        );
    }
}
