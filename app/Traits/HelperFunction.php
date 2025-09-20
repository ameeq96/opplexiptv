<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HelperFunction
{

    public function isRtl($locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();

        $shortLocale = substr($locale, 0, 2);

        $rtlLocales = ['ar', 'ur', 'fa', 'he'];

        return in_array($shortLocale, $rtlLocales);
    }

    protected function runIndex($service, Request $request)
    {
        if (method_exists($service, 'base')) {
            $builder = $service->base();
        } elseif (method_exists($service, 'query')) {
            $builder = $service->query();
        } else {
            throw new \Exception('Service must have base() or query() method');
        }

        if (method_exists($service, 'applyFilters')) {
            $service->applyFilters($builder, $request);
        }

        if (method_exists($service, 'applySorting')) {
            $service->applySorting($builder, $request);
        }

        return $service->paginate($builder, $request);
    }

    protected function handleStore($service, $mediaService, Request $request)
    {
        if (method_exists($service, 'create')) {
            $item = $service->create($request);
        } elseif (method_exists($service, 'createOrder')) {
            $item = $service->createOrder($request);
        } else {
            throw new \Exception('Service must have create() or createOrder()');
        }

        if ($request->hasFile('screenshots')) {
            $mediaService->storeScreenshots($item, $request->file('screenshots'));
        }

        return $item;
    }

    protected function handleUpdate($service, $mediaService, Request $request, $model)
    {
        if (method_exists($service, 'update')) {
            $service->update($request, $model);
        } elseif (method_exists($service, 'updateOrder')) {
            $service->updateOrder($request, $model);
        } else {
            throw new \Exception('Service must have update() or updateOrder()');
        }

        if ($request->hasFile('screenshots')) {
            $mediaService->storeScreenshots($model, $request->file('screenshots'));
        }
    }

    protected function bulkDelete(Request $request, string $key, $crud, $media = null, string $modelClass = null)
    {
        $ids = $request->input($key, []);
        if (empty($ids)) {
            return back()->with('error', 'No records selected.');
        }

        if ($media && $modelClass) {
            $models = $modelClass::whereIn('id', $ids)->with('pictures')->get();
            $media->cleanupPictures($models);
        }

        $count = $crud->bulkDelete($ids);

        return back()->with('success', "{$count} record(s) deleted successfully.");
    }
}
