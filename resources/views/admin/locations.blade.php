
@extends('layouts.dashboard')

@section('title', 'অবস্থান ব্যবস্থাপনা')

@section('content')

@php
    $labels = [
        'districts' => 'জেলা',
        'upazilas' => 'উপজেলা',
        'thanas' => 'থানা',
    ];
@endphp

<h1 class="dash-title">
    অবস্থান ব্যবস্থাপনা (বিভাগ › জেলা › উপজেলা › থানা)
</h1>

{{-- ================= COUNTS ================= --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body py-2">
                জেলা: <strong>{{ bn_digits($counts['districts']) }}</strong>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body py-2">
                উপজেলা: <strong>{{ bn_digits($counts['upazilas']) }}</strong>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body py-2">
                থানা: <strong>{{ bn_digits($counts['thanas']) }}</strong>
            </div>
        </div>
    </div>
</div>

{{-- ================= WARNING ================= --}}
@if(array_sum($counts) <= 6)
    <div class="alert alert-warning small">
        <i class="bi bi-exclamation-triangle me-1"></i>
        বর্তমানে শুধু ডেমো তথ্য আছে।
        প্রকৃত বাংলাদেশের প্রশাসনিক তথ্য নির্ভরযোগ্য উৎস থেকে
        সংগ্রহ করে ইমপোর্ট করুন।
    </div>
@endif

{{-- ================= IMPORT ================= --}}
<div class="card mb-3">
    <div class="card-header">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>
        Excel / CSV ফাইল থেকে অবস্থানের তথ্য ইমপোর্ট
    </div>

    <div class="card-body">

        <p class="small text-muted">
            ফাইলে নিচের কলামগুলো থাকতে হবে:
        </p>

        <div class="mb-3">
            <code>
                division, district_en, district_bn,
                upazila_en, upazila_bn, thana_en, thana_bn
            </code>
        </div>

        <p class="small text-muted">
            বিদ্যমান তথ্য ইংরেজি নাম অনুযায়ী মিলিয়ে আপডেট হবে।
            নতুন তথ্য স্বয়ংক্রিয়ভাবে যুক্ত হবে।
        </p>

        <p class="small">
            <a href="{{ asset('locations_template.csv') }}" download>
                <i class="bi bi-download me-1"></i>
                নমুনা CSV ফাইল ডাউনলোড করুন
            </a>
        </p>

        <form method="POST"
              action="{{ route('admin.locations.import') }}"
              enctype="multipart/form-data"
              class="row g-2"
              data-confirm="নির্বাচিত ফাইল থেকে অবস্থানের তথ্য ইমপোর্ট করবেন?">

            @csrf

            <div class="col-md-8">
                <input
                    type="file"
                    name="excel"
                    class="form-control"
                    accept=".csv,.txt,.xlsx,.xls"
                    required
                    aria-label="Excel অথবা CSV ফাইল নির্বাচন করুন"
                >

                @error('csv')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="col-md-4">
                <button type="submit" class="btn btn-accent w-100">
                    <i class="bi bi-upload me-1"></i>
                    ফাইল ইমপোর্ট করুন
                </button>
            </div>
        </form>

        @if(session('import_errors'))
            <div class="alert alert-warning mt-3 small">
                <strong>ইমপোর্টে কিছু সমস্যা হয়েছে:</strong>

                <ul class="mb-0 mt-2">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>
</div>

{{-- ================= TABS ================= --}}
<ul class="nav nav-tabs mb-3">

    @foreach($labels as $key => $label)

        <li class="nav-item">
            <a
                class="nav-link {{ $level === $key ? 'active' : '' }}"
                href="{{ route('admin.locations', ['level' => $key]) }}"
            >
                {{ $label }}
            </a>
        </li>

    @endforeach

</ul>

{{-- ================= FILTER ================= --}}
<form method="GET" class="card mb-3">

    <input type="hidden" name="level" value="{{ $level }}">

    <div class="card-body">

        <div class="row g-2 align-items-end">

            <div class="col-md-4">
                <label class="form-label small mb-1">
                    নাম (বাংলা / ইংরেজি)
                </label>

                <input
                    type="text"
                    class="form-control"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="নাম লিখুন"
                >
            </div>

            @if($level !== 'districts')

                <div class="col-md-4">

                    <label class="form-label small mb-1">
                        জেলা নির্বাচন করুন
                    </label>

                    <select class="form-select" name="district_id">

                        <option value="">সব জেলা</option>

                        @foreach($districts as $d)

                            <option
                                value="{{ $d->district_id }}"
                                @selected((string) request('district_id') === (string) $d->district_id)
                            >
                                {{ $d->name_en }}
                            </option>

                        @endforeach

                    </select>

                </div>

            @endif

            <div class="col-md-3 d-flex gap-2">

                <button class="btn btn-accent flex-fill" type="submit">
                    <i class="bi bi-search me-1"></i>
                    ফিল্টার
                </button>

                <a
                    href="{{ route('admin.locations', ['level' => $level]) }}"
                    class="btn btn-light"
                >
                    রিসেট
                </a>

            </div>

        </div>

    </div>
</form>

{{-- ================= ADD NEW ================= --}}
<div class="card mb-3">

    <div class="card-header">
        নতুন {{ $labels[$level] }} যোগ করুন
    </div>

    <div class="card-body">

        <form
            method="POST"
            action="{{ route('admin.locations.store', $level) }}"
            class="row g-2"
        >

            @csrf

            @if($level === 'districts')

                <div class="col-md-3">
                    <input
                        class="form-control"
                        name="division_name"
                        placeholder="বিভাগের নাম"
                        required
                        maxlength="40"
                    >
                </div>

            @endif

            @if($level === 'upazilas')

                <div class="col-md-3">
                    <select class="form-select" name="district_id" required>

                        <option value="">জেলা নির্বাচন করুন</option>

                        @foreach($districts as $d)
                            <option value="{{ $d->district_id }}">
                                {{ $d->name_en }}
                            </option>
                        @endforeach

                    </select>
                </div>

            @endif

            @if($level === 'thanas')

                <div class="col-md-3">
                    <input
                        class="form-control"
                        name="upazila_id"
                        placeholder="উপজেলা আইডি"
                        inputmode="numeric"
                        required
                        data-bn-digits
                    >
                </div>

            @endif

            <div class="col-md-3">

                <input
                    class="form-control"
                    name="name_en"
                    placeholder="ইংরেজি নাম"
                    required
                    maxlength="60"
                >

            </div>

            <div class="col-md-3">

                <input
                    class="form-control"
                    name="name_bn"
                    placeholder="বাংলা নাম"
                    required
                    maxlength="60"
                >

            </div>

            <div class="col-md-3">

                <button class="btn btn-accent w-100" type="submit">
                    <i class="bi bi-plus-lg me-1"></i>
                    যোগ করুন
                </button>

            </div>

        </form>

    </div>
</div>

{{-- ================= DATA TABLE ================= --}}
<div class="card">

    <div class="table-responsive">

        <table class="table table-hover mb-0">

            <thead>
                <tr>

                    <th>আইডি</th>
                    <th>ইংরেজি নাম</th>
                    <th>বাংলা নাম</th>

                    @if($level === 'districts')

                        <th>বিভাগ</th>
                        <th>উপজেলা</th>

                    @elseif($level === 'upazilas')

                        <th>জেলা</th>
                        <th>থানা</th>

                    @else

                        <th>উপজেলা / জেলা</th>
                        <th>সেবা এলাকা</th>

                    @endif

                    <th>কাজ</th>

                </tr>
            </thead>

            <tbody>

                @forelse($rows as $r)

                    @php
                        $id = $r->getKey();
                        $formId = 'location-form-'.$id;
                    @endphp

                    <tr>

                        <td>

                            {{ bn_digits($id) }}

                            <form
                                id="{{ $formId }}"
                                method="POST"
                                action="{{ route('admin.locations.update', [$level, $id]) }}"
                            >
                                @csrf
                                @method('PUT')
                            </form>

                        </td>

                        <td>

                            <input
                                form="{{ $formId }}"
                                class="form-control form-control-sm"
                                name="name_en"
                                value="{{ $r->name_en }}"
                                required
                                maxlength="60"
                            >

                        </td>

                        <td>

                            <input
                                form="{{ $formId }}"
                                class="form-control form-control-sm"
                                name="name_bn"
                                value="{{ $r->name_bn }}"
                                required
                                maxlength="60"
                            >

                        </td>

                        @if($level === 'districts')

                            <td>

                                <input
                                    form="{{ $formId }}"
                                    class="form-control form-control-sm"
                                    name="division_name"
                                    value="{{ $r->division_name }}"
                                    required
                                    maxlength="40"
                                >

                            </td>

                            <td>
                                {{ bn_digits($r->upazilas_count) }}
                            </td>

                        @elseif($level === 'upazilas')

                            <td>

                                {{ $r->district->name_bn }}

                                <input
                                    form="{{ $formId }}"
                                    type="hidden"
                                    name="district_id"
                                    value="{{ $r->district_id }}"
                                >

                            </td>

                            <td>
                                {{ bn_digits($r->thanas_count) }}
                            </td>

                        @else

                            <td>

                                {{ $r->upazila->name_bn }}
                                /
                                {{ $r->upazila->district->name_bn }}

                                <input
                                    form="{{ $formId }}"
                                    type="hidden"
                                    name="upazila_id"
                                    value="{{ $r->upazila_id }}"
                                >

                            </td>

                            <td>
                                {{ optional($r->serviceArea)->area_name ?? '—' }}
                            </td>

                        @endif

                        <td class="text-nowrap">

                            <button
                                form="{{ $formId }}"
                                class="btn btn-sm btn-outline-accent"
                                type="submit"
                            >
                                <i class="bi bi-save me-1"></i>
                                সংরক্ষণ
                            </button>

                            <form
                                method="POST"
                                action="{{ route('admin.locations.destroy', [$level, $id]) }}"
                                class="d-inline"
                                data-confirm="এই তথ্যটি মুছে ফেলবেন? ব্যবহৃত তথ্য মোছা যাবে না।"
                            >

                                @csrf
                                @method('DELETE')

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    type="submit"
                                    aria-label="মুছুন"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6">

                            <div class="empty-state">
                                <i class="bi bi-geo-alt"></i>
                                কোনো তথ্য পাওয়া যায়নি।
                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

{{-- ================= PAGINATION ================= --}}
<div class="mt-3">
    {{ $rows->links() }}
</div>

@endsection
