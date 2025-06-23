<?php

namespace App\Providers;

use App\Interfaces\Admin\AuthRepositoryInterface;
use App\Interfaces\Admin\PortfolioCategoryRepositoryInterface;
use App\Interfaces\Admin\PortfolioRepositoryInterface;
use App\Repositories\Admin\AuthClientRepository as AdminAuthClientRepository;
use App\Interfaces\Admin\AuthClientRepositoryInterface as AdminAuthClientRepositoryInterface;
use App\Interfaces\Admin\BrandRepositoryInterface;
use App\Interfaces\Admin\ComplianceRepositoryInterface;
use App\Interfaces\Admin\CountryInfoRepositoryInterface;
use App\Interfaces\Admin\CurrencyRepositoryInterface;
use App\Interfaces\Admin\CustomerReviewRepositoryInterface;
use App\Interfaces\Admin\EmailTemplateRepositoryInterface;
use App\Interfaces\Admin\FaqCategoryRepositoryInterface;
use App\Interfaces\Admin\FaqRepositoryInterface;
use App\Interfaces\Admin\GenderRepositoryInterface;
use App\Interfaces\Admin\LanguageRepositoryInterface;
use App\Interfaces\Admin\MetaRepositoryInterface;
use App\Interfaces\Admin\NewsCategoryRepositoryInterface;
use App\Interfaces\Admin\NewsRepositoryInterface;
use App\Interfaces\Admin\PageRepositoryInterface;
use App\Interfaces\Admin\PaymentGatewayRepositoryInterface;
use App\Interfaces\Admin\PlanRepositoryInterface;
use App\Interfaces\Admin\ReleaseNoteRepositoryInterface;
use App\Interfaces\Admin\RolePermissionRepositoryInterface;
use App\Interfaces\Admin\RoleRepositoryInterface;
use App\Interfaces\Admin\SmsTemplateRepositoryInterface;
use App\Interfaces\Admin\SocialLinkRepositoryInterface;
use App\Interfaces\Admin\SubscribeRepositoryInterface;
use App\Interfaces\Admin\TagRepositoryInterface;
use App\Interfaces\Admin\TreeEntityRepositoryInterface;
use App\Interfaces\Admin\TrustedBrandRepositoryInterface;
use App\Interfaces\Admin\TutorialCategoryRepositoryInterface;
use App\Interfaces\Admin\TutorialRepositoryInterface;
use App\Interfaces\Admin\UserRepositoryInterface;
use App\Interfaces\Admin\EventRepositoryInterface;
use App\Interfaces\Admin\EventCategoryRepositoryInterface;
use App\Interfaces\Admin\YearRepositoryInterface;
use App\Repositories\Admin\AuthRepository;
use App\Repositories\Admin\BrandRepository;
use App\Repositories\Admin\CountryInfoRepository;
use App\Repositories\Admin\EmailTemplateRepository;
use App\Repositories\Admin\FaqCategoryRepository;
use App\Repositories\Admin\FaqRepository;
use App\Repositories\Admin\GenderRepository;
use App\Repositories\Admin\LanguageRepository;
use App\Repositories\Admin\MetaRepository;
use App\Repositories\Admin\NewsCategoryRepository;
use App\Repositories\Admin\NewsRepository;
use App\Repositories\Admin\PageRepository;
use App\Repositories\Admin\PaymentGatewayRepository;
use App\Repositories\Admin\PortfolioCategoryRepository;
use App\Repositories\Admin\PortfolioRepository;
use App\Repositories\Admin\ReleaseNoteRepository;
use App\Repositories\Admin\RolePermissionRepository;
use App\Repositories\Admin\RoleRepository;
use App\Repositories\Admin\SmsTemplateRepository;
use App\Repositories\Admin\SocialLinkRepository;
use App\Repositories\Admin\TagRepository;
use App\Repositories\Admin\TreeEntityRepository;
use App\Repositories\Admin\TutorialCategoryRepository;
use App\Repositories\Admin\TutorialRepository;
use App\Repositories\Admin\UserRepository;
use App\Repositories\Admin\EventRepository;
use App\Repositories\Admin\EventCategoryRepository;
use App\Repositories\Admin\YearRepository;
use App\Repositories\Admin\ComplianceRepository;
use App\Repositories\Admin\CurrencyRepository;
use App\Repositories\Admin\CustomerReviewRepository;
use App\Repositories\Admin\PlanRepository;
use App\Repositories\Admin\SubscribeRepository;
use App\Repositories\Admin\TrustedBrandRepository;
use App\Interfaces\Admin\PartnerRepositoryInterface;
use App\Repositories\Admin\PartnerRepository;
use App\Interfaces\Admin\FeatureRepositoryInterface;
use App\Repositories\Admin\FeatureRepository;
use App\Repositories\Admin\DynamicHeaderRepository;
use App\Interfaces\Admin\DynamicHeaderRepositoryInterface;

