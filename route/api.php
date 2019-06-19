<?php

Route::get('api/resetAccessToken/:sign','api/Index/resetAccessToken');

Route::group('api', [
    'initByCode'   => 'api/Index/initByCode',
])->method('get')->middleware(['sign']);

Route::group('api', [
    'findUser'   => 'api/User/findUser',
    'sms'   => 'api/Sms/sendSms',
    'checkSms'   => 'api/Sms/checkCode',
])->method('get')->middleware(['sign', 'before']);

Route::group('api', [
    'register'   => 'api/User/register',
    'applyAgent/apply'=>'api/ApplyAgent/apply'
])->method('post')->middleware(['sign', 'before', 'after']);