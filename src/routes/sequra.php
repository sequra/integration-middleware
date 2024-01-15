<?php

use Illuminate\Support\Facades\Route;
use SeQura\Middleware\Http\Controllers\CountrySettingsController;
use SeQura\Middleware\Http\Controllers\DisconnectController;
use SeQura\Middleware\Http\Controllers\GeneralSettingsController;
use SeQura\Middleware\Http\Controllers\IntegrationController;
use SeQura\Middleware\Http\Controllers\OnboardingController;
use SeQura\Middleware\Http\Controllers\OrderStatusSettingsController;
use SeQura\Middleware\Http\Controllers\PaymentMethodsController;
use SeQura\Middleware\Http\Controllers\StoresController;
use SeQura\Middleware\Http\Controllers\TransactionLogController;
use SeQura\Middleware\Http\Controllers\WidgetSettingsController;
use SeQura\Middleware\Http\Middleware\Cors;
use SeQura\Middleware\Http\Middleware\InitializeAdminContext;
use SeQura\Middleware\Http\Middleware\ValidateAdminRequest;

Route::post(
    'sequra/async/asyncprocess/guid/{guid}',
    'SeQura\Middleware\Http\Controllers\AsyncProcessController@run'
)->name('async');

Route::get(
    'healthz',
    'SeQura\Middleware\Http\Controllers\HealthCheckController@check'
)->name('healthz');

Route::prefix('sequra')->name('sequra')->group(static function () {
    Route::group([
        'prefix' => '/admin',
        'as' => '.admin',
        'middleware' => [
            Cors::class,
            ValidateAdminRequest::class,
            InitializeAdminContext::class,
        ]
    ], static function () {
        Route::post('disconnect', [DisconnectController::class, 'disconnect'])->name('.disconnect');
        Route::get('countries', [CountrySettingsController::class, 'getSellingCountries'])->name('.countries.get');
        Route::get('countries/settings', [CountrySettingsController::class, 'getCountrySettings'])->name('.countries.settings.get');
        Route::post('countries/settings', [CountrySettingsController::class, 'setCountrySettings'])->name('.countries.settings.set');
        Route::get('order-statuses', [OrderStatusSettingsController::class, 'getShopOrderStatuses'])->name('.order-statuses.get');
        Route::get('order-statuses/settings', [OrderStatusSettingsController::class, 'getOrderStatusSettings'])->name('.order-statuses.settings.get');
        Route::post('order-statuses/settings', [OrderStatusSettingsController::class, 'setOrderStatusSettings'])->name('.order-statuses.settings.set');
        Route::get('general-settings', [GeneralSettingsController::class, 'getGeneralSettings'])->name('.general-settings.get');
        Route::post('general-settings', [GeneralSettingsController::class, 'setGeneralSettings'])->name('.general-settings.set');
        Route::get('general-settings/categories', [GeneralSettingsController::class, 'getShopCategories'])->name('.general-settings.categories');
        Route::get('general-settings/payment-methods', [GeneralSettingsController::class, 'getShopPaymentMethods'])->name('.general-settings.payment-methods');
        Route::get('integration/version', [IntegrationController::class, 'getVersion'])->name('.integration.version');
        Route::get('integration/state', [IntegrationController::class, 'getState'])->name('.integration.state');
        Route::get('integration/shop-name', [IntegrationController::class, 'getShopName'])->name('.integration.shop-name');
        Route::get('connection', [OnboardingController::class, 'getConnectionData'])->name('.connection.get');
        Route::post('connection', [OnboardingController::class, 'setConnectionData'])->name('.connection.set');
        Route::post('connection/validate', [OnboardingController::class, 'validateConnectionData'])->name('.connection.validate');
        Route::get('payment-methods', [PaymentMethodsController::class, 'getPaymentMethods'])->name('.payment-methods');
        Route::get('stores', [StoresController::class, 'getStores'])->name('.stores');
        Route::get('store', [StoresController::class, 'getCurrentStore'])->name('.store');
        Route::get('widget', [WidgetSettingsController::class, 'getWidgetSettings'])->name('.widget.get');
        Route::post('widget', [WidgetSettingsController::class, 'setWidgetSettings'])->name('.widget.set');
        Route::get('transaction-logs', [TransactionLogController::class, 'getTransactionLogs'])->name('.transaction-logs');
    });
});
