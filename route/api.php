<?php

Route::rule('api/resetAccessToken/:sign','api/Index/resetAccessToken', 'GET');

Route::rule('api/payment/notify','api/Payment/notify', 'POST');

Route::group('api', [
    'initByCode'   => 'api/Index/initByCode',
])->method('get')->middleware(['sign']);

Route::group('api', [
	'account/detail'   => 'api/Account/detail',
	'account/recordlist'   => 'api/Account/recordlist',

	'apply/detail'   => 'api/Apply/detail',
	'apply/userlist'   => 'api/Apply/userlist',
	'apply/agentlist'   => 'api/Apply/agentlist',

	'order/detail'   => 'api/Order/detail',
	'order/buylist'   => 'api/Order/buylist',
	'order/selllist'   => 'api/Order/selllist',

	'payment/paymentlist'   => 'api/Payment/paymentlist',

	'transfers/transferslist'   => 'api/Transfers/transferslist',

    'findUser'   => 'api/User/findUser',
    'sms'   => 'api/Sms/sendSms',
    'checkSms'   => 'api/Sms/checkCode',
])->method('get')->middleware(['sign', 'before']);

Route::group('api', [
	'apply/checkApply'   => 'api/Apply/checkApply',
	'apply/payAdvByUser'   => 'api/Apply/payAdvByUser',
	'apply/succByUser'   => 'api/Apply/succByUser',
	'apply/succByAgent'   => 'api/Apply/succByAgent',
	'apply/failByUser'   => 'api/Apply/failByUser',
	'apply/failByAgent'   => 'api/Apply/failByAgent',

	'order/checkOrder'   => 'api/Order/checkOrder',
	'order/succByBuy'   => 'api/Order/succByBuy',
	'order/succBySell'   => 'api/Order/succBySell',
	'order/failByBuy'   => 'api/Order/failByBuy',
	'order/failBySell'   => 'api/Order/failBySell',

	'propety/insertPropety'   => 'api/Propety/insertPropety',
	'propety/updatePropety'   => 'api/Propety/updatePropety',

	'service/insertService'   => 'api/Service/insertService',
	'service/updateService'   => 'api/Service/updateService',

	'transfers/trans'   => 'api/Transfers/trans',
	'transfers/cancel'   => 'api/Transfers/cancel',

    'register'   => 'api/User/register',
    'applyAgent/apply'=>'api/ApplyAgent/apply'
])->method('post')->middleware(['sign', 'before']);
