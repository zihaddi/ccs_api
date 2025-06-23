<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\AccessibilityScanController;
use App\Http\Controllers\Api\Cms\AuthClientController;
use App\Http\Controllers\Api\Cms\ComplianceController;
use App\Http\Controllers\Api\Cms\ContactController;
use App\Http\Controllers\Api\Cms\CountryInfoController;
use App\Http\Controllers\Api\Cms\CurrencyController;
use App\Http\Controllers\Api\Cms\CustomerReviewController;
use App\Http\Controllers\Api\Cms\FaqController;
use App\Http\Controllers\Api\Cms\GenderController;
use App\Http\Controllers\Api\Cms\LanguageController;
use App\Http\Controllers\Api\Cms\MetaController;
use App\Http\Controllers\Api\Cms\NewsController;
use App\Http\Controllers\Api\Cms\PageController;
use App\Http\Controllers\Api\Cms\PaymentGatewayController;
use App\Http\Controllers\Api\Cms\PlanController;
use App\Http\Controllers\Api\Cms\PortfolioController;
use App\Http\Controllers\Api\Cms\ReleaseNoteController;
use App\Http\Controllers\Api\Cms\SocialLinkController;
use App\Http\Controllers\Api\Cms\TagController;
use App\Http\Controllers\Api\Cms\TeamMemberController;
use App\Http\Controllers\Api\Cms\TrustedBrandController;
use App\Http\Controllers\Api\Cms\TutorialCategoryController;
use App\Http\Controllers\Api\Cms\TutorialController;
use App\Http\Controllers\Api\Cms\PartnerController;
use App\Http\Controllers\Api\Cms\FeatureController;
use App\Http\Controllers\Api\Cms\BrandController;
use App\Http\Controllers\Api\Cms\EventCategoryController;
use App\Http\Controllers\Api\Cms\EventController;
use App\Http\Controllers\Api\Cms\YearController;
use App\Http\Controllers\Api\Admin\TreeEntityController;
use App\Http\Controllers\Api\Admin\DynamicHeaderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthClientController::class)->group(function () {
    Route::post('/login', 'login')->name('cmsAuth.login');
});


Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])->group(function () {
    Route::post('/refresh-token', [AuthClientController::class, 'refreshToken']);
});


Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {
    Route::post('/me', [AuthClientController::class, 'getUser']);
    Route::controller(ComplianceController::class)->group(function () {
        Route::post('compliances', 'index')->name('compliances.index');
        Route::post('compliances/{id}', 'show')->name('compliances.show');
        Route::post('compliances/{slug}', 'showBySlug')->name('compliances.showBySlug');
    });
    Route::controller(CountryInfoController::class)->group(function () {
        Route::post('countries', 'index')->name('countries.index');
        Route::post('countries/{id}', 'show')->name('countries.show');
        Route::post('countries/{slug}', 'showBySlug')->name('countries.showBySlug');
    });

    Route::controller(CurrencyController::class)->group(function () {
        Route::post('currencies', 'index')->name('currencies.index');
        Route::post('currencies/{id}', 'show')->name('currencies.show');
        Route::post('currencies/{slug}', 'showBySlug')->name('currencies.showBySlug');
    });

    //CustomerReview
    Route::controller(CustomerReviewController::class)->group(function () {
        Route::post('customer-reviews', 'index')->name('customer-reviews.index');
        Route::post('customer-reviews/{id}', 'show')->name('customer-reviews.show');
        Route::post('customer-reviews/{slug}', 'showBySlug')->name('customer-reviews.showBySlug');
    });

    Route::controller(FaqController::class)->group(function () {
        Route::post('faqs', 'index')->name('faqs.index');
        Route::post('faqs/{id}', 'show')->name('faqs.show');
        Route::post('faqs/{slug}', 'showBySlug')->name('faqs.showBySlug');
    });

    Route::controller(GenderController::class)->group(function () {
        Route::post('genders', 'index')->name('genders.index');
        Route::post('genders/{id}', 'show')->name('genders.show');
        Route::post('genders/{slug}', 'showBySlug')->name('genders.showBySlug');
    });

    Route::controller(LanguageController::class)->group(function () {
        Route::post('languages', 'index')->name('languages.index');
        Route::post('languages/{id}', 'show')->name('languages.show');
        Route::post('languages/{slug}', 'showBySlug')->name('languages.showBySlug');
    });

    Route::controller(MetaController::class)->group(function () {
        Route::post('metas', 'index')->name('metas.index');
        Route::post('metas/{id}', 'show')->name('metas.show');
        Route::post('metas/{slug}', 'showBySlug')->name('metas.showBySlug');
    });

    Route::controller(NewsController::class)->group(function () {
        Route::post('news', 'index')->name('news.index');
        Route::post('news/{id}', 'show')->name('news.show');
        Route::post('news/{slug}', 'showBySlug')->name('news.showBySlug');
    });

    Route::controller(PageController::class)->group(function () {
        Route::post('pages', 'index')->name('pages.index');
        Route::post('pages/{id}', 'show')->name('pages.show');
        Route::post('pages/{slug}', 'showBySlug')->name('pages.showBySlug');
    });

    Route::controller(PaymentGatewayController::class)->group(function () {
        Route::post('payment-gateways', 'index')->name('payment-gateways.index');
        Route::post('payment-gateways/{id}', 'show')->name('payment-gateways.show');
        Route::post('payment-gateways/{slug}', 'showBySlug')->name('payment-gateways.showBySlug');
    });

    Route::controller(PlanController::class)->group(function () {
        Route::post('plans', 'index')->name('plans.index');
        Route::post('plans/{id}', 'show')->name('plans.show');
        Route::post('plans/{slug}', 'showBySlug')->name('plans.showBySlug');
    });

    Route::controller(ReleaseNoteController::class)->group(function () {
        Route::post('release-notes', 'index')->name('release-notes.index');
        Route::post('release-notes/{id}', 'show')->name('release-notes.show');
        Route::post('release-notes/{slug}', 'showBySlug')->name('release-notes.showBySlug');
    });

    Route::controller(SocialLinkController::class)->group(function () {
        Route::post('social-links', 'index')->name('social-links.index');
        Route::post('social-links/{id}', 'show')->name('social-links.show');
        Route::post('social-links/{slug}', 'showBySlug')->name('social-links.showBySlug');
    });

    Route::controller(TagController::class)->group(function () {
        Route::post('tags', 'index')->name('tags.index');
        Route::post('tags/{id}', 'show')->name('tags.show');
        Route::post('tags/{slug}', 'showBySlug')->name('tags.showBySlug');
    });

    Route::controller(TrustedBrandController::class)->group(function () {
        Route::post('trusted-brands', 'index')->name('trusted-brands.index');
        Route::post('trusted-brands/{id}', 'show')->name('trusted-brands.show');
        Route::post('trusted-brands/{slug}', 'showBySlug')->name('trusted-brands.showBySlug');
    });

    Route::controller(TutorialController::class)->group(function () {
        Route::post('tutorials', 'index')->name('tutorials.index');
        Route::post('tutorials/{id}', 'show')->name('tutorials.show');
        Route::post('tutorials/{slug}', 'showBySlug')->name('tutorials.showBySlug');
    });

    Route::controller(TutorialCategoryController::class)->group(function () {
        Route::post('tutorial-categories', 'index')->name('tutorial-categories.index');
        Route::post('tutorial-categories/{id}', 'show')->name('tutorial-categories.show')->where('id', '[0-9]+');
        Route::post('tutorial-categories/{slug}', 'showBySlug')->name('tutorial-categories.showBySlug')->where('slug', '[a-zA-Z0-9\-]+');
    });

    Route::controller(PartnerController::class)->group(function () {
        Route::post('partners', 'index')->name('partners.index');
        Route::post('partners/{id}', 'show')->name('partners.show');
        Route::post('partners/{slug}', 'showBySlug')->name('partners.showBySlug');
    });

    Route::controller(FeatureController::class)->group(function () {
        Route::post('features', 'index')->name('features.index');
        Route::post('features/{id}', 'show')->name('features.show');
        Route::post('features/{slug}', 'showBySlug')->name('features.showBySlug');
    });

    Route::controller(BrandController::class)->group(function () {
        Route::post('brands', 'index')->name('brands.index');
        Route::post('brands/{id}', 'show')->name('brands.show');
        Route::post('brands/{slug}', 'showBySlug')->name('brands.showBySlug');
    });

    Route::group(['prefix' => 'year'], function () {
        Route::post('/', [YearController::class, 'index']);
        Route::post('/{id}', [YearController::class, 'show']);
    });

    Route::group(['prefix' => 'event'], function () {
        Route::post('/', [EventController::class, 'index']);
        Route::post('/{id}', [EventController::class, 'show']);
    });

    Route::group(['prefix' => 'event-category'], function () {
        Route::post('/', [EventCategoryController::class, 'index']);
        Route::post('/{id}', [EventCategoryController::class, 'show']);
    });

    Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');

    //protfolio

    Route::controller(PortfolioController::class)->group(function () {
        Route::post('portfolios', 'index')->name('portfolios.index');
        Route::post('portfolios/{id}', 'show')->name('portfolios.show');
        Route::post('portfolios/{slug}', 'showBySlug')->name('portfolios.showBySlug');
    });

    //Team members
    Route::controller(TeamMemberController::class)->group(function () {
        Route::post('team-members', 'index')->name('team-members.index');
        Route::post('team-members/{id}', 'show')->name('team-members.show');
    });


    Route::controller(TreeEntityController::class)->name('tree-entity.')->prefix('tree-entity')->group(function () {
        Route::get('show-menu', 'showmenu')->name('show-menu');

    });

    Route::controller(DynamicHeaderController::class)->name('dynamic-header.')->prefix('dynamic-header')->group(function () {
        Route::get('show-menu', 'showmenu')->name('show-menu');

    });

});
