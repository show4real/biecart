<?php

namespace Modules\MobileApp\Http\Controllers\Api\V1;

use FontLib\Table\Type\name;
use Illuminate\Routing\Controller;
use Modules\CountryManage\Entities\Country;
use Modules\CountryManage\Entities\State;

class CountryController extends Controller
{
    /*
    * fetch all country list from database
    */
    public function country()
    {
        $country = Country::select('id', 'name')->orderBy('name', 'asc')->paginate(10);

        return response()->json([
            'countries' => $country
        ]);
    }

    /*
    * fetch all state list based on provided country id from database
    */
    public function stateByCountryId($id)
    {
        if(empty($id)){
            return response()->json([
                'message' => __('provide a valid country id')
            ])->setStatusCode(422);
        }

        $state = State::select('id', 'name','country_id')->where('country_id',$id)->orderBy('name', 'asc')->paginate(10);

        return response()->json([
            'state' => $state
        ]);
    }

    public function searchCountry($name)
    {
        if(empty($name)){
            return response()->json([
                'message' => __('provide a valid country name')
            ])->setStatusCode(422);
        }

        $country = Country::where('name', 'LIKE', '%'.$name.'%')->select('id', 'name')->orderBy('name', 'asc')->paginate(10);

        return response()->json([
            'countries' => $country
        ]);
    }

    public function searchState($name)
    {
        if(empty($name)){
            return response()->json([
                'message' => __('provide a valid state name')
            ])->setStatusCode(422);
        }

        $state = State::where('name', 'LIKE', '%'.$name.'%')->select('id', 'name','country_id')->orderBy('name', 'asc')->paginate(10);

        return response()->json([
            'state' => $state
        ]);
    }
}
