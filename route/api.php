<?php

Route::group('api', [
    'checkCode'   => 'api/User/checkCode',
])->method('get')->middleware('before');

Route::group('api', [
    'register'   => 'api/User/register',
])->method('post')->middleware(['before', 'after']);