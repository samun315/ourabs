<?php

namespace App\Http\Controllers\Menu\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\Menu\MenuRequest;
use App\Services\Menu\Menu\MenuService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(protected MenuService $menuService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('menu.menu.index');
        
        if ($request->ajax()) {
            return $this->menuService->getMenuList($request);
        }

        return view("menu.menu.index");
    }

    public function store(MenuRequest $request): JsonResponse
    {
        Gate::authorize('menu.menu.create');

        try {

            $this->menuService->createMenu($request->fields());
            return sendSuccessResponse(201, 'Menu created successfully.');
        } catch (Exception $e) {
            return sendErrorResponse('Internal Server Error: ', $e->getMessage());
        }
    }

    public function edit(int $menuId):JsonResponse
    {
        Gate::authorize('menu.menu.edit');

        $data = $this->menuService->getMenuById($menuId);
        return sendSuccessResponse(200, '', 'menuInfo', $data);
    }

    public function update(MenuRequest $request, int $menuId): JsonResponse
    {
        Gate::authorize('menu.menu.edit');

        try {

            $this->menuService->updateMenu($request->fields(), $menuId);
            return sendSuccessResponse(201, 'Menu updated successfully.');
        } catch (Exception $e) {
            return sendErrorResponse('Internal Server Error: ', $e->getMessage());
        }
    }

}
