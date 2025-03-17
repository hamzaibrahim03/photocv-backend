<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CatalogService;
use App\Http\Requests\Catalog\StoreCatalogRequest;
use App\Http\Requests\Catalog\UpdateCatalogRequest;

class CatalogController extends Controller
{
    private $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function index(Request $request)
    {
        return response()->json($this->catalogService->allCatalogs( $request ));
    }

    public function store(StoreCatalogRequest $request)
    {
        try {
            return response()->json( $this->catalogService->createCatalog( $request->except('icon'), $request->file('icon') ) );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Something went wrong', 'details' => $e->getMessage() ], 500 );
        }
    }

    public function update(UpdateCatalogRequest $request, $id)
    {
        try {
            return response()->json( $this->catalogService->updateCatalog( $id, $request->except('icon'), $request->file('icon') ) );
        } catch ( \Exception $e ) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy( $id )
    {
        return response()->json($this->catalogService->deleteCatalog( $id ) );
    }
}
