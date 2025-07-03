<?php

namespace App\Repositories\Cms;

use App\Interfaces\Cms\MediaContentRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Cms\MediaContent\MediaContentResource;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use Symfony\Component\HttpFoundation\Response;

class MediaContentRepository implements MediaContentRepositoryInterface
{
    use HttpResponses, Helper;

    public function __construct()
    {
        //
    }

    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['tvChannel'])
                ->where('status', true)
                ->published()
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = MediaContentResource::collection($query)->response()->getData();
                return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show($obj, $id)
    {
        try {
            $mediaContent = $obj::with(['tvChannel'])
                ->where('status', true)
                ->published()
                ->find($id);

            if ($mediaContent) {
                // Increment view count
                $mediaContent->increment('view_count');

                $responseData = new MediaContentResource($mediaContent);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function showBySlug($obj, $slug)
    {
        try {
            $mediaContent = $obj::with(['tvChannel'])
                ->where('status', true)
                ->published()
                ->where('slug', $slug)
                ->first();

            if ($mediaContent) {
                // Increment view count
                $mediaContent->increment('view_count');

                $responseData = new MediaContentResource($mediaContent);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getFeatured($obj, $request)
    {
        try {
            $query = $obj::with(['tvChannel'])
                ->featured()
                ->where('status', true)
                ->published()
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = MediaContentResource::collection($query)->response()->getData();
                return $this->success($data, 'Featured content retrieved successfully', Response::HTTP_OK, true);
            } else {
                return $this->error(null, 'No featured content found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getByType($obj, $request, $contentType)
    {
        try {
            $query = $obj::with(['tvChannel'])
                ->byContentType($contentType)
                ->where('status', true)
                ->published()
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = MediaContentResource::collection($query)->response()->getData();
                return $this->success($data, "Content by type '{$contentType}' retrieved successfully", Response::HTTP_OK, true);
            } else {
                return $this->error(null, "No content found for type '{$contentType}'", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getByChannel($obj, $request, $channelId)
    {
        try {
            $query = $obj::with(['tvChannel'])
                ->byChannel($channelId)
                ->where('status', true)
                ->published()
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = MediaContentResource::collection($query)->response()->getData();
                return $this->success($data, "Content by channel retrieved successfully", Response::HTTP_OK, true);
            } else {
                return $this->error(null, "No content found for this channel", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getPopular($obj, $request)
    {
        try {
            $query = $obj::with(['tvChannel'])
                ->where('status', true)
                ->published()
                ->orderBy('view_count', 'desc')
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = MediaContentResource::collection($query)->response()->getData();
                return $this->success($data, 'Popular content retrieved successfully', Response::HTTP_OK, true);
            } else {
                return $this->error(null, 'No popular content found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getRecent($obj, $request)
    {
        try {
            $query = $obj::with(['tvChannel'])
                ->where('status', true)
                ->published()
                ->orderBy('created_at', 'desc')
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = MediaContentResource::collection($query)->response()->getData();
                return $this->success($data, 'Recent content retrieved successfully', Response::HTTP_OK, true);
            } else {
                return $this->error(null, 'No recent content found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function search($obj, $request, $searchTerm)
    {
        try {
            $query = $obj::with(['tvChannel'])
                ->where('status', true)
                ->published()
                ->where(function ($query) use ($searchTerm) {
                    $query->where('title', 'like', '%' . $searchTerm . '%')
                          ->orWhere('description', 'like', '%' . $searchTerm . '%')
                          ->orWhere('article_content', 'like', '%' . $searchTerm . '%')
                          ->orWhereJsonContains('tags', $searchTerm);
                })
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = MediaContentResource::collection($query)->response()->getData();
                return $this->success($data, 'Search results retrieved successfully', Response::HTTP_OK, true);
            } else {
                return $this->error(null, 'No search results found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getByNewsCategory($obj, $request, $newsCategory)
    {
        try {
            $query = $obj::with(['tvChannel'])
                ->where('news_category', $newsCategory)
                ->where('status', true)
                ->published()
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $data = MediaContentResource::collection($query)->response()->getData();
                return $this->success($data, "Content by news category '{$newsCategory}' retrieved successfully", Response::HTTP_OK, true);
            } else {
                return $this->error(null, "No content found for news category '{$newsCategory}'", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}