use App\Interfaces\Admin\ContactRepositoryInterface;
use App\Repositories\Admin\ContactRepository;
use App\Interfaces\Admin\TeamMemberRepositoryInterface;
use App\Models\DynamicHeader;
use App\Repositories\Admin\TeamMemberRepository;
use Illuminate\Support\ServiceProvider;

class AdminRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AdminAuthClientRepositoryInterface::class, AdminAuthClientRepository::class);
        $this->app->bind(TreeEntityRepositoryInterface::class, TreeEntityRepository::class);
        $this->app->bind(DynamicHeaderRepositoryInterface::class, DynamicHeaderRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(RolePermissionRepositoryInterface::class, RolePermissionRepository::class);
        $this->app->bind(CountryInfoRepositoryInterface::class, CountryInfoRepository::class);
        $this->app->bind(EmailTemplateRepositoryInterface::class, EmailTemplateRepository::class);
        $this->app->bind(SmsTemplateRepositoryInterface::class, SmsTemplateRepository::class);
        $this->app->bind(FaqCategoryRepositoryInterface::class, FaqCategoryRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(GenderRepositoryInterface::class, GenderRepository::class);
        $this->app->bind(LanguageRepositoryInterface::class, LanguageRepository::class);
        $this->app->bind(MetaRepositoryInterface::class, MetaRepository::class);
        $this->app->bind(NewsRepositoryInterface::class, NewsRepository::class);
        $this->app->bind(NewsCategoryRepositoryInterface::class, NewsCategoryRepository::class);
        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);
        $this->app->bind(PaymentGatewayRepositoryInterface::class, PaymentGatewayRepository::class);
        $this->app->bind(ReleaseNoteRepositoryInterface::class, ReleaseNoteRepository::class);
        $this->app->bind(SocialLinkRepositoryInterface::class, SocialLinkRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(TutorialCategoryRepositoryInterface::class, TutorialCategoryRepository::class);
        $this->app->bind(TutorialRepositoryInterface::class, TutorialRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);

        $this->app->bind(ComplianceRepositoryInterface::class, ComplianceRepository::class);
        $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
        $this->app->bind(CustomerReviewRepositoryInterface::class, CustomerReviewRepository::class);
        $this->app->bind(PlanRepositoryInterface::class, PlanRepository::class);
        $this->app->bind(SubscribeRepositoryInterface::class, SubscribeRepository::class);
        $this->app->bind(TrustedBrandRepositoryInterface::class, TrustedBrandRepository::class);

        // Event Management Bindings
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventCategoryRepositoryInterface::class, EventCategoryRepository::class);
        $this->app->bind(YearRepositoryInterface::class, YearRepository::class);

        // Partner and Feature Bindings
        $this->app->bind(PartnerRepositoryInterface::class, PartnerRepository::class);
        $this->app->bind(FeatureRepositoryInterface::class, FeatureRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);

        $this->app->bind(PortfolioCategoryRepositoryInterface::class, PortfolioCategoryRepository::class);
        $this->app->bind(PortfolioRepositoryInterface::class, PortfolioRepository::class);
        $this->app->bind(TeamMemberRepositoryInterface::class, TeamMemberRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
