<dl class="row mb-0">
  <dt class="col-sm-4">আবেদন নম্বর</dt><dd class="col-sm-8">{{ bn_digits($app->application_id) }}</dd>
  <dt class="col-sm-4">নাম</dt><dd class="col-sm-8">{{ $app->applicant_name }}</dd>
  <dt class="col-sm-4">ইমেইল</dt><dd class="col-sm-8">{{ $app->email }}</dd>
  <dt class="col-sm-4">মোবাইল</dt><dd class="col-sm-8">{{ \App\Support\Phone::display($app->phone) }}</dd>
  <dt class="col-sm-4">জাতীয় পরিচয়পত্র</dt><dd class="col-sm-8">{{ str_repeat('*', max(0, strlen($app->nid_number) - 4)).substr($app->nid_number, -4) }}</dd>
  <dt class="col-sm-4">জন্ম তারিখ</dt><dd class="col-sm-8">{{ bn_digits($app->date_of_birth->format('d/m/Y')) }} ({{ bn_digits($app->date_of_birth->age) }} বছর)</dd>
  <dt class="col-sm-4">শিক্ষাগত যোগ্যতা</dt><dd class="col-sm-8">{{ $app->education_level }}</dd>
  <dt class="col-sm-4">বাসস্থানের থানা</dt><dd class="col-sm-8">{{ $app->thana->fullName() }}</dd>
  <dt class="col-sm-4">আবেদনকৃত এলাকা</dt><dd class="col-sm-8">{{ $app->area->area_name }}</dd>
  <dt class="col-sm-4">অভিজ্ঞতা</dt><dd class="col-sm-8">{{ $app->experience_summary ?: '—' }}</dd>
  <dt class="col-sm-4">অবস্থা</dt><dd class="col-sm-8"><span class="badge text-bg-secondary">{{ __('kyaf.app_status.'.$app->status) }}</span></dd>
  <dt class="col-sm-4">জমার সময়</dt><dd class="col-sm-8">{{ bst($app->submitted_at) }}</dd>
  @if($app->review_remarks)<dt class="col-sm-4">মন্তব্য</dt><dd class="col-sm-8">{{ $app->review_remarks }}</dd>@endif
  @if($app->status === 'Approved' && $app->invitation_expires_at)<dt class="col-sm-4">আমন্ত্রণ মেয়াদ</dt><dd class="col-sm-8">{{ bst($app->invitation_expires_at) }} পর্যন্ত</dd>@endif
</dl>
