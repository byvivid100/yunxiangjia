<?php

Route::group('api', [
    'checkCode'   => 'api/User/checkCode',
    'sms'   => 'api/Sms/sendSms',
    'test'   => 'api/Sms/test',
])->method('get')->middleware('before');

Route::group('api', [
    'register'   => 'api/User/register',
])->method('post')->middleware(['before', 'after']);