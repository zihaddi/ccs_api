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
use App\Http\Controllers\Api\Cms\TvChannelController;
use App\Http\Controllers\Api\Cms\TvProgramController;
use App\Http\Controllers\Api\Cms\MediaContentController;
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

    Route::controller(CountryInfoController::class)->group(function () {
        Route::post('countries', 'index')->name('countries.index');
        Route::post('countries/{id}', 'show')->name('countries.show');
        Route::post('countries/{slug}', 'showBySlug')->name('countries.showBySlug');
    });





    Route::controller(FaqController::class)->group(function () {
        Route::post('faqs', 'index')->name('faqs.index');
        Route::post('faqs/{id}', 'show')->name('faqs.show');
        Route::post('faqs/{slug}', 'showBySlug')->name('faqs.showBySlug');
    });



    Route::controller(LanguageController::class)->group(function () {
        Route::post('languages', 'index')->name('languages.index');
        Route::post('languages/{id}', 'show')->name('languages.show');
        Route::post('languages/{slug}', 'showBySlug')->name('languages.showBySlug');
    });



    Route::controller(NewsController::class)->group(function () {
        Route::post('news', 'index')->name('news.index');
        Route::post('news/{id}', 'show')->name('news.show');
        Route::post('news/{slug}', 'showBySlug')->name('news.showBySlug');
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


    //TV Channels
    Route::controller(TvChannelController::class)->group(function () {
        Route::post('tv-channels', 'index')->name('tv-channels.index');
        Route::post('tv-channels/{id}', 'show')->name('tv-channels.show')->where('id', '[0-9]+');
        Route::post('tv-channels/{slug}', 'showBySlug')->name('tv-channels.showBySlug')->where('slug', '[a-zA-Z0-9\-]+');
    });

    //TV Programs
    Route::controller(TvProgramController::class)->group(function () {
        Route::post('tv-programs', 'index')->name('tv-programs.index');
        Route::post('tv-programs/{id}', 'show')->name('tv-programs.show')->where('id', '[0-9]+');
        Route::post('tv-programs/{slug}', 'showBySlug')->name('tv-programs.showBySlug')->where('slug', '[a-zA-Z0-9\-]+');
        Route::post('tv-programs/channel/{channelId}', 'getByChannel')->name('tv-programs.by-channel');
        Route::post('tv-programs/today', 'getToday')->name('tv-programs.today');
        Route::post('tv-programs/type/{type}', 'getByType')->name('tv-programs.by-type');
    });

    //Media Contents
    Route::controller(MediaContentController::class)->group(function () {
        Route::post('media-contents', 'index')->name('media-contents.index');
        Route::post('media-contents/{id}', 'show')->name('media-contents.show')->where('id', '[0-9]+');
        Route::post('media-contents/slug/{slug}', 'showBySlug')->name('media-contents.show-by-slug')->where('slug', '[a-zA-Z0-9\-_]+');
        Route::post('media-contents/featured', 'getFeatured')->name('media-contents.featured');
        Route::post('media-contents/type/{contentType}', 'getByType')->name('media-contents.by-type');
        Route::post('media-contents/channel/{channelId}', 'getByChannel')->name('media-contents.by-channel');
        Route::post('media-contents/popular', 'getPopular')->name('media-contents.popular');
        Route::post('media-contents/recent', 'getRecent')->name('media-contents.recent');
        Route::post('media-contents/search/{searchTerm}', 'search')->name('media-contents.search');
        Route::post('media-contents/news-category/{newsCategory}', 'getByNewsCategory')->name('media-contents.by-news-category');
    });

});
