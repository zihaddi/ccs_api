<?php

namespace App\Providers;

use App\Interfaces\Cms\ContactRepositoryInterface;
use App\Repositories\Cms\ContactRepository;
use Illuminate\Support\ServiceProvider;

use App\Repositories\Cms\AuthClientRepository;
use App\Interfaces\Cms\AuthClientRepositoryInterface;
use App\Interfaces\Cms\CountryInfoRepositoryInterface;
use App\Interfaces\Cms\FaqRepositoryInterface;
use App\Interfaces\Cms\LanguageRepositoryInterface;
use App\Interfaces\Cms\NewsRepositoryInterface;
use App\Interfaces\Cms\ReleaseNoteRepositoryInterface;
use App\Interfaces\Cms\SocialLinkRepositoryInterface;
use App\Interfaces\Cms\TutorialCategoryRepositoryInterface;
use App\Interfaces\Cms\TutorialRepositoryInterface;
use App\Interfaces\Cms\EventRepositoryInterface;
use App\Interfaces\Cms\EventCategoryRepositoryInterface;
use App\Interfaces\Cms\MediaContentRepositoryInterface;
use App\Repositories\Cms\TutorialCategoryRepository;
use App\Repositories\Cms\CountryInfoRepository;
use App\Repositories\Cms\FaqRepository;
use App\Repositories\Cms\LanguageRepository;
use App\Repositories\Cms\NewsRepository;
use App\Repositories\Cms\ReleaseNoteRepository;
use App\Repositories\Cms\SocialLinkRepository;
use App\Repositories\Cms\TutorialRepository;
use App\Repositories\Cms\EventRepository;
use App\Repositories\Cms\EventCategoryRepository;
use App\Repositories\Cms\MediaContentRepository;

class CmsRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthClientRepositoryInterface::class, AuthClientRepository::class);
        $this->app->bind(CountryInfoRepositoryInterface::class, CountryInfoRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(LanguageRepositoryInterface::class, LanguageRepository::class);
        $this->app->bind(NewsRepositoryInterface::class, NewsRepository::class);
        $this->app->bind(ReleaseNoteRepositoryInterface::class, ReleaseNoteRepository::class);
        $this->app->bind(SocialLinkRepositoryInterface::class, SocialLinkRepository::class);
        $this->app->bind(TutorialRepositoryInterface::class, TutorialRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventCategoryRepositoryInterface::class, EventCategoryRepository::class);
        $this->app->bind(TutorialCategoryRepositoryInterface::class, TutorialCategoryRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(\App\Interfaces\Cms\TvChannelRepositoryInterface::class, \App\Repositories\Cms\TvChannelRepository::class);
$this->app->bind(\App\Interfaces\Cms\TvProgramRepositoryInterface::class, \App\Repositories\Cms\TvProgramRepository::class);
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
