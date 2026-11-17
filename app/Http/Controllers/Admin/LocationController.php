<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Thana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class LocationController extends Controller
{
    /**
     * অবস্থান তালিকা
     */
    public function index(Request $request)
    {
        $level = $request->query('level', 'districts');

        if (!in_array($level, ['districts', 'upazilas', 'thanas'])) {
            $level = 'districts';
        }

        $counts = [
            'districts' => District::count(),
            'upazilas' => Upazila::count(),
            'thanas' => Thana::count(),
        ];

        $districts = District::orderBy('name_bn')->get();

        $search = trim($request->query('search', ''));

        if ($level === 'districts') {
            $query = District::withCount('upazilas');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_bn', 'like', "%{$search}%")
                        ->orWhere('division_name', 'like', "%{$search}%");
                });
            }

        } elseif ($level === 'upazilas') {
            $query = Upazila::with('district')
                ->withCount('thanas');

            if ($request->filled('district_id')) {
                $query->where('district_id', $request->district_id);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_bn', 'like', "%{$search}%");
                });
            }

        } else {
            $query = Thana::with(['upazila.district', 'serviceArea']);

            if ($request->filled('district_id')) {
                $query->whereHas('upazila', function ($q) use ($request) {
                    $q->where('district_id', $request->district_id);
                });
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_bn', 'like', "%{$search}%");
                });
            }
        }

        $rows = $query->orderBy('name_en')
            ->paginate(25)
            ->withQueryString();

        return view('admin.locations', compact(
            'counts',
            'level',
            'districts',
            'rows'
        ));
    }


    /**
     * Excel / CSV ফাইল থেকে অবস্থানের তথ্য Import
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'excel.required' => 'Excel ফাইল নির্বাচন করুন।',
            'excel.file' => 'সঠিক ফাইল নির্বাচন করুন।',
            'excel.mimes' => 'শুধুমাত্র XLSX, XLS অথবা CSV ফাইল দেওয়া যাবে।',
            'excel.max' => 'ফাইলের সাইজ ১০ MB-এর বেশি হতে পারবে না।',
        ]);

        try {

            // Excel / CSV ফাইল Read
            $filePath = $request->file('excel')->getRealPath();

            $reader = IOFactory::createReaderForFile($filePath);

            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($filePath);

            $sheet = $spreadsheet->getActiveSheet();

            $excelRows = $sheet->toArray(null, true, true, false);

            $spreadsheet->disconnectWorksheets();

            unset($spreadsheet);

            if (count($excelRows) < 2) {
                return back()->with(
                    'error',
                    'Excel ফাইলে কোনো তথ্য পাওয়া যায়নি।'
                );
            }

            // Header পরিষ্কার করা
            $headers = array_map(function ($header) {

                $header = preg_replace(
                    '/^\xEF\xBB\xBF/',
                    '',
                    (string) $header
                );

                $header = strtolower(trim($header));

                return preg_replace('/[\s\-]+/', '_', $header);

            }, $excelRows[0]);

            // প্রয়োজনীয় Column
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

            $dataRows = array_slice($excelRows, 1);

            // Import Statistics
            $stats = [
                'districts' => 0,
                'upazilas' => 0,
                'thanas' => 0,
                'skipped' => 0,
            ];

            // District ও অন্যান্য তথ্য সংগ্রহ
            $districtRows = [];

            $validRows = [];

            foreach ($dataRows as $row) {

                $division = trim((string) (
                    $row[$columnMap['division']] ?? ''
                ));

                $districtEn = trim((string) (
                    $row[$columnMap['district_en']] ?? ''
                ));

                $districtBn = trim((string) (
                    $row[$columnMap['district_bn']] ?? ''
                ));

                $upazilaEn = trim((string) (
                    $row[$columnMap['upazila_en']] ?? ''
                ));

                $upazilaBn = trim((string) (
                    $row[$columnMap['upazila_bn']] ?? ''
                ));

                $thanaEn = trim((string) (
                    $row[$columnMap['thana_en']] ?? ''
                ));

                $thanaBn = trim((string) (
                    $row[$columnMap['thana_bn']] ?? ''
                ));

                // প্রয়োজনীয় তথ্য না থাকলে Row বাদ
                if (
                    $division === '' ||
                    $districtEn === '' ||
                    $districtBn === '' ||
                    $upazilaEn === '' ||
                    $upazilaBn === '' ||
                    $thanaEn === '' ||
                    $thanaBn === ''
                ) {
                    $stats['skipped']++;
                    continue;
                }

                // District একবারই সংগ্রহ
                $districtKey = mb_strtolower($districtEn);

                $districtRows[$districtKey] = [
                    'name_en' => $districtEn,
                    'name_bn' => $districtBn,
                    'division_name' => $division,
                ];

                $validRows[] = [
                    'district_key' => $districtKey,
                    'upazila_en' => $upazilaEn,
                    'upazila_bn' => $upazilaBn,
                    'thana_en' => $thanaEn,
                    'thana_bn' => $thanaBn,
                ];
            }

            if (empty($validRows)) {
                return back()->with(
                    'error',
                    'Import করার মতো সঠিক তথ্য পাওয়া যায়নি।'
                );
            }

            /*
             * Database Transaction
             * সব তথ্য সফল হলে Save হবে।
             * কোনো সমস্যা হলে সব পরিবর্তন Rollback হবে।
             */
            DB::transaction(function () use (
                $districtRows,
                $validRows,
                &$stats
            ) {

                // =====================================
                // 1. DISTRICT BULK INSERT / UPDATE
                // =====================================

                foreach (
                    array_chunk(array_values($districtRows), 500)
                    as $chunk
                ) {

                    DB::table('districts')->upsert(
                        $chunk,
                        ['name_en'],
                        ['name_bn', 'division_name']
                    );
                }

                // District ID একবারে সংগ্রহ
                $districts = DB::table('districts')
                    ->whereIn(
                        'name_en',
                        array_column(
                            array_values($districtRows),
                            'name_en'
                        )
                    )
                    ->get([
                        'district_id',
                        'name_en',
                    ]);

                $districtMap = [];

                foreach ($districts as $district) {

                    $districtMap[
                        mb_strtolower($district->name_en)
                    ] = $district->district_id;
                }


                // =====================================
                // 2. UPAZILA BULK INSERT / UPDATE
                // =====================================

                $upazilaRows = [];

                foreach ($validRows as $row) {

                    $districtId = $districtMap[
                        $row['district_key']
                    ] ?? null;

                    if (!$districtId) {
                        $stats['skipped']++;
                        continue;
                    }

                    $key = $districtId . '|' .
                        mb_strtolower($row['upazila_en']);

                    $upazilaRows[$key] = [
                        'district_id' => $districtId,
                        'name_en' => $row['upazila_en'],
                        'name_bn' => $row['upazila_bn'],
                    ];
                }

                foreach (
                    array_chunk(array_values($upazilaRows), 500)
                    as $chunk
                ) {

                    DB::table('upazilas')->upsert(
                        $chunk,
                        ['district_id', 'name_en'],
                        ['name_bn']
                    );
                }

                // Upazila ID একবারে সংগ্রহ
                $districtIds = array_values(array_unique(
                    array_column(
                        array_values($upazilaRows),
                        'district_id'
                    )
                ));

                $upazilas = DB::table('upazilas')
                    ->whereIn('district_id', $districtIds)
                    ->get([
                        'upazila_id',
                        'district_id',
                        'name_en',
                    ]);

                $upazilaMap = [];

                foreach ($upazilas as $upazila) {

                    $key = $upazila->district_id . '|' .
                        mb_strtolower($upazila->name_en);

                    $upazilaMap[$key] = $upazila->upazila_id;
                }


                // =====================================
                // 3. THANA BULK INSERT / UPDATE
                // =====================================

                $thanaRows = [];

                foreach ($validRows as $row) {

                    $districtId = $districtMap[
                        $row['district_key']
                    ] ?? null;

                    if (!$districtId) {
                        continue;
                    }

                    $upazilaKey = $districtId . '|' .
                        mb_strtolower($row['upazila_en']);

                    $upazilaId = $upazilaMap[
                        $upazilaKey
                    ] ?? null;

                    if (!$upazilaId) {
                        continue;
                    }

                    $key = $upazilaId . '|' .
                        mb_strtolower($row['thana_en']);

                    $thanaRows[$key] = [
                        'upazila_id' => $upazilaId,
                        'name_en' => $row['thana_en'],
                        'name_bn' => $row['thana_bn'],
                    ];
                }

                foreach (
                    array_chunk(array_values($thanaRows), 500)
                    as $chunk
                ) {

                    DB::table('thanas')->upsert(
                        $chunk,
                        ['upazila_id', 'name_en'],
                        ['name_bn']
                    );
                }

                // Statistics
                $stats['districts'] = count($districtRows);

                $stats['upazilas'] = count($upazilaRows);

                $stats['thanas'] = count($thanaRows);

            }, 3);

            // সফল Import Message
            return redirect()
                ->route('admin.locations')
                ->with(
                    'success',
                    'Excel Import সম্পন্ন হয়েছে! ' .
                    'জেলা: ' . $stats['districts'] .
                    ', উপজেলা: ' . $stats['upazilas'] .
                    ', থানা: ' . $stats['thanas'] .
                    ', বাদ পড়েছে: ' . $stats['skipped']
                );

        } catch (Throwable $e) {

            Log::error('Location Excel Import Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with(
                'error',
                'Excel Import করা যায়নি। বিস্তারিত জানতে Laravel Log পরীক্ষা করুন।'
            );
        }
    }


    /**
     * নতুন জেলা, উপজেলা অথবা থানা যোগ
     */
    public function store(Request $request, string $level)
    {
        if (!in_array($level, ['districts', 'upazilas', 'thanas'])) {
            abort(404);
        }

        if ($level === 'districts') {

            $data = $request->validate([
                'division_name' => 'required|string|max:40',
                'name_en' => 'required|string|max:60|unique:districts,name_en',
                'name_bn' => 'required|string|max:60',
            ]);

            District::create($data);

        } elseif ($level === 'upazilas') {

            $data = $request->validate([
                'district_id' => 'required|exists:districts,district_id',

                'name_en' => [
                    'required',
                    'string',
                    'max:60',

                    Rule::unique('upazilas')->where(
                        fn ($q) => $q->where(
                            'district_id',
                            $request->district_id
                        )
                    ),
                ],

                'name_bn' => 'required|string|max:60',
            ]);

            Upazila::create($data);

        } else {

            $data = $request->validate([
                'upazila_id' => 'required|exists:upazilas,upazila_id',

                'name_en' => [
                    'required',
                    'string',
                    'max:60',

                    Rule::unique('thanas')->where(
                        fn ($q) => $q->where(
                            'upazila_id',
                            $request->upazila_id
                        )
                    ),
                ],

                'name_bn' => 'required|string|max:60',
            ]);

            Thana::create($data);
        }

        return redirect()
            ->route('admin.locations', ['level' => $level])
            ->with('success', 'তথ্য সফলভাবে যোগ করা হয়েছে।');
    }


    /**
     * অবস্থানের তথ্য Update
     */
    public function update(Request $request, string $level, int $id)
    {
        if (!in_array($level, ['districts', 'upazilas', 'thanas'])) {
            abort(404);
        }

        if ($level === 'districts') {

            $item = District::where(
                'district_id',
                $id
            )->firstOrFail();

            $data = $request->validate([
                'division_name' => 'required|string|max:40',

                'name_en' => [
                    'required',
                    'string',
                    'max:60',

                    Rule::unique('districts', 'name_en')
                        ->ignore($id, 'district_id'),
                ],

                'name_bn' => 'required|string|max:60',
            ]);

        } elseif ($level === 'upazilas') {

            $item = Upazila::where(
                'upazila_id',
                $id
            )->firstOrFail();

            $data = $request->validate([
                'district_id' => 'required|exists:districts,district_id',

                'name_en' => [
                    'required',
                    'string',
                    'max:60',

                    Rule::unique('upazilas')->where(
                        fn ($q) => $q->where(
                            'district_id',
                            $request->district_id
                        )
                    )->ignore($id, 'upazila_id'),
                ],

                'name_bn' => 'required|string|max:60',
            ]);

        } else {

            $item = Thana::where(
                'thana_id',
                $id
            )->firstOrFail();

            $data = $request->validate([
                'upazila_id' => 'required|exists:upazilas,upazila_id',

                'name_en' => [
                    'required',
                    'string',
                    'max:60',

                    Rule::unique('thanas')->where(
                        fn ($q) => $q->where(
                            'upazila_id',
                            $request->upazila_id
                        )
                    )->ignore($id, 'thana_id'),
                ],

                'name_bn' => 'required|string|max:60',
            ]);
        }

        $item->update($data);

        return redirect()
            ->route('admin.locations', ['level' => $level])
            ->with('success', 'তথ্য সফলভাবে পরিবর্তন করা হয়েছে।');
    }


    /**
     * অবস্থানের তথ্য Delete
     */
    public function destroy(string $level, int $id)
    {
        if (!in_array($level, ['districts', 'upazilas', 'thanas'])) {
            abort(404);
        }

        try {

            if ($level === 'districts') {

                District::where(
                    'district_id',
                    $id
                )->firstOrFail()->delete();

            } elseif ($level === 'upazilas') {

                Upazila::where(
                    'upazila_id',
                    $id
                )->firstOrFail()->delete();

            } else {

                Thana::where(
                    'thana_id',
                    $id
                )->firstOrFail()->delete();
            }

            return redirect()
                ->route('admin.locations', ['level' => $level])
                ->with('success', 'তথ্য সফলভাবে মুছে ফেলা হয়েছে।');

        } catch (QueryException $e) {

            return back()->with(
                'error',
                'এই অবস্থানের অধীনে অন্য তথ্য যুক্ত আছে। তাই এটি মুছে ফেলা যাচ্ছে না।'
            );
        }
    }
}
