<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Thana;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LocationController extends Controller
{
    /**
     * Display location management page.
     */
    public function index()
    {
        $divisions = Division::with([
            'districts.upazilas.thanas'
        ])->orderBy('name_en')->get();

        return view('admin.locations', compact('divisions'));
    }

    /**
     * Import locations from Excel (.xlsx, .xls) or CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'excel.required' => 'অনুগ্রহ করে একটি Excel ফাইল নির্বাচন করুন।',
            'excel.mimes' => 'শুধুমাত্র XLSX, XLS অথবা CSV ফাইল Upload করা যাবে।',
            'excel.max' => 'ফাইলের সাইজ সর্বোচ্চ 10 MB হতে পারবে।',
        ]);

        // Read Excel file
        try {
            $file = $request->file('excel');

            $spreadsheet = IOFactory::load(
                $file->getRealPath()
            );

            $sheet = $spreadsheet->getActiveSheet();

            $rows = $sheet->toArray(
                null,
                true,
                true,
                false
            );

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

        } catch (\Throwable $e) {
            Log::error('Location Excel Import Read Error: ' . $e->getMessage());

            return back()->with(
                'error',
                'Excel ফাইল পড়া যায়নি। সঠিক XLSX, XLS অথবা CSV ফাইল Upload করুন।'
            );
        }

        if (count($rows) < 2) {
            return back()->with(
                'error',
                'Excel ফাইলে কোনো Import করার মতো তথ্য পাওয়া যায়নি।'
            );
        }

        // Normalize column headers
        $headers = array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            $header = strtolower(trim($header));
            $header = preg_replace('/[\s\-]+/', '_', $header);

            return $header;
        }, $rows[0]);

        $requiredHeaders = [
            'division',
            'district_en',
            'district_bn',
            'upazila_en',
            'upazila_bn',
            'thana_en',
            'thana_bn',
        ];

        $missingHeaders = array_diff(
            $requiredHeaders,
            $headers
        );

        if (!empty($missingHeaders)) {
            return back()->with(
                'error',
                'Excel ফাইলে প্রয়োজনীয় Column পাওয়া যায়নি: ' .
                implode(', ', $missingHeaders)
            );
        }

        $columnMap = array_flip($headers);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use (
                $rows,
                $columnMap,
                &$created,
                &$updated,
                &$skipped
            ) {
                foreach (array_slice($rows, 1) as $row) {

                    $getValue = function ($column) use (
                        $row,
                        $columnMap
                    ) {
                        $index = $columnMap[$column] ?? null;

                        return $index !== null
                            ? trim((string) ($row[$index] ?? ''))
                            : '';
                    };

                    $divisionName = $getValue('division');
                    $districtEn = $getValue('district_en');
                    $districtBn = $getValue('district_bn');
                    $upazilaEn = $getValue('upazila_en');
                    $upazilaBn = $getValue('upazila_bn');
                    $thanaEn = $getValue('thana_en');
                    $thanaBn = $getValue('thana_bn');

                    // Skip incomplete rows
                    if (
                        $divisionName === '' ||
                        $districtEn === '' ||
                        $upazilaEn === '' ||
                        $thanaEn === ''
                    ) {
                        $skipped++;
                        continue;
                    }

                    // Find Division
                    $division = Division::where(
                        'name_en',
                        $divisionName
                    )->first();

                    if (!$division) {
                        $skipped++;
                        continue;
                    }

                    // Create or update District
                    $district = District::firstOrNew([
                        'division_id' => $division->id,
                        'name_en' => $districtEn,
                    ]);

                    $isNewDistrict = !$district->exists;

                    $district->name_bn = $districtBn;
                    $district->save();

                    if ($isNewDistrict) {
                        $created++;
                    } else {
                        $updated++;
                    }

                    // Create or update Upazila
                    $upazila = Upazila::firstOrNew([
                        'district_id' => $district->id,
                        'name_en' => $upazilaEn,
                    ]);

                    $isNewUpazila = !$upazila->exists;

                    $upazila->name_bn = $upazilaBn;
                    $upazila->save();

                    if ($isNewUpazila) {
                        $created++;
                    } else {
                        $updated++;
                    }

                    // Create or update Thana
                    $thana = Thana::firstOrNew([
                        'upazila_id' => $upazila->id,
                        'name_en' => $thanaEn,
                    ]);

                    $isNewThana = !$thana->exists;

                    $thana->name_bn = $thanaBn;
                    $thana->save();

                    if ($isNewThana) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
            });

        } catch (\Throwable $e) {
            Log::error('Location Import Database Error: ' . $e->getMessage());

            return back()->with(
                'error',
                'Location Import করার সময় সমস্যা হয়েছে। Database ও Excel Column পরীক্ষা করুন।'
            );
        }

        return back()->with(
            'success',
            "Import সফল হয়েছে! নতুন তৈরি: {$created}, Update: {$updated}, Skip: {$skipped}"
        );
    }
}
