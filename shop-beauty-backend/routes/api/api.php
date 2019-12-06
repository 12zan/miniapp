<?php


Route::post('auth/login', 'Auth\AuthController@login');

Route::middleware(['jwt.auth'])->group(function () {
    Route::post('save-formid', 'HomeController@saveFormId');
    Route::post('fix-info', 'Auth\AuthController@fixInfo');
    Route::post('fix-phone', 'Auth\AuthController@fixPhone');

    Route::get('preload', 'HomeController@preload');
    Route::get('qr-info', 'HomeController@qrInfo');

    Route::get('staffs', 'StaffController@index');
    Route::get('staffs-mini', 'StaffController@miniIndex');
    Route::get('staffs/{id}', 'StaffController@show');

    //扫描扣款码
    Route::get('staff/scan-code', 'StaffController@scanQrcode');
    Route::post('staff/receipt', 'StaffController@receiptByQrcode');
    //预览核销页面
    Route::get('staff/pre-scan-exchange-code', 'StaffController@preScanExchangeCode');
    Route::post('staff/sure-exchange-code', 'StaffController@surePreScanExchangeCode');

    Route::get('items/{id}', 'ServerItemController@show');
    Route::get('items', 'ServerItemController@index');

    Route::get('staff-order', 'StaffOrderController@index');
    Route::get('staff-order/{id}', 'StaffOrderController@show');
    Route::post('staff-order', 'StaffOrderController@store');

    Route::post('orders', 'OrderController@store');
    Route::post('orders/store-value', 'OrderController@storeValue');
    Route::get('orders', 'OrderController@index');
    Route::get('orders/{id}', 'OrderController@show');
    Route::get('orders/{id}/find', 'OrderController@find');

    Route::get('member/money-logs', 'MemberController@moneyLogsIndex');
    Route::get('member/money-logs/{id}', 'MemberController@moneyLogsShow');
    Route::get('member', 'MemberController@show');

    Route::get('member/pay-qrcode', 'MemberController@payQrCode');

    Route::post('pay/wx', 'PayController@wxPay');
    Route::post('pay/store', 'PayController@storePay');

    //活动相关
    Route::get('activity/store-value', 'ActivityController@index');

    //扫码添加员工
    Route::post('staff/by-scan', 'StaffController@store');

});

Route::middleware(['check.appid'])->group(function () {
    Route::get('home/show', 'HomeController@show');
});

    Route::post('pay/notify', 'PayController@notify');
    Route::post('test', 'HomeController@test');