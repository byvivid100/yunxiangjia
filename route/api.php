<?php

Route::group('api', [
    'loadByCode'   => 'api/User/loadByCode',
])->method('get');

Route::group('api', [
    'findUser'   => 'api/User/findUser',
    'sms'   => 'api/Sms/sendSms',
])->method('get')->middleware('before');

Route::group('api', [
    'register'   => 'api/User/register',
])->method('post')->middleware(['before', 'after']);