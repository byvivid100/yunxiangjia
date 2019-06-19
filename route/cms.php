<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/19
 * Time: 14:39
 */

Route::group('cms', [
    '/'   => 'cms/index/index',
])->method('get');