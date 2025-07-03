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
use App\Interfaces\Admin\ReleaseNoteRepositoryInterface;
use App\Interfaces\Admin\RolePermissionRepositoryInterface;
use App\Interfaces\Admin\RoleRepositoryInterface;
use App\Interfaces\Admin\SocialLinkRepositoryInterface;
use App\Interfaces\Admin\TreeEntityRepositoryInterface;
use App\Interfaces\Admin\TutorialCategoryRepositoryInterface;
use App\Interfaces\Admin\TutorialRepositoryInterface;
use App\Interfaces\Admin\UserRepositoryInterface;
use App\Interfaces\Admin\EventRepositoryInterface;
use App\Interfaces\Admin\EventCategoryRepositoryInterface;
use App\Repositories\Admin\AuthRepository;
use App\Repositories\Admin\CountryInfoRepository;
use App\Repositories\Admin\FaqCategoryRepository;
use App\Repositories\Admin\FaqRepository;
use App\Repositories\Admin\LanguageRepository;
use App\Repositories\Admin\NewsCategoryRepository;
use App\Repositories\Admin\NewsRepository;
use App\Repositories\Admin\ReleaseNoteRepository;
use App\Repositories\Admin\RolePermissionRepository;
use App\Repositories\Admin\RoleRepository;
use App\Repositories\Admin\SocialLinkRepository;
use App\Repositories\Admin\TreeEntityRepository;
use App\Repositories\Admin\TutorialCategoryRepository;
use App\Repositories\Admin\TutorialRepository;
use App\Repositories\Admin\UserRepository;
use App\Repositories\Admin\EventRepository;
use App\Repositories\Admin\EventCategoryRepository;
use App\Repositories\Admin\DynamicHeaderRepository;
use App\Interfaces\Admin\DynamicHeaderRepositoryInterface;
use App\Interfaces\Admin\ContactRepositoryInterface;
use App\Repositories\Admin\ContactRepository;
use App\Interfaces\Admin\TeamMemberRepositoryInterface;
use App\Repositories\Admin\TeamMemberRepository;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\Admin\TvChannelRepositoryInterface;
use App\Interfaces\Admin\TvProgramRepositoryInterface;
use App\Interfaces\Admin\MediaContentRepositoryInterface;
use App\Repositories\Admin\TvChannelRepository;
use App\Repositories\Admin\TvProgramRepository;
use App\Repositories\Admin\MediaContentRepository;

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
        $this->app->bind(FaqCategoryRepositoryInterface::class, FaqCategoryRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(LanguageRepositoryInterface::class, LanguageRepository::class);

        $this->app->bind(NewsRepositoryInterface::class, NewsRepository::class);
        $this->app->bind(NewsCategoryRepositoryInterface::class, NewsCategoryRepository::class);

        $this->app->bind(ReleaseNoteRepositoryInterface::class, ReleaseNoteRepository::class);
        $this->app->bind(SocialLinkRepositoryInterface::class, SocialLinkRepository::class);

        $this->app->bind(TutorialCategoryRepositoryInterface::class, TutorialCategoryRepository::class);
        $this->app->bind(TutorialRepositoryInterface::class, TutorialRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        // Event Management Bindings
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventCategoryRepositoryInterface::class, EventCategoryRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(TeamMemberRepositoryInterface::class, TeamMemberRepository::class);
        $this->app->bind(TvChannelRepositoryInterface::class, TvChannelRepository::class);
        $this->app->bind(TvProgramRepositoryInterface::class, TvProgramRepository::class);
        $this->app->bind(MediaContentRepositoryInterface::class, MediaContentRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
