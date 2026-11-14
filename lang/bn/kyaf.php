<?php

return [
    'status' => [
        'Submitted' => 'জমা দেওয়া হয়েছে',
        'Assigned' => 'স্টাফ নিয়োগ হয়েছে',
        'Verified' => 'যাচাইকৃত',
        'Approved' => 'অনুমোদিত',
        'Collected' => 'সংগৃহীত',
        'Distributed' => 'বিতরিত',
        'Completed' => 'সম্পন্ন',
        'Rejected' => 'প্রত্যাখ্যাত',
        'Cancelled' => 'বাতিল',
        'Expired' => 'মেয়াদোত্তীর্ণ',
    ],
    'status_color' => [
        'Submitted' => 'secondary', 'Assigned' => 'info', 'Verified' => 'primary', 'Approved' => 'success',
        'Collected' => 'warning', 'Distributed' => 'success', 'Completed' => 'success',
        'Rejected' => 'danger', 'Cancelled' => 'dark', 'Expired' => 'danger',
    ],
    'category' => ['Cooked' => 'রান্না করা খাবার', 'Packaged' => 'প্যাকেটজাত খাবার', 'Raw' => 'কাঁচা খাদ্যদ্রব্য', 'Bakery' => 'বেকারি আইটেম'],
    'donor_type' => ['Individual' => 'ব্যক্তি', 'Restaurant' => 'রেস্তোরাঁ', 'Organization' => 'প্রতিষ্ঠান'],
    'condition' => ['Good' => 'ভালো', 'Acceptable' => 'গ্রহণযোগ্য', 'Unsafe' => 'অনিরাপদ'],
    'app_status' => [
        'Submitted' => 'জমা হয়েছে', 'UnderReview' => 'পর্যালোচনাধীন', 'Approved' => 'অনুমোদিত',
        'Rejected' => 'প্রত্যাখ্যাত', 'Registered' => 'নিবন্ধিত', 'Expired' => 'মেয়াদোত্তীর্ণ',
    ],
    'user_status' => ['Active' => 'সক্রিয়', 'Inactive' => 'নিষ্ক্রিয়', 'Suspended' => 'স্থগিত', 'Locked' => 'লক', 'PendingActivation' => 'সক্রিয়করণের অপেক্ষায়'],
    'role' => ['ADMIN' => 'অ্যাডমিন', 'AREA_MANAGER' => 'এরিয়া ম্যানেজার', 'STAFF' => 'স্টাফ', 'DONOR' => 'দাতা'],
    'dist_status' => ['Planned' => 'পরিকল্পিত', 'Ongoing' => 'চলমান', 'Completed' => 'সম্পন্ন', 'Cancelled' => 'বাতিল'],
    'msg_status' => ['New' => 'নতুন', 'InProgress' => 'প্রক্রিয়াধীন', 'Resolved' => 'সমাধান হয়েছে'],
    'education' => ['এসএসসি বা সমমান', 'এইচএসসি বা সমমান', 'স্নাতক (সম্মান/পাস)', 'স্নাতকোত্তর', 'অন্যান্য'],
];
