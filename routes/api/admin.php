<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\Admin\AuthClientController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\ComplianceController;
use App\Http\Controllers\Api\Admin\ContactController;
use App\Http\Controllers\Api\Admin\CountryInfoController;
use App\Http\Controllers\Api\Admin\CurrencyController;
use App\Http\Controllers\Api\Admin\CustomerReviewController;
use App\Http\Controllers\Api\Admin\EmailTemplateController;
use App\Http\Controllers\Api\Admin\EventCategoryController;
use App\Http\Controllers\Api\Admin\EventController;
use App\Http\Controllers\Api\Admin\FaqCategoryController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\GenderController;
use App\Http\Controllers\Api\Admin\LanguageController;
use App\Http\Controllers\Api\Admin\MetaController;
use App\Http\Controllers\Api\Admin\NewsCategoryController;
use App\Http\Controllers\Api\Admin\NewsController;
use App\Http\Controllers\Api\Admin\PageController;
use App\Http\Controllers\Api\Admin\PaymentGatewayController;
use App\Http\Controllers\Api\Admin\PlanController;
use App\Http\Controllers\Api\Admin\PortfolioCategoryController;
use App\Http\Controllers\Api\Admin\PortfolioController;
use App\Http\Controllers\Api\Admin\ReleaseNoteController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\RolePermissionController;
use App\Http\Controllers\Api\Admin\SmsTemplateController;
use App\Http\Controllers\Api\Admin\SocialLinkController;
use App\Http\Controllers\Api\Admin\SubscribeController;
use App\Http\Controllers\Api\Admin\TagController;
use App\Http\Controllers\Api\Admin\TeamMemberController;
use App\Http\Controllers\Api\Admin\TreeEntityController;
use App\Http\Controllers\Api\Admin\TrustedBrandController;
use App\Http\Controllers\Api\Admin\TutorialCategoryController;
use App\Http\Controllers\Api\Admin\TutorialController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\PartnerController;
use App\Http\Controllers\Api\Admin\FeatureController;
use App\Http\Controllers\Api\Admin\YearController;
use App\Http\Controllers\Api\Admin\DynamicHeaderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PharIo\Manifest\Email;

//Auth
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login')->name('adminAuth.login');
    Route::post('/otp-resend', 'reqOtpResend')->name('adminAuth.otp_resend');
    Route::post('/otp-verify', 'reqOtpVerify')->name('adminAuth.otp_verify');
    Route::post('/set-password', 'setNewPassword')->name('adminAuth.set_password');
    Route::post('/forgot-password', 'forgotPassword')->name('adminAuth.forgotPassword');
});

//Use Refresh Token
Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])->group(function () {
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
});

