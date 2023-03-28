
## innart-group/generic-in-model-search(Developpment in progress)

The innart-group/generic-in-model-search package is a Laravel package that provides a generic search functionality to models in a Laravel application. It allows you to search for a string in all the table associated with a model. The package is highly configurable and supports various search options, such as excluding certain models from the search and specifying the number of results per page. The package is easy to use and can save you a lot of time and effort when implementing search functionality in your Laravel application.


## Installation

To install the innart-group/generic-in-model-search package using Composer, you can follow these steps:

1. Open your terminal or command prompt and navigate to your Laravel project directory.

2. Run the following command to add the package to your composer.json file:

```php
composer require innart-group/generic-in-model-search
```


3. After running the command above, Composer will download and install the package and its dependencies.


# Configuration
1. Publish the package configuration file by running the following command:



```php
php artisan vendor:publish --provider="InnartGroup\GenericInModelSearch\GenericSearchServiceProvider" --tag=config
```

This will copy the package's config.php file to your project's config directory.

2. Open the config/generic-in-model-search.php file in your project and configure the options according to your needs.


## Usage

Once the installation and configuration process is complete, you can start using the package in your Laravel project by importing the namespace and calling the genericSearch method:

```php
use InnartGroup\GenericInModelSearch\GenericSearch;

$search = new GenericSearch();

$results = $search->genericSearch($request, $modelNameToExclude, $modelClassSpecified, $perPage, $totalResultsExpected);
```

Or by using default full database search :

```php

use InnartGroup\GenericInModelSearch\GenericSearch;

$search = new GenericSearch();

$results = $search->genericSearchDefault($request);

```


You can excluse some models by doing :

```php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InnartGroup\GenericInModelSearch\GenericSearch;

/**
 * Controller class for handling search functionality.
 */
class SearchController extends Controller
{
    /**
     * Handles the search request and returns the search results.
     *
     * @param Request $request The HTTP request object.
     *
     * @return \Illuminate\Http\JsonResponse The HTTP response containing the search results.
     */
    public function search(Request $request)
    {
        $modelClass = 'App\Models\\';
        $search = new GenericSearch();
        // Check if the user is logged in
        if (Auth::check()) {
            // Check if the user is an admin 
            if (Auth::user()->isAdmin) {
                // Exclude the 'User' model from the search results
                $modelNameToExclude = [];
                return $search->genericSearch($request, $modelNameToExclude, $modelClass, 15, 100);
            }
            if (!Auth::user()->isAdmin) {
                // Exclude the 'User' model from the search results
                $modelNameToExclude = ['User', 'Confirmation'];
                return $search->genericSearch($request, $modelNameToExclude, $modelClass, 15, 100);
            }
        }
    
        }
        // If none of the above cases are true, return an error response
        return response()->json([
            'message' => 'Something went wrong.',
        ], 400);
    }
```
## License

The Laravel framework is open-sourced library licensed under the [MIT license](https://opensource.org/licenses/MIT).
