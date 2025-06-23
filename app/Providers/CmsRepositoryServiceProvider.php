<?php

namespace App\Providers;

use App\Interfaces\Cms\ContactRepositoryInterface;
use App\Interfaces\Cms\PortfolioCategoryRepositoryInterface;
use App\Interfaces\Cms\PortfolioRepositoryInterface;
use App\Repositories\Cms\PortfolioCategoryRepository;
use App\Repositories\Cms\ContactRepository;
use App\Repositories\Cms\PortfolioRepository;
use Illuminate\Support\ServiceProvider;

use App\Repositories\Cms\AuthClientRepository;
use App\Interfaces\Cms\AuthClientRepositoryInterface;
use App\Interfaces\Cms\ComplianceRepositoryInterface;
use App\Interfaces\Cms\CountryInfoRepositoryInterface;
use App\Interfaces\Cms\CurrencyRepositoryInterface;
use App\Interfaces\Cms\CustomerReviewRepositoryInterface;
use App\Interfaces\Cms\FaqRepositoryInterface;
use App\Interfaces\Cms\GenderRepositoryInterface;
use App\Interfaces\Cms\LanguageRepositoryInterface;
use App\Interfaces\Cms\MetaRepositoryInterface;
use App\Interfaces\Cms\NewsRepositoryInterface;
use App\Interfaces\Cms\PaymentGatewayRepositoryInterface;
use App\Interfaces\Cms\PlanRepositoryInterface;
use App\Interfaces\Cms\ReleaseNoteRepositoryInterface;
use App\Interfaces\Cms\SocialLinkRepositoryInterface;
use App\Interfaces\Cms\TagRepositoryInterface;
use App\Interfaces\Cms\TrustedBrandRepositoryInterface;
use App\Interfaces\Cms\TutorialCategoryRepositoryInterface;
use App\Interfaces\Cms\TutorialRepositoryInterface;
use App\Interfaces\Cms\EventRepositoryInterface;
use App\Interfaces\Cms\EventCategoryRepositoryInterface;
use App\Interfaces\Cms\PartnerRepositoryInterface;
use App\Interfaces\Cms\FeatureRepositoryInterface;
use App\Interfaces\Cms\BrandRepositoryInterface;
use App\Interfaces\Cms\YearRepositoryInterface;
use App\Repositories\Cms\TutorialCategoryRepository;
use App\Repositories\Cms\ComplianceRepository;
use App\Repositories\Cms\CountryInfoRepository;
use App\Repositories\Cms\CurrencyRepository;
use App\Repositories\Cms\CustomerReviewRepository;
use App\Repositories\Cms\FaqRepository;
use App\Repositories\Cms\GenderRepository;
use App\Repositories\Cms\LanguageRepository;
use App\Repositories\Cms\MetaRepository;
use App\Repositories\Cms\NewsRepository;
use App\Repositories\Cms\PaymentGatewayRepository;
use App\Repositories\Cms\PlanRepository;
use App\Repositories\Cms\ReleaseNoteRepository;
use App\Repositories\Cms\SocialLinkRepository;
use App\Repositories\Cms\TagRepository;
use App\Repositories\Cms\TrustedBrandRepository;
use App\Repositories\Cms\TutorialRepository;
use App\Repositories\Cms\EventRepository;
use App\Repositories\Cms\EventCategoryRepository;
use App\Repositories\Cms\PartnerRepository;
use App\Repositories\Cms\FeatureRepository;
use App\Repositories\Cms\BrandRepository;
use App\Repositories\Cms\YearRepository;

class CmsRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthClientRepositoryInterface::class, AuthClientRepository::class);
        $this->app->bind(ComplianceRepositoryInterface::class, ComplianceRepository::class);
        $this->app->bind(CountryInfoRepositoryInterface::class, CountryInfoRepository::class);
        $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
        $this->app->bind(CustomerReviewRepositoryInterface::class, CustomerReviewRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(GenderRepositoryInterface::class, GenderRepository::class);
        $this->app->bind(LanguageRepositoryInterface::class, LanguageRepository::class);
        $this->app->bind(MetaRepositoryInterface::class, MetaRepository::class);
        $this->app->bind(NewsRepositoryInterface::class, NewsRepository::class);
        $this->app->bind(PaymentGatewayRepositoryInterface::class, PaymentGatewayRepository::class);
        $this->app->bind(PlanRepositoryInterface::class, PlanRepository::class);
        $this->app->bind(ReleaseNoteRepositoryInterface::class, ReleaseNoteRepository::class);
        $this->app->bind(SocialLinkRepositoryInterface::class, SocialLinkRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(TrustedBrandRepositoryInterface::class, TrustedBrandRepository::class);
        $this->app->bind(TutorialRepositoryInterface::class, TutorialRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventCategoryRepositoryInterface::class, EventCategoryRepository::class);
        $this->app->bind(PartnerRepositoryInterface::class, PartnerRepository::class);
        $this->app->bind(FeatureRepositoryInterface::class, FeatureRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(YearRepositoryInterface::class, YearRepository::class);
        $this->app->bind(TutorialCategoryRepositoryInterface::class, TutorialCategoryRepository::class);

        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);

        $this->app->bind(PortfolioCategoryRepositoryInterface::class, PortfolioCategoryRepository::class);
        $this->app->bind(PortfolioRepositoryInterface::class, PortfolioRepository::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