//Use Access Token
Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {
    // Auth
    Route::controller(AuthController::class)->group(function () {
        Route::post('/user', 'getUser')->name('adminAuth.getUser');
        Route::post('/logout', 'logout')->name('adminAuth.logout');
    });
    // Tree Entity
    Route::controller(TreeEntityController::class)->name('tree-entity.')->prefix('tree-entity')->group(function () {
        Route::get('build-menu', 'buildmenu')->name('build-menu');
        Route::post('main-menu', 'treemenuNew')->name('tree-menu');
        Route::post('update-menu', 'updateMenu')->name('update-menu');
        Route::post('delete-menu', 'deleteMenu')->name('delete-menu');
        Route::post('restore/{id}', 'restore')->name('restore');
    });

    Route::apiResource('tree-entity', TreeEntityController::class);
    Route::apiResource('auth-client', AuthClientController::class);
    Route::controller(AuthClientController::class)->group(function () {
        Route::post('auth-client/all', 'index')->name('auth-client.all');
        Route::post('auth-client/restore/{id}', 'restore')->name('tree-entity.restore');
    });

    // Roles
    Route::apiResource('roles', RoleController::class);
    Route::controller(RoleController::class)->group(function () {
        Route::post('roles/all', 'index')->name('roles.all');
        Route::post('roles/restore/{id}', 'restore')->name('roles.restore');
    });

    //use when required
    //->middleware([
    //     'index' => 'check.permission:view',
    //     'store' => 'check.permission:add',
    //     'update' => 'check.permission:edit',
    //     'destroy' => 'check.permission:delete',
    // ])

    // Role Permissions
    Route::controller(RolePermissionController::class)->group(function () {
        Route::post('role-permissions/show/{id}', 'show')->name('roles.show');
        Route::post('role-permissions/permission-update/{id}', 'pupdate')->name('roles.permission-update');
    });


    // Country
    Route::apiResource('countries', CountryInfoController::class);
    Route::controller(CountryInfoController::class)->group(function () {
        Route::post('countries/all', 'index')->name('countries.all');
        Route::post('countries/restore/{id}', 'restore')->name('countries.restore');
    });

    // Email Templates
    Route::apiResource('email-templates', EmailTemplateController::class);
    Route::controller(EmailTemplateController::class)->group(function () {
        Route::post('email-templates/all', 'index')->name('email-templates.all');
        Route::post('email-templates/restore/{id}', 'restore')->name('email-templates.restore');
    });

    // SMS Templates
    Route::apiResource('sms-templates', SmsTemplateController::class);
    Route::controller(SmsTemplateController::class)->group(function () {
        Route::post('sms-templates/all', 'index')->name('sms-templates.all');
        Route::post('sms-templates/restore/{id}', 'restore')->name('sms-templates.restore');
    });

    //Faq Categories
    Route::apiResource('faq-categories', FaqCategoryController::class);
    Route::controller(FaqCategoryController::class)->group(function () {
        Route::post('faq-categories/all', 'index')->name('faq-categories.all');
        Route::post('faq-categories/restore/{id}', 'restore')->name('faq-categories.restore');
    });

    //Faq
    Route::apiResource('faqs', FaqController::class);
    Route::controller(FaqController::class)->group(function () {
        Route::post('faqs/all', 'index')->name('faqs.all');
        Route::post('faqs/restore/{id}', 'restore')->name('faqs.restore');
    });

    //Gender
    Route::apiResource('genders', GenderController::class);
    Route::controller(GenderController::class)->group(function () {
        Route::post('genders/all', 'index')->name('genders.all');
        Route::post('genders/restore/{id}', 'restore')->name('genders.restore');
    });

    //Language
    Route::apiResource('languages', LanguageController::class);
    Route::controller(LanguageController::class)->group(function () {
        Route::post('languages/all', 'index')->name('languages.all');
        Route::post('languages/restore/{id}', 'restore')->name('languages.restore');
    });

    //Meta
    Route::apiResource('metas', MetaController::class);
    Route::controller(MetaController::class)->group(function () {
        Route::post('metas/all', 'index')->name('metas.all');
        Route::post('metas/restore/{id}', 'restore')->name('metas.restore');
    });

    //News Categories
    Route::apiResource('news-categories', NewsCategoryController::class);
    Route::controller(NewsCategoryController::class)->group(function () {
        Route::post('news-categories/all', 'index')->name('news-categories.all');
        Route::post('news-categories/restore/{id}', 'restore')->name('news-categories.restore');
    });

    //News
    Route::apiResource('news', NewsController::class);
    Route::controller(NewsController::class)->group(function () {
        Route::post('news/all', 'index')->name('news.all');
        Route::post('news/restore/{id}', 'restore')->name('news.restore');
    });

    //Pages
    Route::apiResource('pages', PageController::class);
    Route::controller(PageController::class)->group(function () {
        Route::post('pages/all', 'index')->name('pages.all');
        Route::post('pages/restore/{id}', 'restore')->name('pages.restore');
    });

    //Payment Gateway
    Route::apiResource('payment-gateways', PaymentGatewayController::class);
    Route::controller(PaymentGatewayController::class)->group(function () {
        Route::post('payment-gateways/all', 'index')->name('payment-gateways.all');
        Route::post('payment-gateways/restore/{id}', 'restore')->name('payment-gateways.restore');
    });


    //Release Notes
    Route::apiResource('release-notes', ReleaseNoteController::class);
    Route::controller(ReleaseNoteController::class)->group(function () {
        Route::post('release-notes/all', 'index')->name('release-notes.all');
        Route::post('release-notes/restore/{id}', 'restore')->name('release-notes.restore');
    });

    //Social Links
    Route::apiResource('social-links', SocialLinkController::class);
    Route::controller(SocialLinkController::class)->group(function () {
        Route::post('social-links/all', 'index')->name('social-links.all');
        Route::post('social-links/restore/{id}', 'restore')->name('social-links.restore');
    });

    //Tags
    Route::apiResource('tags', TagController::class);
    Route::controller(TagController::class)->group(function () {
        Route::post('tags/all', 'index')->name('tags.all');
        Route::post('tags/restore/{id}', 'restore')->name('tags.restore');
    });

    //Tutorial Categories
    Route::apiResource('tutorial-categories', TutorialCategoryController::class);
    Route::controller(TutorialCategoryController::class)->group(function () {
        Route::post('tutorial-categories/all', 'index')->name('tutorial-categories.all');
        Route::post('tutorial-categories/restore/{id}', 'restore')->name('tutorial-categories.restore');
    });

    //Tutorials
    Route::apiResource('tutorials', TutorialController::class);
    Route::controller(TutorialController::class)->group(function () {
        Route::post('tutorials/all', 'index')->name('tutorials.all');
        Route::post('tutorials/restore/{id}', 'restore')->name('tutorials.restore');
    });

    //Users
    Route::apiResource('users', UserController::class);
    Route::controller(UserController::class)->group(function () {
        Route::post('users/all', 'index')->name('users.all');
        Route::post('users/restore/{id}', 'restore')->name('users.restore');
    });

    //Compliance
    Route::apiResource('compliances', ComplianceController::class);
    Route::controller(ComplianceController::class)->group(function () {
        Route::post('compliances/all', 'index')->name('compliances.all');
        Route::post('compliances/restore/{id}', 'restore')->name('compliances.restore');
    });

    //Currency
    Route::apiResource('currencies', CurrencyController::class);
    Route::controller(CurrencyController::class)->group(function () {
        Route::post('currencies/all', 'index')->name('currencies.all');
        Route::post('currencies/restore/{id}', 'restore')->name('currencies.restore');
    });

    //CustomerReview
    Route::apiResource('customer-reviews', CustomerReviewController::class);
    Route::controller(CustomerReviewController::class)->group(function () {
        Route::post('customer-reviews/all', 'index')->name('customer-reviews.all');
        Route::post('customer-reviews/restore/{id}', 'restore')->name('customer-reviews.restore');
    });

    //Plan
    Route::apiResource('plans', PlanController::class);
    Route::controller(PlanController::class)->group(function () {
        Route::post('plans/all', 'index')->name('plans.all');
        Route::post('plans/restore/{id}', 'restore')->name('plans.restore');
    });

    //Subscribe
    Route::apiResource('subscribes', SubscribeController::class);
    Route::controller(SubscribeController::class)->group(function () {
        Route::post('subscribes/all', 'index')->name('subscribes.all');
        Route::post('subscribes/restore/{id}', 'restore')->name('subscribes.restore');
    });

    //TrustedBrand
    Route::apiResource('trusted-brands', TrustedBrandController::class);
    Route::controller(TrustedBrandController::class)->group(function () {
        Route::post('trusted-brands/all', 'index')->name('trusted-brands.all');
        Route::post('trusted-brands/restore/{id}', 'restore')->name('trusted-brands.restore');
    });

    //Brands
    Route::apiResource('brands', BrandController::class);
    Route::controller(BrandController::class)->group(function () {
        Route::post('brands/all', 'index')->name('brands.all');
        Route::post('brands/restore/{id}', 'restore')->name('brands.restore');
    });

    //Partners
    Route::apiResource('partners', PartnerController::class);
    Route::controller(PartnerController::class)->group(function () {
        Route::post('partners/all', 'index')->name('partners.all');
        Route::post('partners/restore/{id}', 'restore')->name('partners.restore');
    });

    //Features
    Route::apiResource('features', FeatureController::class);
    Route::controller(FeatureController::class)->group(function () {
        Route::post('features/all', 'index')->name('features.all');
        Route::post('features/restore/{id}', 'restore')->name('features.restore');
    });

    //Event Routes
    Route::apiResource('events', EventController::class);
    Route::controller(EventController::class)->group(function () {
        Route::post('events/all', 'index')->name('events.all');
        Route::post('events/restore/{id}', 'restore')->name('events.restore');
    });
    //Event Category Routes
    Route::apiResource('event-categories', EventCategoryController::class);
    Route::controller(EventCategoryController::class)->group(function () {
        Route::post('event-categories/all', 'index')->name('event-categories.all');
        Route::post('event-categories/restore/{id}', 'restore')->name('event-categories.restore');
    });
    //Year Routes
    Route::apiResource('years', YearController::class);
    Route::controller(YearController::class)->group(function () {
        Route::post('years/all', 'index')->name('years.all');
        Route::post('years/restore/{id}', 'restore')->name('years.restore');
    });

    Route::apiResource('contacts', ContactController::class)->only(['index', 'show', 'destroy']);
    Route::controller(ContactController::class)->group(function () {
        Route::post('contacts/all', 'index')->name('contacts.all');
        Route::post('contacts/restore/{id}', 'restore')->name('contacts.restore');
    });

    //Protfolio Category
    Route::apiResource('portfolio-categories', PortfolioCategoryController::class);
    Route::controller(PortfolioCategoryController::class)->group(function () {
        Route::post('portfolio-categories/all', 'index')->name('portfolio-categories.all');
        Route::post('portfolio-categories/restore/{id}', 'restore')->name('portfolio-categories.restore');
    });

    //Portfolio
    Route::apiResource('portfolios', PortfolioController::class);
    Route::controller(PortfolioController::class)->group(function () {
        Route::post('portfolios/all', 'index')->name('portfolios.all');
        Route::post('portfolios/restore/{id}', 'restore')->name('portfolios.restore');
    });

    //Team members
    Route::apiResource('team-members', TeamMemberController::class);
    Route::controller(TeamMemberController::class)->group(function () {
        Route::post('team-members/all', 'index')->name('team-members.all');
        Route::post('team-members/restore/{id}', 'restore')->name('team-members.restore');
    });

    //Dynamic Header
    Route::controller(DynamicHeaderController::class)->name('dynamic-header.')->prefix('dynamic-header')->group(function () {
        Route::get('build-menu', 'buildmenu')->name('build-menu');
        Route::post('main-menu', 'treemenuNew')->name('tree-menu');
        Route::post('update-menu', 'updateMenu')->name('update-menu');
        Route::post('delete-menu', 'deleteMenu')->name('delete-menu');
        Route::post('restore/{id}', 'restore')->name('restore');
    });

    Route::apiResource('dynamic-header', DynamicHeaderController::class);
});
