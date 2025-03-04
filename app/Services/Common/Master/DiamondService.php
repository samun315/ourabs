<?php

namespace App\Services\Common\Master;

use App\Models\Common\Master\Currency;
use App\Models\Common\Master\Diamond;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DiamondService
{
    public function getDiamondList(Request $request): JsonResponse
    {
        $query = Diamond::query()->select()->latest();

        $searchKeyword = $request->input('search');

        // If search keyword is present, apply search filter
        if ($searchKeyword) {
            $query->where('amount', 'like', '%' . $searchKeyword . '%')
                ->orWhere('active', 'like', '%' . $searchKeyword . '%');
        }

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {

                return '<button data-id="' . $row->diamond_amount_id . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm editDiamondButton" data-bs-toggle="modal" data-bs-target="#showModal"> <i class="fas fa-edit"></i> </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeDiamond(array $data): Diamond
    {
        return Diamond::query()->create($data);
    }

    public function getDiamondInfoById(int $diamondId): Model|Collection|Builder|array|null
    {
        return Diamond::query()->findOrFail($diamondId);
    }

    public function updateDiamondInfo(array $updateData, int $diamondId): bool
    {
        $diamond = $this->getDiamondInfoById($diamondId);

        return $diamond->update($updateData);
    }

    public function updateDiamondStatus(array $updateData, int $diamondId): bool
    {
        // Find the currncy by its ID or fail if not found.
        $diamond = Diamond::query()->where('diamond_amount_id', $diamondId)->first();

        // Update the currncy with the provided data and return the result.
        return $diamond->update($updateData);
    }
}
