<?php

namespace InnartGroup\GenericInModelSearch;

use Illuminate\Http\Request;

/**
 * GenericSearch class for handling search functionality.
 */
class GenericSearch
{
    /**
     * Perform a generic search across multiple models and their properties.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function genericSearchDefault(Request $request)
    {
        try {
            $query = $request->input('q');

            // Get a list of all model names in the specified namespace
            $models = array_map(function ($model) {
                return basename($model, '.php');
            }, glob(app_path('Models/*.php')));

            $data = [];

            $modelNameToExclude = config('generic-in-model-search.excluded_models');
            $modelClassSpecified = config('generic-in-model-search.model_namespace');
            $perPage = config('generic-in-model-search.per_page');
            $totalResultsExpected = config('generic-in-model-search.total_results_expected');

            // Loop through each model and each property
            foreach ($models as $model) {
                if (in_array($model, $modelNameToExclude)) {
                    continue; // Exclude the specified models from the search results
                }
                $modelClass = $modelClassSpecified . $model;
                $properties = (new $modelClass())->getFillable();

                foreach ($properties as $property) {
                    // Search for the property with the search query
                    $results = $modelClass::where($property, 'like', '%' . $query . '%')->get();

                    // Add the results to the data array
                    foreach ($results as $result) {
                        if (!isset($data[$model])) {
                            $data[$model] = [];
                        }
                        $data[$model][] = $result;
                    }
                }
            }

            if ($totalResultsExpected !== null) {
                // Check if the number of results exceeds totalResultsExpected
                $totalResults = count($data);
                if ($totalResults >= $totalResultsExpected) {
                    return response()->json([
                        'message' => 'The search returned too many results. Please refine your search.',
                    ], 400);
                }
            }

            // Paginate the results if necessary
            $currentPage = $request->input('page', 1);
            $offset = ($currentPage - 1) * $perPage;

            foreach ($data as $model => $results) {
                $data[$model] = array_slice($results, $offset, $perPage);
            }

            // Return the search results as a paginated JSON response
            return $this->returnResponse($data, $totalResults, $perPage, $currentPage);

        } catch (\Exception$e) {
            return response()->json([
                'message' => 'An error occurred while processing your request.',
            ], 500);
        }
    }

    /**
     * Perform a generic search across multiple models and their properties.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $modelNameToExclude The list of model names to exclude from the search results.
     * @param string $modelClassSpecified The namespace of the models to search.
     * @param int $perPage The number of results to display per page.
     * @param int|null $totalResultsExpected The maximum number of results allowed. Set to null to disable.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function genericSearch(Request $request, $modelNameToExclude, $modelClassSpecified, $perPage = 15, $totalResultsExpected)
    {
        try {
            $query = $request->input('q');

            // Get a list of all model names in the specified namespace
            $models = array_map(function ($model) {
                return basename($model, '.php');
            }, glob(app_path('Models/*.php')));

            $data = [];

            // Loop through each model and each property
            foreach ($models as $model) {
                if (in_array($model, $modelNameToExclude)) {
                    continue; // Exclude the specified models from the search results
                }
                $modelClass = $modelClassSpecified . $model;
                $properties = (new $modelClass())->getFillable();

                foreach ($properties as $property) {
                    // Search for the property with the search query
                    $results = $modelClass::where($property, 'like', '%' . $query . '%')->get();

                    // Add the results to the data array
                    foreach ($results as $result) {
                        if (!isset($data[$model])) {
                            $data[$model] = [];
                        }
                        $data[$model][] = $result;
                    }
                }
            }

            if ($totalResultsExpected !== null) {
                // Check if the number of results exceeds totalResultsExpected
                $totalResults = count($data);
                if ($totalResults >= $totalResultsExpected) {
                    return response()->json([
                        'message' => 'The search returned too many results. Please refine your search.',
                    ], 400);
                }
            }

            // Paginate the results if necessary
            $currentPage = $request->input('page', 1);
            $offset = ($currentPage - 1) * $perPage;

            foreach ($data as $model => $results) {
                $data[$model] = array_slice($results, $offset, $perPage);
            }

            // Return the search results as a paginated JSON response
            return $this->returnResponse($data, $totalResults, $perPage, $currentPage);

        } catch (\Exception$e) {
            return response()->json([
                'message' => 'An error occurred while processing your request.',
            ], 500);
        }
    }

    /**
     * Helper method to return a paginated JSON response for the search results.
     *
     * @param array $data The search results data.
     * @param int $totalResults The total number of search results.
     * @param int $perPage The number of results per page.
     * @param int $currentPage The current page number.
     *
     * @return Illuminate\Http\JsonResponse A paginated JSON response containing the search results.
     */
    public function returnResponse($data, $totalResults, $perPage, $currentPage)
    {
        $response = [
            'data' => $data,
            'total' => $totalResults,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => ceil($totalResults / $perPage),
        ];
    
        return collect($response);
    }
}
