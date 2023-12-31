<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Helpers\LanguageHelper;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use App\Models\PricePlan;
use App\Models\StaticOption;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\Blog\Entities\Blog;
use Modules\Blog\Entities\BlogCategory;

class LanguagesController extends Controller
{
    const BASE_PATH = 'landlord.admin.languages.';
    public bool $allow_method = false;

    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:language-list|language-edit|language-create|language-delete', ['only' => ['index', 'make_default']]);
        $this->middleware('permission:language-create', ['only' => ['store']]);
        $this->middleware('permission:language-edit', ['only' => ['backend_edit_words', 'frontend_edit_words', 'update_words', 'update', 'add_new_string', 'clone']]);
        $this->middleware('permission:language-delete', ['only' => ['delete']]);
    }

    public function __destruct()
    {
        if ($this->allow_method)
        {
            $language = Language::select('name', 'slug', 'status', 'direction')->get();
            update_static_option_central('central_language', json_encode($language));
        }
    }

    public function index()
    {
        $all_lang = Language::all();
        return view(self::BASE_PATH.'index',compact('all_lang'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string:max:191',
            'direction' => 'required|string:max:191',
            'slug' => 'required|string:max:191',
            'status' => 'required|string:max:191',
        ]);

        $language = Language::create([
            'name' => $request->name,
            'direction' => $request->direction,
            'slug' => $request->slug,
            'status' => $request->status,
            'default' => 0
        ]);

        //generate admin panel string
        if (!tenant() && !file_exists(resource_path('lang/') . $request->slug . '.json'))
        {
            $backend_default_lang_data = file_get_contents(resource_path('lang/') . 'default.json');
            file_put_contents(resource_path('lang/') . $request->slug . '.json', $backend_default_lang_data);
        }

        \Cache::forget('lang_key');
        $this->allow_method = true;

        return redirect()->back()->with([
            'msg' => __('New Language Added Success...'),
            'type' => 'success'
        ]);
    }

    public function all_edit_words($slug)
    {
        abort_if(tenant(), 404);

        if(!file_exists(resource_path('lang/') . $slug . '.json') && !is_dir(resource_path('lang/') . $slug . '.json')){
            $backend_default_lang_data = file_get_contents(resource_path('lang/') . 'default.json');
            file_put_contents(resource_path('lang/') . $slug . '.json', $backend_default_lang_data);
        }
        $all_word = file_get_contents(resource_path('lang/') . $slug . '.json');

        return view(self::BASE_PATH.'edit-words')->with([
            'all_word' => json_decode($all_word),
            'lang_slug' => $slug,
            'type' => 'admin',
            'language' => Language::where('slug',$slug)->first()
        ]);
    }


    public function update_words(Request $request, $id)
    {
        $this->validate($request,[
            'string_key' => 'required',
            'translate_word' => 'required',
        ],[
            'type.required' => __('type is missing'),
            'string_key.required' => __('select source text'),
            'translate_word.required' => __('add translate text'),
        ]);

        $slug = $id;

        //todo get text json file
        //todo get current key index and replace it in the json file
        if (file_exists(resource_path('lang/') . $slug .'.json')) {
            $default_lang_data = file_get_contents(resource_path('lang') . '/'.$slug .'.json');
            $default_lang_data = (array)json_decode($default_lang_data);
            $default_lang_data[$request->string_key] = $request->translate_word;
            $default_lang_data = (object)$default_lang_data;
            $default_lang_data = json_encode($default_lang_data);
            file_put_contents(resource_path('lang/') . $slug . '.json', $default_lang_data);
        }

        \Cache::forget('lang_key');

        return response()->json([
            'type' => 'success',
            'msg' =>  __('Words Change Success')
        ]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string:max:191',
            'direction' => 'required|string:max:191',
            'status' => 'required|string:max:191',
            'slug' => 'required|string:max:191'
        ]);
        Language::where('id', $request->id)->update([
            'name' => $request->name,
            'direction' => $request->direction,
            'status' => $request->status,
            'slug' => $request->slug
        ]);

        $backend_lang_file_path = resource_path('lang/') . $request->slug . '.json';
        $frontend_lang_file_path = resource_path('lang/') . $request->slug . '.json';
        if (!file_exists($backend_lang_file_path)) {
            file_put_contents(resource_path('lang/') . $request->slug . '.json', file_get_contents(resource_path('lang/') . $request->slug . '.json'));
        }

        \Cache::forget('lang_key');
        $this->allow_method = true;

        return redirect()->back()->with([
            'msg' => __('Language Update Success...'),
            'type' => 'success'
        ]);
    }

    public function delete(Request $request, $id)
    {
        $lang = Language::find($id);

        if (!tenant())
        {
            if (file_exists(resource_path('lang/') . $lang->slug . '.json')) {
                unlink(resource_path('lang/') . $lang->slug . '.json');
            }
        }

        $lang->delete();

        \Cache::forget('lang_key');
        $this->allow_method = true;

        return redirect()->back()->with([
            'msg' => __('Language Delete Success...'),
            'type' => 'danger'
        ]);
    }

    public function make_default(Request $request, $id)
    {
        Language::where('default', 1)->update(['default' => 0]);
        Language::find($id)->update(['default' => 1]);
        $lang = Language::find($id);
        $lang->default = 1;
        $lang->save();

        \Cache::forget('lang_key');
        session()->put('lang', $lang->slug);
        return redirect()->back()->with([
            'msg' => __('Default Language Set To') . ' ' . $lang->name,
            'type' => 'success'
        ]);
    }

    public function clone_languages(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'name' => 'required|string',
            'direction' => 'required|string',
            'status' => 'required|string',
            'slug' => 'required|string',
        ]);

        $clone_lang = Language::find($request->id);
        Language::create([
            'name' => $request->name,
            'direction' => $request->direction,
            'slug' => $request->slug,
            'status' => $request->status,
            'default' => 0
        ]);

        $search_term = '_' . $clone_lang->slug . '_';
        $all_static_option = StaticOption::where('option_name', 'regexp', $search_term)->get();
        foreach ($all_static_option as $option) {
            $option_name = str_replace($search_term, '_' . $request->slug . '_', $option->option_name);
            StaticOption::create([
                'option_name' => $option_name,
                'option_value' => $option->option_value
            ]);
        }

        $backend_default_lang_data = file_get_contents(resource_path('lang') . '/' . $clone_lang->slug . '.json');
        file_put_contents(resource_path('lang/') . $request->slug . '.json', $backend_default_lang_data);


        return redirect()->back()->with([
            'msg' => __('Language clone success with content...'),
            'type' => 'success'
        ]);
    }


    public function add_new_words(Request $request)
    {

        $this->validate($request, [
            'lang_slug' => 'required|string',
            'new_string' => 'required|string',
            'translate_string' => 'required|string',
        ]);

        if (file_exists(resource_path('lang/') . $request->lang_slug . '.json')) {
            $default_lang_data = file_get_contents(resource_path('lang/') . $request->lang_slug .'.json');
            $default_lang_data = (array)json_decode($default_lang_data);
            $default_lang_data[$request->new_string] = $request->translate_string;
            $default_lang_data = (object)$default_lang_data;
            $default_lang_data = json_encode($default_lang_data);
            file_put_contents(resource_path('lang/') . $request->lang_slug . '.json', $default_lang_data);
        }

        \Cache::forget('lang_key');

        return back()->with(['msg' => __('New Word Added'), 'type' => 'success']);
    }

    public function regenerate_source_text(Request $request){

        $this->validate($request,[
            'slug' => 'required'
        ]);

        if (file_exists(resource_path('lang/') . $request->slug . '.json')){
            @unlink(resource_path('lang/') . $request->slug . '.json');
        }
        Artisan::call('translatable:export '.$request->slug );
        \Cache::forget('lang_key');
        return back()->with(['msg' => __('Source text generate success'), 'type' => 'success']);
    }

    public function add_new_string(Request $request)
    {
        $this->validate($request, [
            'slug' => 'required',
            'string' => 'required',
            'translate_string' => 'required',
        ]);
        if (file_exists(resource_path('lang/') . $request->slug . '.json')) {
            $default_lang_data = file_get_contents(resource_path('lang') . '/' . $request->slug  . '.json');
            $default_lang_data = (array) json_decode($default_lang_data);
            $default_lang_data[$request->string] = $request->translate_string;
            $default_lang_data = (object) $default_lang_data;
            $default_lang_data =   json_encode($default_lang_data);
            file_put_contents(resource_path('lang/') . $request->slug . '.json', $default_lang_data);
        }

        \Cache::forget('lang_key');
        return redirect()->back()->with([
            'msg' => __('new translated string added..'),
            'type' => 'success'
        ]);
    }
}
