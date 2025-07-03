<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\MediaContentRepositoryInterface;
use App\Constants\Constants;
use App\Http\Resources\Admin\MediaContent\MediaContentResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaContentRepository extends BaseRepository implements MediaContentRepositoryInterface
{
    use Access, HttpResponses, Helper;

    public function __construct()
    {
        //
    }

    public function index($obj, $request)
    {
        try {
            $query = $obj::with(['tvChannel', 'createdBy', 'modifiedBy'])
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $responseData = MediaContentResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, Constants::GETALL, Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, Constants::GETALL, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function store($obj, $request)
    {
        try {
            DB::beginTransaction();

            // Handle auto-generation of slug if not provided
            if (empty($request['slug']) && !empty($request['title'])) {
                $request['slug'] = \Illuminate\Support\Str::slug($request['title']);

                // Ensure slug is unique
                $originalSlug = $request['slug'];
                $counter = 1;
                while ($obj::where('slug', $request['slug'])->exists()) {
                    $request['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Handle file uploads
            if (isset($request['featured_image']) && $this->isBase64Image($request['featured_image'])) {
                $request['featured_image'] = $this->storeBase64Image($request['featured_image'], 'media-contents');
            }

            if (isset($request['audio_url']) && $this->isBase64File($request['audio_url'])) {
                $request['audio_url'] = $this->storeBase64File($request['audio_url'], 'media-contents/audio');
            }

            if (isset($request['video_url']) && $this->isBase64File($request['video_url'])) {
                $request['video_url'] = $this->storeBase64File($request['video_url'], 'media-contents/video');
            }

            // Handle gallery images
            if (isset($request['gallery_images']) && is_array($request['gallery_images'])) {
                $processedImages = [];
                foreach ($request['gallery_images'] as $image) {
                    if ($this->isBase64Image($image)) {
                        $processedImages[] = $this->storeBase64Image($image, 'media-contents/gallery');
                    } else {
                        $processedImages[] = $image;
                    }
                }
                $request['gallery_images'] = $processedImages;
                $request['gallery_count'] = count($processedImages);
            }

            $mediaContent = $obj::create($request);

            if ($mediaContent) {
                DB::commit();
                $responseData = new MediaContentResource($mediaContent->load(['tvChannel', 'createdBy', 'modifiedBy']));
                return $this->success($responseData, Constants::STORE, Response::HTTP_CREATED, true);
            } else {
                DB::rollBack();
                return $this->error(null, Constants::STORE, Response::HTTP_BAD_REQUEST, false);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show($obj, $id)
    {
        try {
            $mediaContent = $obj::with(['tvChannel', 'createdBy', 'modifiedBy'])->find($id);

            if ($mediaContent) {
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
            $mediaContent = $obj::with(['tvChannel', 'createdBy', 'modifiedBy'])->where('slug', $slug)->first();

            if ($mediaContent) {
                $responseData = new MediaContentResource($mediaContent);
                return $this->success($responseData, Constants::SHOW, Response::HTTP_OK, true);
            } else {
                return $this->error(null, Constants::SHOW, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function update($obj, $request, $id)
    {
        try {
            DB::beginTransaction();

            $mediaContent = $obj::find($id);
            if ($mediaContent) {
                // Handle auto-generation of slug if title is updated but slug is not provided
                if (empty($request['slug']) && !empty($request['title']) && $request['title'] !== $mediaContent->title) {
                    $request['slug'] = \Illuminate\Support\Str::slug($request['title']);

                    // Ensure slug is unique (excluding current record)
                    $originalSlug = $request['slug'];
                    $counter = 1;
                    while ($obj::where('slug', $request['slug'])->where('id', '!=', $id)->exists()) {
                        $request['slug'] = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                }

                // Handle file uploads
                if (isset($request['featured_image']) && $this->isBase64Image($request['featured_image'])) {
                    if ($mediaContent->featured_image) {
                        $this->deleteStoredImage($mediaContent->featured_image);
                    }
                    $request['featured_image'] = $this->storeBase64Image($request['featured_image'], 'media-contents');
                }

                if (isset($request['audio_url']) && $this->isBase64File($request['audio_url'])) {
                    if ($mediaContent->audio_url) {
                        $this->deleteStoredFile($mediaContent->audio_url);
                    }
                    $request['audio_url'] = $this->storeBase64File($request['audio_url'], 'media-contents/audio');
                }

                if (isset($request['video_url']) && $this->isBase64File($request['video_url'])) {
                    if ($mediaContent->video_url) {
                        $this->deleteStoredFile($mediaContent->video_url);
                    }
                    $request['video_url'] = $this->storeBase64File($request['video_url'], 'media-contents/video');
                }

                // Handle gallery images
                if (isset($request['gallery_images']) && is_array($request['gallery_images'])) {
                    $processedImages = [];
                    foreach ($request['gallery_images'] as $image) {
                        if ($this->isBase64Image($image)) {
                            $processedImages[] = $this->storeBase64Image($image, 'media-contents/gallery');
                        } else {
                            $processedImages[] = $image;
                        }
                    }
                    $request['gallery_images'] = $processedImages;
                    $request['gallery_count'] = count($processedImages);
                }

                $mediaContent->update($request);

                DB::commit();
                $responseData = new MediaContentResource($mediaContent->load(['tvChannel', 'createdBy', 'modifiedBy']));
                return $this->success($responseData, Constants::UPDATE, Response::HTTP_OK, true);
            } else {
                DB::rollBack();
                return $this->error(null, Constants::UPDATE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function destroy($obj, $id)
    {
        try {
            DB::beginTransaction();

            $mediaContent = $obj::find($id);
            if ($mediaContent) {
                $mediaContent->delete();

                DB::commit();
                return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
            } else {
                DB::rollBack();
                return $this->error(null, Constants::DESTROY, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function restore($obj, $id)
    {
        try {
            DB::beginTransaction();

            $mediaContent = $obj::withTrashed()->find($id);
            if ($mediaContent && $mediaContent->trashed()) {
                $mediaContent->restore();

                DB::commit();
                $responseData = new MediaContentResource($mediaContent->load(['tvChannel', 'createdBy', 'modifiedBy']));
                return $this->success($responseData, Constants::RESTORE, Response::HTTP_OK, true);
            } else {
                DB::rollBack();
                return $this->error(null, Constants::RESTORE, Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function toggleFeatured($obj, $id)
    {
        try {
            DB::beginTransaction();

            $mediaContent = $obj::find($id);
            if ($mediaContent) {
                $mediaContent->update(['is_featured' => !$mediaContent->is_featured]);

                DB::commit();
                $responseData = new MediaContentResource($mediaContent->load(['tvChannel', 'createdBy', 'modifiedBy']));
                return $this->success($responseData, 'Featured status updated successfully', Response::HTTP_OK, true);
            } else {
                DB::rollBack();
                return $this->error(null, 'Media content not found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function updateStatus($obj, $id, $status)
    {
        try {
            DB::beginTransaction();

            $mediaContent = $obj::find($id);
            if ($mediaContent) {
                $mediaContent->update(['status' => $status]);

                DB::commit();
                $responseData = new MediaContentResource($mediaContent->load(['tvChannel', 'createdBy', 'modifiedBy']));
                return $this->success($responseData, 'Status updated successfully', Response::HTTP_OK, true);
            } else {
                DB::rollBack();
                return $this->error(null, 'Media content not found', Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getFeaturedContent($obj, $request)
    {
        try {
            $query = $obj::with(['tvChannel', 'createdBy', 'modifiedBy'])
                ->featured()
                ->where('status', true)
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $responseData = MediaContentResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, 'Featured content retrieved successfully', Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, "No featured content found", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getByContentType($obj, $request, $contentType)
    {
        try {
            $query = $obj::with(['tvChannel', 'createdBy', 'modifiedBy'])
                ->byContentType($contentType)
                ->where('status', true)
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $responseData = MediaContentResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, "Content by type '{$contentType}' retrieved successfully", Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, "No content found for type '{$contentType}'", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getByChannel($obj, $request, $channelId)
    {
        try {
            $query = $obj::with(['tvChannel', 'createdBy', 'modifiedBy'])
                ->byChannel($channelId)
                ->where('status', true)
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $responseData = MediaContentResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, "Content by channel retrieved successfully", Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, "No content found for this channel", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getPopularContent($obj, $request)
    {
        try {
            $query = $obj::with(['tvChannel', 'createdBy', 'modifiedBy'])
                ->where('status', true)
                ->orderBy('view_count', 'desc')
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $responseData = MediaContentResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, 'Popular content retrieved successfully', Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, "No popular content found", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getContentByNewsCategory($obj, $request, $newsCategory)
    {
        try {
            $query = $obj::with(['tvChannel', 'createdBy', 'modifiedBy'])
                ->where('news_category', $newsCategory)
                ->where('status', true)
                ->orderByPublished()
                ->filter((array)$request)
                ->paginate($request['length'] ?? 10)
                ->withQueryString();

            if ($query) {
                $responseData = MediaContentResource::collection($query)->response()->getData();
                $responseData = (array)$responseData;
                $responseData['permissions'] = $this->getUserPermissions();
                return $this->success($responseData, "Content by news category '{$newsCategory}' retrieved successfully", Response::HTTP_OK, true);
            } else {
                $responseData = ['permissions' => $this->getUserPermissions()];
                return $this->error($responseData, "No content found for news category '{$newsCategory}'", Response::HTTP_NOT_FOUND, false);
            }
        } catch (\Exception $e) {
            $responseData = ['permissions' => $this->getUserPermissions()];
            return $this->error($responseData, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    // Helper methods for file handling
    private function isBase64Image($string)
    {
        return (bool) preg_match('/^data:image\/[a-zA-Z]+;base64,/', $string);
    }

    private function isBase64File($string)
    {
        return (bool) preg_match('/^data:[a-zA-Z\/]+;base64,/', $string);
    }

    private function storeBase64Image($base64String, $folder = 'images')
    {
        try {
            $imageData = explode(',', $base64String);
            $imageType = explode('/', explode(':', substr($imageData[0], 0, strpos($imageData[0], ';')))[1])[1];
            $imageName = time() . '_' . uniqid() . '.' . $imageType;
            $imagePath = $folder . '/' . $imageName;

            Storage::disk('public')->put($imagePath, base64_decode($imageData[1]));

            return $imagePath;
        } catch (\Exception $e) {
            throw new \Exception('Failed to store image: ' . $e->getMessage());
        }
    }

    private function storeBase64File($base64String, $folder = 'files')
    {
        try {
            $fileData = explode(',', $base64String);
            $mimeType = explode('/', explode(':', substr($fileData[0], 0, strpos($fileData[0], ';')))[1])[1];
            $fileName = time() . '_' . uniqid() . '.' . $mimeType;
            $filePath = $folder . '/' . $fileName;

            Storage::disk('public')->put($filePath, base64_decode($fileData[1]));

            return $filePath;
        } catch (\Exception $e) {
            throw new \Exception('Failed to store file: ' . $e->getMessage());
        }
    }

    private function deleteStoredImage($imagePath)
    {
        if ($imagePath && !filter_var($imagePath, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    private function deleteStoredFile($filePath)
    {
        if ($filePath && !filter_var($filePath, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($filePath);
        }
    }
}
