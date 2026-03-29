<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        // 1. Validasi harus ada titik koordinat
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'image' => 'required',
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();

        // 2. Cek apakah hari ini sudah absen masuk
        $cekAbsen = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($cekAbsen) {
            return response()->json([
                'message' => 'Anda sudah melakukan absen masuk hari ini.'
            ], 400);
        }

        // 3. Simpan data absen masuk
        $attendance = Attendance::create([
            'user_id' => $request->user()->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->toTimeString(),
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
            'image' => $request->image, // <--- INI HARUS ADA
        ]);

        return response()->json(['message' => 'Absen berhasil!', 'data' => $attendance]);
    }

    public function clockOut(Request $request)
    {
        // 1. Validasi titik koordinat
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();

        // 2. Cari data absen hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 3. Validasi status absen
        if (!$attendance) {
            return response()->json([
                'message' => 'Anda belum melakukan absen masuk hari ini.'
            ], 400);
        }

        if ($attendance->clock_out_time) {
            return response()->json([
                'message' => 'Anda sudah melakukan absen keluar hari ini.'
            ], 400);
        }

        // 4. Update data absen dengan jam dan lokasi keluar
        $attendance->update([
            'clock_out_time' => Carbon::now()->toTimeString(),
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Absen keluar berhasil dicatat!'
        ], 200);
    }

    public function history(Request $request)
    {
        $bulan = $request->query('bulan', now()->month);
        $tahun = $request->query('tahun', now()->year);

        $history = Attendance::where('user_id', $request->user()->id)
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($history);
    }
}
