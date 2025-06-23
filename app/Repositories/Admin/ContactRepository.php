<?php

namespace App\Repositories\Admin;

use App\Http\Resources\Admin\Contact\ContactResource;
use App\Http\Traits\Access;
use App\Http\Traits\Helper;
use App\Http\Traits\HttpResponses;
use App\Interfaces\Admin\ContactRepositoryInterface;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ContactRepository extends BaseRepository implements ContactRepositoryInterface
{
    use Access, HttpResponses, Helper;

    public function index($obj, array $data)
    {
        try {
            $query = $obj::query()
                ->orderBy('created_at', 'desc')
                ->filter((array)$data);

            $query = $query->when(
                isset($data['paginate']) && $data['paginate'] == true,
                function ($query) use ($data) {
                    return $query->paginate($data['per_page'] ?? 10);
                },
                function ($query) {
                    return $query->get();
                }
            );

            $responseData = ContactResource::collection($query)->response()->getData();
            $responseData = (array)$responseData;
            $responseData['permissions'] = $this->getUserPermissions();
            return $this->success(
                $responseData,
                'Contact list retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->error(
                $e->getMessage(),
                'Failed to retrieve contact list',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function show($obj, $id)
    {
        try {
            $contact = $obj->findOrFail($id);

            return $this->success(
                new ContactResource($contact),
                'Contact details retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->error(
                $e->getMessage(),
                'Failed to retrieve contact details',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function destroy($obj, $id)
    {
        try {
            $contact = $obj->findOrFail($id);
            $contact->delete();

            return $this->success(
                null,
                'Contact deleted successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->error(
                $e->getMessage(),
                'Failed to delete contact',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function restore($obj, $id)
    {
        try {
            $contact = $obj->withTrashed()->findOrFail($id);
            $contact->restore();

            return $this->success(
                new ContactResource($contact),
                'Contact restored successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->error(
                $e->getMessage(),
                'Failed to restore contact',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
